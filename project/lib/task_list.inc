<?php

// class task_list extends db_list {{{
class task_list extends db_list {
  var $table = "task";
  var $entity_class = "task";
  var $fields = "task.*, LEAST(task.dateActualStart, task.dateTargetStart, task.dateActualCompletion, task.dateTargetCompletion) as sortDate";
  var $order_by = "sortDate, priority";

  function get_task_summary($task_options = "", $hierarchical = true, $format = "html") {

    global $sess;

    $tasks = $this->entities;

    if (count($tasks) == 0) {
      $rtn = "";
    }

    reset($tasks);

    $child_filter = $this->filter;
    $child_filter->set_element("top", false);
    while (list(, $task) = each($tasks)) {
      $rtn.= $task->get_summary($task_options, $hierarchical, $child_filter, $format, 0);
    }


    if ($format == "html" && $rtn != "") {
      #$header = "<table width=\"100%\">";
      #$footer = "</table>";

      if ($task_options["column_headings"]) {
        $header.= "<tr>";
        $header.= "  <td class=\"tasks\">Task</td>";
        if ($task_options["show_person"]) {
          $header.= "  <td class=\"tasks\">Person</td>";
        }
        if ($task_options["show_project"]) {
          $header.= "  <td class=\"tasks\">Project</td>";
        }
        if ($task_options["show_project_short"]) {
          $header.= "  <td class=\"tasks\">Project</td>";
        }
        if ($task_options["show_priorities"]) {
          $header.= "  <td class=\"tasks\">Task Priority</td>";
          $header.= "  <td class=\"tasks\">Project Priority</td>";
          $header.= "  <td class=\"tasks\">Days Due</td>";
          $header.= "  <td class=\"tasks\">Priority</td>";
          $header.= "  <td class=\"tasks\">Status</td>";
        }
        $header.= "</tr>";
      }
      $rtn = "$header$rtn$footer";
    }

    return $rtn;
  }
}
// }}}

// class task_filter extends db_filter {{{
class task_filter extends db_filter {
  var $classname = "task_filter";


