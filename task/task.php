<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

require_once("../alloc.php");
define("PAGE_IS_PRINTABLE",1);

  function show_task_children($template) {
    global $TPL;
    global $task;
    if ($task->get_value("taskTypeID") == "Parent") {
      $options["parentTaskID"] = $task->get_id();
      $options["taskView"] = "byProject";
      $task->get_value("projectID") and $options["projectIDs"][] = $task->get_value("projectID");
      $options["showDates"] = true;
      #$options["showCreator"] = true;
      $options["showAssigned"] = true;
      $options["showPercent"] = true;
      $options["showHeader"] = true;
      $options["showTimes"] = true;

      $_GET["media"] == "print" and $options["showDescription"] = true;
      $_GET["media"] == "print" and $options["showComments"] = true;
      $TPL["taskListRows"] = task::get_list($options);
      $TPL["taskListOptions"] = $options;

      include_template($template);
    }
  }

  function get_parent_taskIDs($taskID) {
    $q = prepare("SELECT taskID,taskName,parentTaskID 
                    FROM task 
                   WHERE taskID = %d 
                     AND (taskID != parentTaskID OR parentTaskID IS NULL)"
                ,$taskID);
    $db = new db_alloc();
    $db->query($q);
    
    while($db->next_record()) {
      $rtn[$db->f("taskName")] = $db->f("taskID"); 
      $arr = get_parent_taskIDs($db->f("parentTaskID"));
      if (is_array($arr)) {
        $rtn = array_merge($rtn, $arr);
      }
    }
    return $rtn;
  }

  function show_attachments() {
    global $taskID;
    util_show_attachments("task",$taskID);
  }

  function show_comments() {
    global $taskID;
    global $TPL;
    global $task;

    if ($_REQUEST["commentSummary"]) {
      $_REQUEST["showTaskHeader"] = true;
      $_REQUEST["clients"] = true;
      $TPL["commentsR"] = comment::get_list_summary($_REQUEST);
      $TPL["extra_page_links"] = '<a href="'.$TPL["url_alloc_task"].'taskID='.$TPL["task_taskID"].'&sbs_link=comments">Full</a>';
    } else {
      $TPL["commentsR"] = comment::util_get_comments("task",$taskID);
      $TPL["extra_page_links"] = '<a href="'.$TPL["url_alloc_task"].'taskID='.$TPL["task_taskID"];
      $TPL["extra_page_links"].= '&sbs_link=comments&commentSummary=true&maxCommentLength=50000000">Summary</a>';
    }
    $TPL["commentsR"] and $TPL["class_new_comment"] = "hidden";
    $TPL["allParties"] = $task->get_all_parties($task->get_value("projectID")) or $TPL["allParties"] = array();
    $TPL["entity"] = "task";
    $TPL["entityID"] = $task->get_id();
    if (has("project")) {
      $project = $task->get_foreign_object("project");
      $TPL["clientID"] = $project->get_value("clientID");
    }

    $commentTemplate = new commentTemplate();
    $ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"task"));
    $TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);

    include_template("../comment/templates/commentM.tpl");
  }

  function show_taskCommentsPrinter() {
    global $taskID;
    global $TPL;
    $TPL["commentsR"] = comment::util_get_comments("task",$taskID,$options);
    include_template("../comment/templates/commentP.tpl");
  }

  function show_taskHistory() {
    global $task;
    global $TPL;
    $TPL["changeHistory"] = $task->get_changes_list();
    include_template("templates/taskHistoryM.tpl");
  }


global $timeSheetID;

$db = new db_alloc();
$task = new task();

// If taskID

$taskID = $_POST["taskID"] or $taskID = $_GET["taskID"];

if (isset($taskID)) {
  // Displaying a record
  $task->set_id($taskID);
  $task->select();

// Creating a new record
} else {
  $_POST["dateCreated"] = date("Y-m-d H:i:s");
  $task->read_globals();
  $taskID = $task->get_id();
  if (has("project") && $task->get_value("projectID")) {
    $project = $task->get_foreign_object("project");
  }
}

// if someone uploads an attachment
if ($_POST["save_attachment"]) {
  move_attachment("task",$taskID);
  alloc_redirect($TPL["url_alloc_task"]."taskID=".$taskID."&sbs_link=attachments");
} 
  

