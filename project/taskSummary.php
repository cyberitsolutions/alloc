<?php
include("alloc.inc");

$page_vars = array("projectID","taskStatus","taskTypeID","personID","taskView", "showDetails","projectType","applyFilter");
$_FORM = get_all_form_data($page_vars);

if (!$_FORM["applyFilter"]) {
  $_FORM = &$user_FORM;
} else if ($_FORM["applyFilter"]) {
  $user_FORM = &$_FORM;
  $user->register("user_FORM");
}

$db = new db_alloc;


// They want to search on all projects that belong to the projectType they've radio selected
if ($_FORM["projectType"] && !$_FORM["projectID"]) {

  if ($_FORM["projectType"] != "all") {
    $q = project::get_project_type_query($_FORM["projectType"]);
    $db->query($q);
    while ($db->next_record) {
      $user_projects[] = $db->f("projectID");
    }
  }

// Else if they've selected projects
} else if ($_FORM["projectID"] && is_array($_FORM["projectID"])) {
  $user_projects = $_FORM["projectID"];

// Else a project has been specified in the url
} else if ($_FORM["projectID"]) {
  $user_projects[] = $_FORM["projectID"];
}


// Join them up with commars and add a restrictive sql clause subset
if (is_array($user_projects) && count($user_projects)) {
  $extra_sql = "WHERE projectID IN (".implode(",",$user_projects).")";
}


if ($_FORM["taskView"] == "byProject") {
  $q = "SELECT projectID, projectName, clientID FROM project ".$extra_sql. " ORDER BY projectName";
  $db = new db_alloc;
  $db->query($q);
  while ($db->next_record()) {
    
    $project = new project;
    $project->read_db_record($db);

    $summary.= "\n<tr>";
    $summary.= "\n<td class=\"tasks\"><a href=\"".$project->get_url()."\"><strong>".$project->get_value("projectName")."</strong></a></td>";
    $summary.= "\n<td class=\"tasks_r\" colspan=\"2\"><strong>". $project->get_navigation_links(). "</strong></td>\n</tr>";

    // Pass filter elements!!: personID, taskTypeID, the status of the task
    $summary.= "\n".$project->get_hierarchical_task_summary(0);
    $summary.= "\n<tr><td colspan=\"3\">&nbsp;</td></tr>";
  }

  $TPL["task_summary"] = $summary;
  

} else if ($_FORM["taskView"] == "prioritised") {
  

}







// Load up the filter bits
$TPL["projectOptions"] = project::get_project_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);
$_FORM["projectType"] and $TPL["projectType_checked_".$_FORM["projectType"]] = " checked"; 

$TPL["personOptions"] = "<option value=\"\"> -- ALL -- ";
$TPL["personOptions"].= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

$taskType = new taskType;
$TPL["taskTypeOptions"] = $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);

$taskViews = array("byProject"=>"View By Project", "prioritised"=>"View By Priority");
$TPL["taskViewOptions"] = get_options_from_array($taskViews, $_FORM["taskView"]);

$taskStatii = array("completed"=>"Completed","not_completed"=>"Not Completed","in_progress"=>"In Progress","overdue"=>"Overdue");
$TPL["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);

if ($_FORM["showDetails"]) {
  $TPL["show_details_checked"] = " checked";
}



include_template("templates/taskSummaryM.tpl");
page_close();
?>