  function task_filter($filter_values = "") {

    $weeks = $filter_values;    // doh no good dirty hack.
    if ($weeks == "") {
      $weeks = 1;
    }
    $weeks_to_show_date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + (7 * $weeks), date("Y")));
    $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 2, date("Y")));
    $weeks_to_show_query = "((task.dateTargetStart <= '".$weeks_to_show_date."' or task.dateActualStart <= '";
    $weeks_to_show_query.= $weeks_to_show_date."') AND (task.dateTargetStart >= '".$yesterday."' OR task.dateActualStart >= '";
    $weeks_to_show_query.= $yesterday."')) ";
    // die($weeks_to_show_query);

    if (date("D") == "Mon") {
      $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 4, date("Y")));
    } else {
      $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
    }

    $this->elements = array("completed"=>new boolean_sql_filter_element("completed", "Completed"
                                                   ,"(task.dateActualCompletion IS NOT NULL AND task.dateActualCompletion != '')")
                           ,"not_completed"=>new boolean_sql_filter_element("not_completed", "Incomplete"
                                                   ,"(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '')")
                           ,"in_progress"=>new boolean_sql_filter_element("in_progress", "In Progress"
                                                   ,"(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') 
                                                     AND (task.dateActualStart IS NOT NULL AND task.dateActualStart != '')")
                           ,"overdue"=>new boolean_sql_filter_element("overdue", "Overdue", $this->get_overdue_element_sql())
                           ,"top"=>new boolean_sql_filter_element("top", "Top Level", "parentTaskID = 0")
                           ,"phase"=>new boolean_sql_filter_element("top", "Top Level", "taskTypeID = ".TT_PHASE)
                           ,"fault"=>new boolean_sql_filter_element("top", "Top Level", "taskTypeID = ".TT_FAULT)
                           ,"message"=>new boolean_sql_filter_element("message", "Messages", "task.dateActualCompletion IS NULL 
                                                                                              AND task.taskTypeID = ".TT_MESSAGE)
                           ,"project"=>new foreign_key_filter_element("project", "Project", "task.projectID")
                           ,"projects"=>new foreign_key_filter_array("projects", "Projects", "task.projectID")
                           ,"all_pm"=>new boolean_sql_filter_element("all_pm", "")
                           ,"all_tsm"=>new boolean_sql_filter_element("all_tsm", "")
                           ,"parent_task"=>new foreign_key_filter_element("parent_task", "Parent Task", "parentTaskID")
                           ,"person"=>new foreign_key_filter_element("person", "Person Responsible", "personID")
                           ,"dateActualCompletion"=>new field_filter_element("dateActualCompletion", "Date Completed", "task.dateActualCompletion")
                           ,"due_today"=>new boolean_sql_filter_element("due_today", "Due Today", "task.dateActualCompletion IS NULL 
                                                                  AND task.dateTargetCompletion = '".date("Y-m-d")."'")
                           ,"weeks_to_show"=>new boolean_sql_filter_element("weeks_to_show", "Range of weeks", $weeks_to_show_query)
                           ,"new"=>new boolean_sql_filter_element("new", "New Tasks", "task.dateActualCompletion IS NULL AND task.dateCreated >= '".$past."'")
      );
  }



  // Returns the SQL used to filter for overdue tasks
  function get_overdue_element_sql() {
    $today = date("Y-m-d");
    $criteria = "(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') 
                 AND (task.dateTargetCompletion IS NOT NULL AND task.dateTargetCompletion != '' AND '".date("Y-m-d")."' > task.dateTargetCompletion)";

    return $criteria;
  }



//   function read_form() {{{ should be called **SET** form duh!!!
  function read_form() {
    global $personID, $current_user, $taskStatus, $taskType, $projectID;

    if (!have_entity_perm("task", PERM_READ, $current_user, false)) {
      $person = $current_user;
    } else if ($personID != "") {
      $person = new person;
      $person->set_id($personID);
      $person->select();
    }
    $this->set_element("person", $person);

    if (!isset($taskStatus)) {
      $taskStatus = "not_completed";
    }

    if ($taskStatus == "completed") {
      $this->set_element("completed", true);
    } else if ($taskStatus == "not_completed") {
      $this->set_element("not_completed", true);
    } else if ($taskStatus == "in_progress") {
      $this->set_element("in_progress", true);
    } else if ($taskStatus == "overdue") {
      $this->set_element("overdue", true);
    }

    if ($taskType) {
      $this->set_element($taskType, true);
    }

    // we're using the 'projects' element here instead of the 'project' element for consistency
    if ($projectID && $projectID != "ALL_PM" && $projectID != "ALL_TSM") {
      $this->set_element("projects", array($projectID));

    // Add a project filter to restrict the list to projects for which the user is manager.
    } else if ($projectID == "ALL_PM") {
      $db = new db_alloc;
      $q = sprintf("SELECT projectID 
                      FROM projectPerson 
                 LEFT JOIN projectPersonRole on projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE personID = '%d' AND projectPersonRole.projectPersonRoleHandle = 'isManager' GROUP BY projectID"
                  ,$current_user->get_id());
      $db->query($q);
      while($db->next_record()) {
        $user_projects[] = $db->f("projectID");
      }
      if (is_array($user_projects)) {
        $this->set_element("projects", $user_projects);
      }
      $this->set_element("all_pm",true);

    } else if ($projectID == "ALL_TSM") {
      $db = new db_alloc;
      $q = sprintf("SELECT projectID 
                      FROM projectPerson 
                 LEFT JOIN projectPersonRole on projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE personID = '%d' AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient' GROUP BY projectID"
                  ,$current_user->get_id());
      $db->query($q);
      while($db->next_record()) {
        $user_projects[] = $db->f("projectID");
      }
      if (is_array($user_projects)) {
        $this->set_element("projects", $user_projects);
      }
      $this->set_element("all_tsm",true);
    }

  }
// }}}

}
// }}}

// class prioritised_task_list extends task_list {{{
class prioritised_task_list extends task_list {
  var $table = "task LEFT JOIN project on task.projectID = project.projectID";

  // The SQL below uses the principals to calculate a 'priority factor'.
  // The due date based priority is a number between 0 (infinitely overdue) and 8 (infinite days to complete).
  // A due date based priority of 4 is assigned to a task due right now.
  // This is calculated using tan-1(days until due / 20) then normalised to give a value between 0 and 8.
  // Where the number 20 can be increased to reduce the rate at which the value approaches the extremes of 0 and 8.
  // Overall priority factor = task priority * square of project priority * due date based priority factor / 10
  // This gives a number between 0 and 100 where 0 = absolutely urgent and 100 = don't bother
  var $fields = "task.*, projectPriority, 
		               IF(task.dateTargetCompletion IS NULL, \"-\",
                                  TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) as daysUntilDue,
		               priority * POWER(projectPriority, 2) * 
		               IF(
		                 task.dateTargetCompletion IS NULL, 8,
		                 ATAN(
		                   (
		                     TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())
		                   ) / 20
		                 ) / 3.14 * 8 + 4
		               ) / 10 as priorityFactor";
  var $order_by = "priorityFactor";
}
// }}}

?>