// If saving a record
if ($_POST["save"] || $_POST["save_and_back"] || $_POST["save_and_new"] || $_POST["save_and_summary"] || $_POST["timeSheet_save"] || $_POST["close_task"]) {

  $task->read_globals();

  if ($_POST["close_task"]) {
    $task->set_value("taskStatus","closed_complete");
  }

  // If we're auto-nuking the pending tasks, we need to do that before the call to task->save()
  if ($task->get_id() && !$_POST["pendingTasksIDs"]) {
    $task->add_pending_tasks($_POST["pendingTasksIDs"]);
  }

  // Moved all validation over into task.inc.php save()
  $success = $task->save();

  count($msg) and $msg = "&message_good=".urlencode(implode("<br>",$msg));

  if ($success) {
    interestedParty::make_interested_parties("task",$task->get_id(),$_POST["interestedParty"]);
    $task->add_pending_tasks($_POST["pendingTasksIDs"]);
    $task->add_reopen_reminder($_POST["reopen_task"]);

    // Create reminders if necessary
    if($_POST["createTaskReminder"] == true) {
      $task->create_task_reminder();
    }
  
    if ($_POST["save"] && $_POST["view"] == "brief") {
      #$url = $TPL["url_alloc_taskList"];
      $url = $TPL["url_alloc_task"]."taskID=".$task->get_id();
    } else if ($_POST["save"] || $_POST["close_task"]) {
      $url = $TPL["url_alloc_task"]."taskID=".$task->get_id();
    } else if ($_POST["save_and_back"]) {
      $url = $TPL["url_alloc_project"]."projectID=".$task->get_value("projectID");
    } else if ($_POST["save_and_summary"]) {
      $url = $TPL["url_alloc_taskList"];
    } else if ($_POST["save_and_new"]) {
      $url = $TPL["url_alloc_task"]."projectID=".$task->get_value("projectID")."&parentTaskID=".$task->get_value("parentTaskID");
    } else if ($_POST["timeSheet_save"]) {
      $url = $TPL["url_alloc_timeSheet"]."timeSheetID=".$_POST["timeSheetID"]."&taskID=".$task->get_id();
    } else {
      alloc_error("Unexpected save button");
    }
    alloc_redirect($url.$msg);
    exit();
  }

// If deleting a record
} else if ($_POST["delete"]) {
  if ($task->can_be_deleted()) {
    $task->read_globals();
    $task->delete();
    alloc_redirect($TPL["url_alloc_taskList"]);
  } else {
    alloc_error("This task cannot be deleted. You either don't have permission, or this task has history items.");
  }
}

// Start stuff here
$task->set_values("task_");

$person = new person();
$person->set_id($task->get_value("creatorID"));
$person->select();
$TPL["task_createdBy"] = $person->get_name();
$TPL["task_createdBy_personID"] = $person->get_id();

if ($task->get_value("closerID") && $task->get_value("dateClosed")) {
  $TPL["task_closed_by"] = person::get_fullname($task->get_value("closerID"));
  $TPL["task_closed_when"] = $task->get_value("dateClosed");
}

$person = new person();
$person->set_id($task->get_value("personID"));
$person->select();
$TPL["person_username"] = $person->get_name();
$TPL["person_username_personID"] = $person->get_id();

$manager = new person();
$manager->set_id($task->get_value("managerID"));
$manager->select();
$TPL["manager_username"] = $manager->get_name();
$TPL["manager_username_personID"] = $manager->get_id();

$estimator = new person();
$estimator->set_id($task->get_value("estimatorID"));
$estimator->select();
$TPL["estimator_username"] = $estimator->get_name();
$TPL["estimator_username_personID"] = $estimator->get_id();



// If we've been sent here by a "New Message" or "New Fault" option in the Quick List dropdown
if (!$taskID && $_GET["tasktype"]) {
  $task->set_value("taskTypeID", $_GET["tasktype"]);
}

// If we've been sent here by a "New Task" link from the calendar
if (!$taskID && $_GET["dateTargetStart"]) {
  $TPL["task_dateTargetStart"] = $_GET["dateTargetStart"];
  $task->set_value("personID", $_GET["personID"]);
}


// Set options for the dropdown boxen
$task->set_option_tpl_values();

$time_billed = $task->get_time_billed(false);
$time_billed_label = seconds_to_display_format($time_billed);
if ($time_billed != "") {
  $TPL["time_billed_link"] = "<a href=\"".$TPL["url_alloc_timeSheetList"]."taskID=".$task->get_id()."&dontSave=true&applyFilter=true\">".$time_billed_label."</a>";
} 

$TPL["task_timeLimit"]    or $TPL["task_timeLimit"] = "";
$TPL["task_timeBest"]     or $TPL["task_timeBest"] = "";
$TPL["task_timeWorst"]    or $TPL["task_timeWorst"] = "";
$TPL["task_timeExpected"] or $TPL["task_timeExpected"] = "";
$TPL["percentComplete"] = $task->get_percentComplete();


// Generate navigation links
if (has("project") && $task->get_id()) {
  $project = $task->get_foreign_object("project");
  $project->set_values("project_");
  if ($project->get_id()) {
    $ops["taskID"] = $task->get_id();
    $ops["showProject"] = true;
    $TPL["navigation_links"] = $project->get_navigation_links($ops);
  }
}

$parent_task = $task->get_foreign_object("task", "parentTaskID");
$parent_task->set_values("parentTask_");

$TPL["taskType_taskTypeID"] = $task->get_value("taskTypeID");

$q = prepare("SELECT clientID FROM project LEFT JOIN task ON task.projectID = project.projectID WHERE taskID = %d",$task->get_id());
$db->query($q);
$db->next_record();
if ($db->f("clientID")) {
  $TPL["new_client_contact_link"] = "<br><br><a href=\"".$TPL["url_alloc_client"]."clientID=".$db->f("clientID")."\">";
  $TPL["new_client_contact_link"].= "New Client Contact</a>";
  $TPL["task_clientID"] = $db->f("clientID");
}


