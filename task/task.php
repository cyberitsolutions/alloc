<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

  function show_reminders($template) {
    global $TPL, $taskID, $reminderID;

    // show all reminders for this project
    $reminder = new reminder;
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM reminder WHERE reminderType='task' AND reminderLinkID=%d", $taskID);
    $db->query($query);
    while ($db->next_record()) {
      $reminder->read_db_record($db);
      $reminder->set_tpl_values(DST_HTML_ATTRIBUTE, "reminder_");
      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }
      $person = new person;
      $person->set_id($reminder->get_value('personID'));
      $person->select();
      $TPL["reminder_reminderRecipient"] = $person->get_username(1);
      $TPL["returnToParent"] = "task";

      include_template($template);
    }
  }

  function show_task_children($template) {
    global $TPL, $task;
    if ($task->get_value("taskTypeID") == TT_PHASE) {
      include_template($template);
    }
  }

  function get_parent_taskIDs($taskID) {
    $q = sprintf("SELECT taskID,taskName,parentTaskID FROM task WHERE taskID = %d",$taskID);
    $db = new db_alloc;
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

  function show_taskComments() {
    global $taskID, $TPL;
    $options["showEditButtons"] = true;
    $TPL["commentsR"] = util_get_comments("task",$taskID,$options);
    if ($TPL["commentsR"] && !$_GET["comment_edit"]) {
      $TPL["class_new_task_comment"] = "hidden";
    }
    include_template("templates/taskCommentM.tpl");
  }

  function show_taskCommentsPrinter() {
    global $taskID, $TPL;
    $TPL["commentsR"] = util_get_comments("task",$taskID,$options);
    include_template("templates/taskPrinterCommentsM.tpl");
  }


global $timeSheetID;

if ($_GET["timeSheetID"]) {
  $TPL["timeSheet_save"] = "<input type=\"submit\" name=\"timeSheet_save\" value=\"Save and Return to Time Sheet\">";
  $TPL["timeSheetID"] = $_GET["timeSheetID"];
}

$db = new db_alloc;
$task = new task;

// If taskID

$taskID = $_POST["taskID"] or $taskID = $_GET["taskID"];

if (isset($taskID)) {
  // Displaying a record
  $task->set_id($taskID);
  $task->select();
  $orig_personID = $task->get_value("personID");
  $orig_duplicateTaskID = $task->get_value("duplicateTaskID");
  $orig_dateActualCompletion = $task->get_value("dateActualCompletion");
  $TPL["taskDuplicateOptions"] = task::get_task_duplicate_options("not_completed",$taskID);

// Creating a new record
} else {
  $_POST["dateCreated"] = date("Y-m-d H:i:s");
  $task->read_globals();
  $taskID = $task->get_id();
  if ($task->get_value("projectID")) {
    $project = $task->get_foreign_object("project");
  }
}

// if someone uploads an attachment
if ($_POST["save_attachment"]) {
  move_attachment("task",$taskID);
  header("Location: ".$TPL["url_alloc_task"]."taskID=".$taskID);
} 
  

// If saving a record
if ($_POST["save"] || $_POST["save_and_back"] || $_POST["save_and_new"] || $_POST["save_and_summary"] || $_POST["timeSheet_save"]) {

  $task->read_globals();
  $task_is_new = !$task->get_id();

  // Marked as dupe?
  $dupeID = $_POST["duplicateTaskID"];
  if ($dupeID && $dupeID != $orig_duplicateTaskID) {

    $othertask = new task;
    $othertask->set_id($dupeID);
    $othertask->select();
    if ($othertask->get_value("duplicateTaskID")) {
      $msg_error = "Task ".$dupeID." ".$othertask->get_task_name()." is a duplicate. You may not set a task to be a duplicate of a duplicate.";
      //abort the page
      $url = $TPL["url_alloc_task"]."taskID=".$task->get_id();
      page_close();
      header("Location: ".$url."&message=".urlencode($msg_error));
      exit();
    }

    $task->set_value("duplicateTaskID", $dupeID);
    // Close off the task
    if (!$task->get_value("dateActualCompletion")) {
      $task->set_value("dateActualCompletion", date("Y-m-d"));
    }
    $task->email_task_duplicate();
  }

  // If dateActualCompletion and there's no dateActualStart then default today
  if ($task->get_value("dateActualCompletion") && $task->get_value("dateActualStart") == "") {
    $task->set_value("dateActualStart", date("Y-m-d"));
  }

  // mark all children as complete
  if (!$orig_dateActualCompletion && $task->get_value("dateActualCompletion")) {
    if ($task->get_value("closerID") == "" && !$task->get_value("dateClosed")) {
      $task->set_value("closerID",$current_user->get_id());
      $task->set_value("dateClosed",date("Y-m-d H:i:s"));
    }
    if (!$orig_dateActualCompletion) {
      $msg[] = $task->email_task_closed();
    }
    $arr = $task->close_off_children_recursive();
    if (is_array($arr)) {
      $msg = array_merge($msg,$arr);
    }

  } else if ($orig_dateActualCompletion && !$task->get_value("dateActualCompletion")) {
    $task->set_value("closerID",0);
    $task->set_value("dateClosed","");
  }

  if (!$task_is_new) {
    if (sprintf("%d",$task->get_value("personID")) != sprintf("%d",$orig_personID)) {
      $msg[] = $task->email_task_reassigned($orig_personID);
      $task->set_value("dateAssigned",date("Y-m-d H:i:s"));
    }
  } else if ($task->get_value("personID")) {
    $task->set_value("dateAssigned",date("Y-m-d H:i:s"));
  }

  $success = $task->save();

  if ($task_is_new && $task->get_value("taskTypeID") == TT_FAULT) {     // Task is a "Fault" type task.
    $task->new_fault_task();
  }
  if ($task_is_new && $task->get_value("taskTypeID") == TT_MESSAGE) {   // Task is a "Message" type task.
    $task->new_message_task();
  }

  // Add entries to taskCCList
  $q = sprintf("DELETE FROM taskCCList WHERE taskID = %d",$task->get_id());
  $db->query($q);
  
  if (is_array($_POST["taskCCList"])) {
    foreach ($_POST["taskCCList"] as $encoded_name_and_email) {
      $name_and_email = unserialize(base64_decode(urldecode($encoded_name_and_email)));
      $CCname = db_esc($name_and_email["name"]);
      preg_match("/[A-Za-z0-9]+/",$CCname) or $CCname = ""; // sometimes name were being saved as a single space
      $q = sprintf("INSERT INTO taskCCList (fullName,emailAddress,taskID) VALUES ('%s','%s',%d)",$CCname,db_esc($name_and_email["email"]),$task->get_id());
      $db->query($q);
    }
  }

  if ($task_is_new && $current_user->get_id() != $task->get_value("personID")) {
    $successful_recipients = $task->send_emails(array("assignee"),"task_created");
    $successful_recipients and $msg[] = "Email sent to ".$successful_recipients;
  }
 
 
  count($msg) and $msg = "&message_good=".urlencode(implode("<br/>",$msg));

  if ($success) {
  
    if ($_POST["save"] && $_POST["view"] == "brief") {
      #$url = $TPL["url_alloc_taskList"];
      $url = $TPL["url_alloc_task"]."taskID=".$task->get_id();
    } else if ($_POST["save"]) {
      $url = $TPL["url_alloc_task"]."taskID=".$task->get_id();
    } else if ($_POST["save_and_back"]) {
      $url = $TPL["url_alloc_project"]."projectID=".$task->get_value("projectID");
    } else if ($_POST["save_and_summary"]) {
      $url = $TPL["url_alloc_taskList"];
    } else if ($_POST["save_and_new"]) {
      $url = $TPL["url_alloc_task"]."projectID=".$task->get_value("projectID")."&parentTaskID=".$task->get_value("parentTaskID");
    } else if ($_POST["timeSheet_save"]) {
      $url = $TPL["url_alloc_timeSheet"]."timeSheetID=".$_POST["timeSheetID"];
    } else {
      die("Unexpected save button");
    }
    page_close();
    header("Location: ".$url.$msg);
    exit();
  }

// If deleting a record
} else if ($_POST["delete"]) {
  $task->read_globals();
  $task->delete();
  header("location: ".$TPL["url_alloc_taskList"]);
}



// Start stuff here
$task->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");


$person = new person;
$person->set_id($task->get_value("creatorID"));
$person->select();
$TPL["task_createdBy"] = $person->get_username(1);
$TPL["task_createdBy_personID"] = $person->get_id();

if ($task->get_value("closerID") && $task->get_value("dateClosed")) {
  $TPL["task_closed_info"] = "<tr><td>Task Closed By</td><td><b>".person::get_fullname($task->get_value("closerID"))."</b> ".$task->get_value("dateClosed")."</td></tr>";
}

$person = new person;
$person->set_id($task->get_value("personID"));
$person->select();
$TPL["person_username"] = $person->get_username(1);
$TPL["person_username_personID"] = $person->get_id();

$manager = new person;
$manager->set_id($task->get_value("managerID"));
$manager->select();
$TPL["manager_username"] = $manager->get_username(1);
$TPL["manager_username_personID"] = $manager->get_id();





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
$time_billed_label = "Actual Billed ".seconds_to_display_format($time_billed);
if ($time_billed > 0) {
  $TPL["time_billed_link"] = "<a href=\"".$TPL["url_alloc_timeSheetList"]."taskID=".$task->get_id()."&dontSave=true&applyFilter=true\">".$time_billed_label."</a>";
} 

$TPL["task_timeEstimate"] or $TPL["task_timeEstimate"] = "";
$TPL["percentComplete"] = $task->get_percentComplete();


// Generate navigation links
$project = $task->get_foreign_object("project");
$project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");
if ($project->get_id()) {
  $ops["taskID"] = $task->get_id();
  $ops["showProject"] = true;
  $TPL["navigation_links"] = $project->get_navigation_links($ops);
}

$parent_task = $task->get_foreign_object("task", "parentTaskID");
$parent_task->set_tpl_values(DST_HTML_ATTRIBUTE, "parentTask_");

$taskType = $task->get_foreign_object("taskType", "taskTypeID");
$taskType->set_tpl_values(DST_HTML_ATTRIBUTE, "taskType_");

$q = sprintf("SELECT clientID FROM project LEFT JOIN task ON task.projectID = project.projectID WHERE taskID = %d",$task->get_id());
$db->query($q);
$db->next_record();
if ($db->f("clientID")) {
  $TPL["new_client_contact_link"] = "<br/><br/><a href=\"".$TPL["url_alloc_client"]."clientID=".$db->f("clientID")."\">";
  $TPL["new_client_contact_link"].= "New Client Contact</a>";
  $TPL["task_clientID"] = $db->f("clientID");
}


$parentTaskIDs = get_parent_taskIDs($task->get_value("parentTaskID"));
if (is_array($parentTaskIDs)) {
  $parentTaskIDs = array_reverse($parentTaskIDs,1);

  foreach ($parentTaskIDs as $tName => $tID) {
    $spaces.="&nbsp;&nbsp;&nbsp;&nbsp;";
    $TPL["hierarchy_links"] .= "<br/>".$spaces."<a href=\"".$TPL["url_alloc_task"]."taskID=".$tID."\">".$tID." ".$tName."</a>";
  }
}
$TPL["hierarchy_links"].= "<br/><br/><b>".$TPL["task_taskID"]." ".$TPL["task_taskName"]."</b>";

$dupeID = $task->get_value("duplicateTaskID");
if ($dupeID) {
  $realtask = new task;
  $realtask->set_id($dupeID);
  $realtask->select();
  $TPL["taskDuplicateLink"] = $realtask->get_task_link(array("showTaskID"=>1));
  $mesg = "This task is a duplicate of ".$TPL["taskDuplicateLink"];
  $TPL["message_help"][] = $mesg;
  $TPL["comments_disabled"] = true;
  $TPL["editing_disabled"] = true;
  #$TPL["disabled_reason"] = "Posting comments for this task is disabled because it has been marked as a duplicate.";
}






if ($_GET["commentID"] && $_GET["comment_edit"]) {
  $comment = new comment();
  $comment->set_id($_GET["commentID"]);
  $comment->select();
  $TPL["comment"] = $comment->get_value('comment');
  $TPL["commentEmailRecipients"] = $comment->get_value('commentEmailRecipients');
  $TPL["comment_buttons"] =
    sprintf("<input type=\"hidden\" name=\"comment_id\" value=\"%d\">", $_GET["commentID"])
           ."<input type=\"submit\" name=\"comment_update\" value=\"Save Comment\">";
} else {
  $TPL["comment_buttons"] = "<input type=\"submit\" name=\"comment_save\" value=\"Save Comment\">";
  if ($task->get_id()) {

    if ($current_user->get_id() != $task->get_value("creatorID")) {
      $TPL["email_comment_creator_checked"] = " checked";
    } 
    if ($current_user->get_id() != $task->get_value("personID")) {
      $TPL["email_comment_assignee_checked"] = " checked";
    } 
    if ($task->get_value("managerID") && $current_user->get_id() != $task->get_value("managerID")) {
      $TPL["email_comment_manager_checked"] = " checked";
    } 
    // If there are interested parties then, default the checkbox to on
    $q = sprintf("SELECT * FROM taskCCList WHERE taskID = %d",$task->get_id());
    $db = new db_alloc();
    $db->query($q);
    $db->num_rows() and $TPL["email_comment_CCList_checked"] = " checked";
  }
}


if ($task->get_id()) {
  $options["parentTaskID"] = $task->get_id();
  $options["taskView"] = "byProject";
  $options["projectIDs"][] = $task->get_value("projectID");
  $options["showDates"] = true;
  #$options["showCreator"] = true;
  $options["showAssigned"] = true;
  $options["showPercent"] = true;
  $options["showHeader"] = true;
  $options["showTimes"] = true;

  $_GET["media"] == "print" and $options["showDescription"] = true;
  $_GET["media"] == "print" and $options["showComments"] = true;
  $TPL["task_children_summary"] = task::get_task_list($options);

  $taskType = $task->get_foreign_object("taskType");
  $TPL["task_taskType"] = $taskType->get_value("taskTypeName");
} else {
  $TPL["task_children_summary"] = "";
  $TPL["task_taskType"] = "Task";
}


if ($taskID) {
  $TPL["taskSelfLink"] = "<a href=\"".$task->get_url()."\">".$task->get_id()." ".$task->get_task_name()."</a>";
  $TPL["main_alloc_title"] = "Task " . $task->get_id() . ": " . $task->get_task_name()." - ".APPLICATION_NAME;
} else {
  $TPL["taskSelfLink"] = "New Task";
  $TPL["main_alloc_title"] = "New Task - ".APPLICATION_NAME;
}

// Printer friendly view
if ($_GET["media"] == "print") {

  $client = new client;
  $client->set_id($project->get_value("clientID"));
  $client->select();
  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

  $clientContact = new clientContact;
  $clientContact->set_id($client->get_value("clientPrimaryContactID"));
  $clientContact->select();
  $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "clientContact_");

  // Need to html-ise taskName and description
  $TPL["task_taskName"] = htmlentities($task->get_value("taskName"));
  $TPL["task_taskDescription"] = text_to_html($task->get_value("taskDescription"));

  include_template("templates/taskPrinterM.tpl");

// Detailed editable view
} else if ($_GET["view"] == "detail" || !$task->get_id()) {
  $TPL["task_taskName"] = text_to_html($task->get_value("taskName"));

  include_template("templates/taskDetailM.tpl");

// Default read-only view
} else {

  // Need to html-ise taskName and description
  $TPL["task_taskName"] = text_to_html($task->get_value("taskName"));
  $TPL["task_taskDescription"] = text_to_html($task->get_value("taskDescription"));
  $TPL["taskHash"] = $task->make_token_add_comment_from_email();

  include_template("templates/taskM.tpl");
}




page_close();



?>
