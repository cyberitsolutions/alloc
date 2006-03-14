<?php
include("alloc.inc");

function show_task_summary() {
  global $current_user, $task_filter, $taskView, $current_user, $TPL, $show_details;

  // By Priority View
  if ($taskView == "prioritised") {

    $task_list = new prioritised_task_list($task_filter);
    $task_options = array("show_project_short"=>!is_object($task_filter->get_element("project"))
                         ,"show_person"=>!is_object($task_filter->get_element("person"))
                         ,"show_links"=>true
                         ,"skip_indent"=>true
                         ,"show_priorities"=>true
                         ,"column_headings"=>true);
    $summary = $task_list->get_task_summary($task_options, false);

  // By Project View
  } else {
    $task_options = array("show_links"=>true
                         ,"show_new_children_links"=>true
                         ,"nobr_taskName"=>true
                         ,"status_type"=>"none");

    if (!is_object($task_filter->get_element("person"))) {
      $task_options["show_person"] = true;
    }

    if ($show_details) {
      $task_options["show_details"] = true;
    }
  

    if (is_array($task_filter->get_element("projects"))) {
      $project_ids = $task_filter->get_element("projects");
      foreach($project_ids as $pid) {
        $p = new project;
        $p->set_id($pid);
        $p->select();
        $projects[] = $p;
      } 
      
    } else {
      // Irrespective of whether the project list has a filter, load up the projects.
      $project_list = new project_list();
      $projects = $project_list->get_entity_array();
    }

    $summary = "";
    reset($projects);
    while (list(, $project) = each($projects)) {
  
      // if top-level is selected then only show the tasks at top level - note the deliberate reset of top -> true 
      if ($task_filter->get_element("top") == true) {
        $project_summary = $project->get_task_summary($task_filter, $task_options, false, "html");
        $task_filter->set_element("top", true);
      
      // normal hierarchal view 
      } else {
        $project_summary = $project->get_task_summary($task_filter, $task_options, true, "html");
      }

      if ($project_summary) {
        $summary.= "\n<tr>";
        $summary.= "\n<td class=\"tasks\"><a href=\"".$project->get_url()."\"><strong>".$project->get_value("projectName")."</strong></a></td>";
        $summary.= "\n<td class=\"tasks_r\" colspan=\"2\"><strong>". $project->get_navigation_links(). "</strong></td>\n</tr>";
        $summary.= $project_summary;
        $summary.= "\n<tr><td colspan=\"3\">&nbsp;</td></tr>";
      }
    }
  }

  echo stripslashes($summary);
}


#global $user_taskSummary_filter, $user_taskSummary_view, $task_filter;

// If they have clicked the "Filter" button
if (isset($applyFilter)) {
  // Create a new filter for the user based on form values
  $task_filter = new task_filter();
  $task_filter->read_form();
  $user_taskSummary_filter = &$task_filter;
  $user->register("user_taskSummary_filter");

  // Set session variable to track view
  $user_taskSummary_view = $taskView or $user_taskSummary_view = "byProject";
  $user->register("user_taskSummary_view");

  //Ditto for details
  $user_taskSummary_showDetails = $show_details;
  $user->register("user_taskSummary_showDetails");
} 

// Else use previous session settings
if (!is_object($task_filter)) {
  $task_filter = &$user_taskSummary_filter;
  $taskView = $user_taskSummary_view;
  $show_details = $user_taskSummary_showDetails;
}

// Else create a new filter for the user based on default values
if (!is_object($task_filter)) {
  $task_filter = new task_filter();
  $task_filter->set_element("not_completed", true);
  $task_filter->set_element("person", $current_user);
  $user_taskSummary_filter = $task_filter;
}

// If we're passed a personID on the URL then filter for that users tasks
if ($_GET["personID"]) {
  $p = new person;
  $p->set_id($_GET["personID"]);
  $p->select();
  $task_filter->set_element("person", $p);
  $user_taskSummary_filter = $task_filter;
}



$TPL["filter_form"] = $task_filter->get_form();
$TPL["person_username"] = "All Users";

include_template("templates/taskSummaryM.tpl");
page_close();
?>