$parentTaskIDs = get_parent_taskIDs($task->get_value("parentTaskID"));
if (is_array($parentTaskIDs)) {
  $parentTaskIDs = array_reverse($parentTaskIDs,1);

  foreach ($parentTaskIDs as $tName => $tID) {
    $TPL["hierarchy_links"] .= $br.$spaces."<a href=\"".$TPL["url_alloc_task"]."taskID=".$tID."\">".$tID." ".page::htmlentities($tName)."</a>";
    $spaces.="&nbsp;&nbsp;&nbsp;&nbsp;";
    $br = "<br>";
  }
}

$dupeID = $task->get_value("duplicateTaskID");
if ($dupeID) {
  $realtask = new task();
  $realtask->set_id($dupeID);
  $realtask->select();
  $TPL["taskDuplicateLink"] = $realtask->get_task_link(array("prefixTaskID"=>1,"return"=>"html"));
  $mesg = "This task is a duplicate of ".$TPL["taskDuplicateLink"];
  $TPL["message_help_no_esc"][] = $mesg;
  $TPL["editing_disabled"] = true;
}

$rows = $task->get_pending_tasks();
foreach ((array)$rows as $pendingTaskID) {
  $realtask = new task();
  $realtask->set_id($pendingTaskID);
  $realtask->select();
  unset($st1,$st2);
  if (substr($realtask->get_value("taskStatus"),0,6) == "closed") {
    $st1 = "<strike>";
    $st2 = "</strike>";
  } else {
    $wasopen = true;
  }
  $pendingTaskLinks[] = $st1.$realtask->get_task_link(array("prefixTaskID"=>1,"return"=>"html")).$st2;
}
$is = "was";
$wasopen and $is = "is";
$pendingTaskLinks and $TPL["message_help_no_esc"][] = "This task ".$is." pending the completion of:<br>".implode("<br>",$pendingTaskLinks);

$rows = $task->get_pending_tasks(true);
foreach ((array)$rows as $tID) {
  $realtask = new task();
  $realtask->set_id($tID);
  $realtask->select();
  unset($st1,$st2);
  if (substr($realtask->get_value("taskStatus"),0,6) == "closed") {
    $st1 = "<strike>";
    $st2 = "</strike>";
  } else {
    $wasopen = true;
  }
  $blockTaskLinks[] = $st1.$realtask->get_task_link(array("prefixTaskID"=>1,"return"=>"html")).$st2;
}
$is = "was";
$wasopen and $is = "is";
$blockTaskLinks and $TPL["message_help_no_esc"][] = "This task ".$is." blocking the start of:<br>".implode("<br>",$blockTaskLinks);


if (in_str("pending_",$task->get_value("taskStatus"))) {
  $rows = $task->get_reopen_reminders();
  foreach ($rows as $r) {
    $TPL["message_help_no_esc"][] = 'This task is set to
                                    <a href="'.$TPL["url_alloc_reminder"].'step=3&reminderID='.$r["rID"].'&returnToParent=task">
                                    automatically reopen at '.$r["reminderTime"].'</a>';
  }
}



if ($task->get_id()) {
 $TPL["task_taskType"] = $task->get_value("taskTypeID");
} else {
  $TPL["task_children_summary"] = "";
  $TPL["task_taskType"] = "Task";
}


if ($taskID) {
  $TPL["taskTypeImage"] = $task->get_task_image();
  $TPL["taskSelfLink"] = "<a href=\"".$task->get_url()."\">".$task->get_id()." ".$task->get_name(array("return"=>"html"))."</a>";
  $TPL["main_alloc_title"] = "Task " . $task->get_id() . ": " . $task->get_name()." - ".APPLICATION_NAME;
  $TPL["task_exists"] = true;

  $q = prepare("SELECT GROUP_CONCAT(pendingTaskID) as pendingTaskIDs FROM pendingTask WHERE taskID = %d",$task->get_id());
  $db->query($q);
  $row = $db->row();
  $TPL["task_pendingTaskIDs"] = $row["pendingTaskIDs"];

} else {
  $TPL["taskSelfLink"] = "New Task";
  $TPL["main_alloc_title"] = "New Task - ".APPLICATION_NAME;
}

if (!$task->get_id()) {
  $TPL["message_help"][] = "Enter a Task Name and click the Save button to create a new Task.";
  $TPL["task_dateTargetStart"] or $TPL["task_dateTargetStart"] = date("Y-m-d");
}

$TPL["task"] = $task;

// Printer friendly view
if ($_GET["media"] == "print") {
  if (has("client")) {
    $client = new client();
    $client->set_id($project->get_value("clientID"));
    $client->select();
    $client->set_values("client_");
  }
  if (has("project") && has("client")) {
    $project = $task->get_foreign_object("project");
    $clientContact = new clientContact();
    $clientContact->set_id($project->get_value("clientContactID"));
    $clientContact->select();
    $clientContact->set_values("clientContact_");
  }
  include_template("templates/taskPrinterM.tpl");
} else {
  include_template("templates/taskM.tpl");
}


?>
