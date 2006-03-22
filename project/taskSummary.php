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
                  );
$_FORM = get_all_form_data($page_vars);

if (!$_FORM["applyFilter"]) {
  $_FORM = &$user_FORM;
} else if ($_FORM["applyFilter"]) {
  $user_FORM = &$_FORM;
  $user->register("user_FORM");
}

$db = new db_alloc;
$filter = array();


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


// Join them up with commars and add a restrictive sql clause subset
if (is_array($user_projects) && count($user_projects)) {
  $extra_sql = "WHERE projectID IN (".implode(",",$user_projects).")";
}


// Task level filtering
if ($_FORM["taskStatus"]) {

  $taskStatusFilter = array("completed"=>"(task.dateActualCompletion IS NOT NULL AND task.dateActualCompletion != '')"
                           ,"not_completed"=>"(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '')"
                           ,"in_progress"=>"((task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') AND (task.dateActualStart IS NOT NULL AND task.dateActualStart != ''))"
                           ,"overdue"=>"((task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') 
                                         AND 
                                         (task.dateTargetCompletion IS NOT NULL AND task.dateTargetCompletion != '' AND '".date("Y-m-d")."' > task.dateTargetCompletion))"
                           );
  $filter[] = $taskStatusFilter[$_FORM["taskStatus"]];
}


if (count($_FORM["taskTypeID"]==1) && !$_FORM["taskTypeID"][0]) {
  $_FORM["taskTypeID"] = "";
}

if (is_array($_FORM["taskTypeID"]) && count($_FORM["taskTypeID"])) {
  $filter[] = "(taskTypeID in (".implode(",",$_FORM["taskTypeID"])."))";

} else if ($_FORM["taskTypeID"]) {
  $filter[] = sprintf("(taskTypeID = %d)",$_FORM["taskTypeID"]);
}

if ($_FORM["personID"]) {
  $filter[] = sprintf("(personID = %d)",$_FORM["personID"]);
}


$people_cache = get_cached_table("person");
$timeUnit_cache = get_cached_table("timeUnit");




if ($_FORM["taskView"] == "byProject") {

  $summary = "\n<tr><td></td>";

  $_FORM["showCreator"]  and $summary.= "\n<td>Task Creator</td>";
  $_FORM["showAssigned"] and $summary.= "\n<td>Assigned To</td>"; 
  $_FORM["showTimes"]    and $summary.= "\n<td>Estimate</td>";
  $_FORM["showTimes"]    and $summary.= "\n<td>Actual</td>";
  $_FORM["showDates"]    and $summary.= "\n<td>Targ Start</td>";
  $_FORM["showDates"]    and $summary.= "\n<td>Targ Compl</td>";
  $_FORM["showDates"]    and $summary.= "\n<td>Act Start</td>";
  $_FORM["showDates"]    and $summary.= "\n<td>Act Compl</td>";
  $_FORM["showPercent"]  and $summary.= "\n<td>%</td>";

  $summary.="\n</tr>";
  


  $q = "SELECT projectID, projectName, clientID FROM project ".$extra_sql. " ORDER BY projectName";
  $db = new db_alloc;
  $db->query($q);
  while ($db->next_record()) {
    
    $project = new project;
    $project->read_db_record($db);
  
    $tasks = $project->get_task_children(0,$filter,1);
    
    if (count($tasks)) {
      $summary.= "\n<tr>";
      $summary.= "\n  <td class=\"tasks\"><a href=\"".$project->get_url()."\"><strong>".$project->get_value("projectName")."</strong></a></td>";
      $summary.= "\n  <td class=\"tasks_r\" colspan=\"8\"><strong>". $project->get_navigation_links(). "</strong></td>";
      $summary.= "\n</tr>";
      foreach ($tasks as $task) {
        $summary.= "\n<tr>";
        $summary.= "\n  <td style=\"padding-left:".($task["padding"]*15)."\">".$task["taskLink"]."</td>";
        $_FORM["showCreator"]  and $summary.= "\n<td>".$people_cache[$task["creatorID"]]["name"]."</td>";
        $_FORM["showAssigned"] and $summary.= "\n<td>".$people_cache[$task["personID"]]["name"]."</td>";
        $estime = $task["timeEstimate"]; $task["timeEstimateUnitID"] and $estime.= " ".$timeUnit_cache[$task["timeEstimateUnitID"]]["timeUnitLabelA"];
        $actual = task::get_time_billed($task["taskID"]); 
        $_FORM["showTimes"]    and $summary.= "\n<td>".$estime."</td>";
        $_FORM["showTimes"]    and $summary.= "\n<td>".$actual."</td>";
        $_FORM["showDates"]    and $summary.= "\n<td>".$task["dateTargetStart"]."</td>";
        $_FORM["showDates"]    and $summary.= "\n<td>".$task["dateTargetCompletion"]."</td>";
        $_FORM["showDates"]    and $summary.= "\n<td>".$task["dateActualStart"]."</td>";
        $_FORM["showDates"]    and $summary.= "\n<td>".$task["dateActualCompletion"]."</td>";
        $_FORM["showPercent"]  and $summary.= "\n<td>".sprintf("%d",$task["percentComplete"])."%</td>";
        $summary.= "\n</tr>";
        $_FORM["showDescription"] and $summary.="\n<tr><td style=\"padding-left:".($task["padding"]*15)."\" colspan=\"10\">".$task["taskDescription"]."</td></tr>";
      }
      $summary.= "\n<tr><td colspan=\"3\">&nbsp;</td></tr>";
    }
  }

  if (count($tasks)) {
    $TPL["task_summary"] = $summary;
  } else {
    $TPL["task_summary"] = "<tr><td colspan=\"10\" align=\"center\"><b>No Tasks Found</b></td></tr>";
  }
  

} else if ($_FORM["taskView"] == "prioritised") {
  

}







// Load up the filter bits
$TPL["projectOptions"] = project::get_project_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);
$_FORM["projectType"] and $TPL["projectType_checked_".$_FORM["projectType"]] = " checked"; 

$TPL["personOptions"] = "\n<option value=\"\"> ";
$TPL["personOptions"].= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

$taskType = new taskType;
$TPL["taskTypeOptions"] = "\n<option value=\"\"> ";
$TPL["taskTypeOptions"].= $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);

$_FORM["taskView"] and $TPL["taskView_checked_".$_FORM["taskView"]] = " checked";

$taskStatii = array(""=>"","not_completed"=>"Not Completed","in_progress"=>"In Progress","overdue"=>"Overdue","completed"=>"Completed");
$TPL["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);

$_FORM["showDescription"] and $TPL["showDescription_checked"]  = " checked";
$_FORM["showDates"]       and $TPL["showDates_checked"]        = " checked";
$_FORM["showCreator"]     and $TPL["showCreator_checked"]      = " checked";
$_FORM["showAssigned"]    and $TPL["showAssigned_checked"]     = " checked";
$_FORM["showTimes"]       and $TPL["showTimes_checked"]        = " checked";
$_FORM["showPercent"]     and $TPL["showPercent_checked"]      = " checked";



include_template("templates/taskSummaryM.tpl");
page_close();
?>
