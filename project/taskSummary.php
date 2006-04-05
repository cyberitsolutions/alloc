<?php
include("alloc.inc");


$page_vars = array("projectID"
                  ,"taskStatus"
                  ,"taskTypeID"
                  ,"personID"
                  ,"taskView"
                  ,"projectType"
                  ,"applyFilter"
                  ,"showDescription"
                  ,"showDates"
                  ,"showCreator"
                  ,"showAssigned"
                  ,"showTimes"
                  ,"showPercent"
                  ,"showPriority"
                  ,"showStatus"
                  );

$_FORM = get_all_form_data($page_vars);

if (!$_FORM["applyFilter"]) {
  $_FORM = &$user_FORM;
} else if ($_FORM["applyFilter"]) {
  $user_FORM = &$_FORM;
  is_object($current_user) and $current_user->prefs["user_FORM"] = &$user_FORM;
}

$db = new db_alloc;


// They want to search on all projects that belong to the projectType they've radio selected
if ($_FORM["projectType"] && !$_FORM["projectID"]) {

  if ($_FORM["projectType"] != "all") {
    $q = project::get_project_type_query($_FORM["projectType"]);
    $db->query($q);
    while ($db->next_record()) {
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


$_FORM["projectIDs"] = $user_projects;
$_FORM["showHeader"] = true;
$_FORM["showProject"] = true;
$_FORM["padding"] = 1;

// Get task list
$TPL["task_summary"] = task::get_task_list($_FORM);


// Load up the filter bits
$TPL["projectOptions"] = project::get_project_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);
$_FORM["projectType"] and $TPL["projectType_checked_".$_FORM["projectType"]] = " checked"; 

$TPL["personOptions"] = "\n<option value=\"\"> ";
$TPL["personOptions"].= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

$taskType = new taskType;
$TPL["taskTypeOptions"] = "\n<option value=\"\"> ";
$TPL["taskTypeOptions"].= $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);

$_FORM["taskView"] or $_FORM["taskView"] = "byProject";
$_FORM["taskView"] and $TPL["taskView_checked_".$_FORM["taskView"]] = " checked";

$taskStatii = array(""=>"","not_completed"=>"Not Completed","in_progress"=>"In Progress","overdue"=>"Overdue","completed"=>"Completed");
$TPL["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);

$_FORM["showDescription"] and $TPL["showDescription_checked"] = " checked";
$_FORM["showDates"]       and $TPL["showDates_checked"]       = " checked";
$_FORM["showCreator"]     and $TPL["showCreator_checked"]     = " checked";
$_FORM["showAssigned"]    and $TPL["showAssigned_checked"]    = " checked";
$_FORM["showTimes"]       and $TPL["showTimes_checked"]       = " checked";
$_FORM["showPercent"]     and $TPL["showPercent_checked"]     = " checked";
$_FORM["showPriority"]    and $TPL["showPriority_checked"]    = " checked";
$_FORM["showStatus"]      and $TPL["showStatus_checked"]      = " checked";



include_template("templates/taskSummaryM.tpl");
page_close();
?>
