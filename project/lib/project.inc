<?php
define("PERM_PROJECT_VIEW_TASK_ALLOCS", 256);
define("PERM_PROJECT_ADD_TASKS", 512);

class project extends db_entity
{
  var $classname = "project";
  var $data_table = "project";
  var $display_field_name = "projectName";

  function project() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectID");
    $this->data_fields = array("projectName"=>new db_text_field("projectName")
                               , "projectShortName"=>new db_text_field("projectShortName")
                               , "projectComments"=>new db_text_field("projectComments")
                               , "clientID"=>new db_text_field("clientID")
                               , "projectType"=>new db_text_field("projectType")
                               , "projectClientName"=>new db_text_field("projectClientName")
                               , "projectClientPhone"=>new db_text_field("projectClientPhone")
                               , "projectClientMobile"=>new db_text_field("projectClientMobile")
                               , "projectClientEMail"=>new db_text_field("projectClientEMail")
                               , "projectClientAddress"=>new db_text_field("projectClientAddress")
                               , "dateTargetStart"=>new db_text_field("dateTargetStart")
                               , "dateTargetCompletion"=>new db_text_field("dateTargetCompletion")
                               , "dateActualStart"=>new db_text_field("dateActualStart")
                               , "dateActualCompletion"=>new db_text_field("dateActualCompletion")
                               , "projectBudget"=>new db_text_field("projectBudget")
                               , "currencyType"=>new db_text_field("currencyType")
                               , "projectPriority"=>new db_text_field("projectPriority")
                               , "projectStatus"=>new db_text_field("projectStatus")
                               , "is_agency"=>new db_text_field("is_agency")
                               , "cost_centre_tfID"=>new db_text_field("cost_centre_tfID")
                               , "customerBilledDollars"=>new db_text_field("customerBilledDollars")
      );
    $this->permissions[PERM_PROJECT_VIEW_TASK_ALLOCS] = "View task allocations";
    $this->permissions[PERM_PROJECT_ADD_TASKS] = "Add tasks";
  }

  function get_tasks($existing_filter = "") {
    if ($existing_filter == "") {
      $filter = new task_filter();
    } else {
      $filter = $existing_filter;
    }
    $filter->set_element("project", $this);
    $list = new task_list($filter);
    return $list->get_entity_array();
  }

  function get_task_filter($show_weeks = 0) {
    $filter = new task_filter($show_weeks);
    $filter->set_element("project", $this);
    return $filter;
  }

  function get_top_tasks($existing_filter = "") {
    if ($existing_filter == "") {
      $filter = new task_filter();
    } else {
      $filter = $existing_filter;
    }
    $filter->set_element("top", true);
    return $this->get_tasks($filter);
  }

  function get_current_top_tasks($extra_criteria = "") {
    if ($extra_criteria) {
      $extra_criteria.= " AND ";
    }
    $extra_criteria.= " parentTaskID = 0";
    return $this->get_current_tasks($extra_criteria);
  }

  function get_current_tasks($extra_criteria = "") {

    if ($extra_criteria) {
      $criteria.= " AND ($extra_criteria)";
    }
    return $this->get_tasks($criteria);
  }

  function get_url() {
    global $sess;
    return $sess->email_url(get_url_path()."project.php?projectID=".$this->get_id());
  }

  function get_hierarchical_task_summary($root_taskID=0, $extra_sql="") {

    $db = new db_alloc;
    $q = sprintf("SELECT leftID, rightID FROM task WHERE taskID=%d",$root_taskID); 
    $db->query($q);
    $row = $db->next_record();

    // start with an empty $right stack 
    $right = array(); 

    // now, retrieve all descendants of the $root node 
    $q = sprintf("SELECT taskID, leftID, rightID, taskName
                    FROM task 
                   WHERE leftID BETWEEN %d AND %d and dateActualCompletion = '' or dateActualCompletion is null
                     AND projectID = %d
                         %s 
                ORDER BY leftID ASC", $row["leftID"],$row["rightID"],$this->get_id(), $extra_sql); 
    $db->query($q);

    while ($db->next_record()) {


/*
echo "<br/>Tally:".count($right) . " innards: <pre>".print_r($right,1)."</pre>";
echo "<br/>rightcountrigh: ".$right[count($right)-1];
echo "<br/><br/>";
$k++;
$k>10 && exit;
        $test = $right[count($right)-1];
        while ($test < $db->f("rightID") && count($right)) {

          $test = $right[count($right)-1];
          array_pop($right); 
        } 
*/


      if (count($right)) {
        
        if ($db->f("leftID") == $prev_left+1) {
          $padding++;
        } 

        foreach ($right as $rightID => $p) {
          if ($db->f("right")+1 == $rightID && $p>0) {
            $padding = $p;
          }
        }

      }


      $right[$prev_right] = $padding;

      $prev_right = $db->f("rightID");
      $prev_left = $db->f("leftID");


      $task = new task;
      $task->read_db_record($db);
      // display indented node title 
      $space = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$padding);
      $rtn.= "<tr><td colspan=2>".$padding.$space.$task->get_value("leftID").$task->get_task_link().$task->get_value("rightID")."</td></tr>"; 



    }  

    return $rtn;
  }



  function get_task_summary($existing_filter = "", $task_options = "", $hierarchical = true, $format = "html") {
    global $default_task_options;
    if ($existing_filter == "") {
      $filter = new task_filter();
    } else {
      $filter = $existing_filter;
    }
    $filter->set_element("project", $this);
    if ($hierarchical) {
      $filter->set_element("top", true);
    }
    if ($task_options == "") {
      $task_options = $default_task_options;
    }
    #if ($this->have_perm(PERM_PROJECT_VIEW_TASK_ALLOCS) && !is_object($filter->get_element("person"))) {
      #$task_options["show_person"] = true;
    #} else {
      $task_options["show_person"] = false;
    #}

    $list = new task_list($filter);
    $summary = $list->get_task_summary($task_options, $hierarchical, $format);
    return $summary;
  }

  function is_owner($person = "") {
    global $current_user;
    $person or $person = $current_user;

    // If brand new record then let it be created.
    if (!$this->get_id())
      return true;

    // Else check that user has isManager permission for this project
    return $this->has_project_permission($person, array("isManager"));
  }

  function has_project_permission($person = "", $permissions = array()) {
    // Check that user has permission for this project
    global $current_user;
    $person or $person = $current_user;
    $permissions and $p = " AND ppr.projectPersonRoleHandle in ('".implode("','",$permissions)."')";

    $query = sprintf("SELECT personID, projectID, pp.projectPersonRoleID, ppr.projectPersonRoleName, ppr.projectPersonRoleHandle 
                        FROM projectPerson pp 
                   LEFT JOIN projectPersonRole ppr ON ppr.projectPersonRoleID = pp.projectPersonRoleID 
                       WHERE projectID = '%d' and personID = '%d' %s"
                    ,$this->get_id(), $person->get_id(), $p);
    #echo "<br><br>".$query;

    $db = new db_alloc;
    $db->query($query);
    return $db->next_record();
  }

  function get_timeSheetRecipients() {
    $rows = array();
    $q = sprintf("SELECT projectPerson.personID as personID
                    FROM projectPerson
               LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID 
                   WHERE projectPerson.projectID = %d AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient'",$this->get_id());
    $db = new db_alloc;
    $db->query($q);
    while ($db->next_record()) {
      $rows[] = $db->f("personID");
    }
    return $rows;
  }



  function get_navigation_links() {
    global $taskID, $TPL, $current_user;
 
    // Client 
    if ($this->get_value("clientID")) {  
      $url = $TPL["url_alloc_client"]."&clientID=".$this->get_value("clientID");
      $links[] = "<a href=\"$url\">Client</a>";
    }

    // Tasks
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_taskSummary"]."&applyFilter=1&taskStatus=not_completed&view=byProject&projectID=".$this->get_id();
      $links[] = "<a href=\"$url\">Tasks</a>";
    } 

    // Graph
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_projectSummary"]."&projectID=".$this->get_id();
      $links[] = "<a href=\"$url\">Graph</a>";
    }

    // Allocation
    if ($this->have_perm(PERM_PROJECT_VIEW_TASK_ALLOCS)) {
      $url = $TPL["url_alloc_personGraphs"]."&projectID=".$this->get_id();
      $links[] = "<a href=\"$url\">Allocation</a>";
    } 

    // New Task
    if ($this->have_perm(PERM_PROJECT_ADD_TASKS)) {
      $url = $TPL["url_alloc_task"]."&projectID=".$this->get_id();
      $links[] = "<a href=\"$url\">New Task</a>";
    }

    // Join links up with space and html no-breaks
    if (is_array($links)) {
      return "<nobr>&nbsp;&nbsp;".implode("</nobr><nobr>&nbsp;&nbsp;",$links)."</nobr>";
    }
  }

  function get_project_type_query($type="mine") {
    $type or $type = "mine";
    global $current_user;

    if ($type == "mine") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$current_user->get_id());

    } else if ($type == "pm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                       AND projectPersonRole.projectPersonRoleHandle = 'isManager' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$current_user->get_id());

    } else if ($type == "tsm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                       AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$current_user->get_id());

    } else if ($type == "curr") {
      $q = sprintf("SELECT projectID,projectName FROM project WHERE project.projectStatus = 'current' ORDER BY projectName");

    } else if ($type == "all") {
      $q = sprintf("SELECT projectID,projectName FROM project ORDER BY projectName");
    }
    return $q;
  }

  function get_project_list_dropdown($type="mine",$projectIDs=array()) {
    $db = new db_alloc;
    $q = project::get_project_type_query($type);
    // Project dropdown
    $db->query($q);
    while ($db->next_record()) {
      $ops[$db->f("projectID")] = $db->f("projectName");
    }

    $options = get_select_options($ops, $projectIDs, 35);

    return "<select name=\"projectID[]\" size=\"5\" style=\"width:275px;\" multiple=\"true\">".$options."</select>";
  }



}




class project_list extends db_list
{
  var $table = "project";
  var $entity_class = "project";
  var $fields = "project.*";
  var $order_by = "projectName";
}


?>
