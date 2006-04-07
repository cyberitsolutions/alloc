<?php
include("alloc.inc");

global $timeSheetID;

if ($timeSheetID) {
  $TPL["timeSheet_save"] = "<input type=\"submit\" name=\"timeSheet_save\" value=\"Save and Return to Time Sheet\">";
  $TPL["timeSheetID"] = $timeSheetID;
}

$db = new db_alloc;
$task = new task;

// If taskID
if (isset($taskID)) {
  // Displaying a record
  $task->set_id($taskID);
  $task->select();
  $orig_personID = $task->get_value("personID");
  $orig_percentComplete = $task->get_value("percentComplete");

// Creating a new record
} else {
  $dateCreated = date("Y-m-d H:i:s");
  $task->read_globals();
  $taskID = $task->get_id();
  if ($task->get_value("projectID")) {
    $project = $task->get_foreign_object("project");
  }
}


// If saving a record
if (isset($save) || isset($save_and_back) || isset($save_and_new) || isset($save_and_summary) || isset($timeSheet_save)) {

  $task->read_globals();
  $task_is_new = !$task->get_id();

  $task->get_value("percentComplete") || $task->set_value("percentComplete", "0"); 

  // If there's a date in dateActualCompletion then push percentComplete up to 100%
  if ($task->get_value("dateActualCompletion") != "") {
    $task->set_value("percentComplete", "100");
  }
  
  // If percentComplete is at 100% and there's no dateActualCompletion then default today 
  if ($task->get_value("percentComplete") == "100" && $task->get_value("dateActualCompletion") == "") {
    $task->set_value("dateActualCompletion", date("Y-m-d"));
  }
  
  // If percentComplete is at 100% and there's no dateActualStart then default today
  if ($task->get_value("percentComplete") == "100" && $task->get_value("dateActualStart") == "") {
    $task->set_value("dateActualStart", date("Y-m-d"));
  }

  // mark all children as complete
  if ($task->get_value("percentComplete") == "100") {
    $task->close_off_children_recursive();
  }
  
  if ($task_is_new || $task->get_value("personID") != $orig_personID) {
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
  global $taskCCList;
  $q = sprintf("DELETE FROM taskCCList WHERE taskID = %d",$task->get_id());
  $db->query($q);
  
  if (is_array($taskCCList)) {
    foreach ($taskCCList as $encoded_name_and_email) {
      $name_and_email = unserialize(base64_decode(urldecode($encoded_name_and_email)));
      $q = sprintf("INSERT INTO taskCCList (fullName,emailAddress,taskID) VALUES ('%s','%s',%d)",$name_and_email["name"],$name_and_email["email"],$task->get_id());
      $db->query($q);
    }
  }


  global $taskEmail;
  if ($task->get_value("percentComplete") == "100" && $orig_percentComplete != "100") {
    $successful_recipients = $task->send_emails(array("creator"),$task,"Task Closed");
    $successful_recipients and $msg[] = "Emailed ".$successful_recipients;

  } else if ($task_is_new) {
    $successful_recipients = $task->send_emails(array("assignee"),$task,"Task Created");
    $successful_recipients and $msg[] = "Emailed ".$successful_recipients;
  }
  
  count($msg) and $msg = "&msg=".urlencode(implode("<br/>",$msg));

  if ($success) {
    if (isset($save)) {
      $url = $TPL["url_alloc_task"]."&taskID=".$task->get_id();
    } else if (isset($save_and_back)) {
      $url = $TPL["url_alloc_project"]."&projectID=".$task->get_value("projectID");
    } else if (isset($save_and_summary)) {
      $url = $TPL["url_alloc_taskSummary"];
    } else if (isset($save_and_new)) {
      $url = $TPL["url_alloc_task"]."&projectID=".$task->get_value("projectID")."&parentTaskID=".$task->get_value("parentTaskID");
    } else if (isset($timeSheet_save)) {
      $url = $TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheetID;
    } else {
      die("Unexpected save button");
    }
    page_close();
    header("Location: ".$url.$msg);
    exit();
  }

// If deleting a record
} else if (isset($delete)) {
  $task->read_globals();
  $projectID or $projectID = $task->get_value("projectID");
  $task->delete();
  header("location: ".$TPL["url_alloc_project"]."&projectID=$projectID");
}



// Start stuff here
$task->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");


$person = new person;
$person->set_id($task->get_value("creatorID"));
$person->select();
$TPL["task_createdBy"] = $person->get_username(1);
$TPL["task_createdBy_personID"] = $person->get_id();


$person = new person;
$person->set_id($task->get_value("personID"));
$person->select();
$TPL["person_username"] = $person->get_username(1);
$TPL["person_username_personID"] = $person->get_id();

$TPL["message_good"] = urldecode($_GET["msg"]);



// If we've been sent here by a "New Message" or "New Fault" option in the Quick List dropdown
if (!$taskID && $tasktype) {
  $task->set_value("taskTypeID", $tasktype);
}

// Set options for the dropdown boxen
$task->set_option_tpl_values();

$TPL["task_timeActual"] = $task->get_time_billed();
$TPL["task_timeEstimate"] or $TPL["task_timeEstimate"] = "";


// Generate navigation links
$project = $task->get_foreign_object("project");
$project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");
if ($project->get_id()) {
  $TPL["navigation_links"] = $project->get_navigation_links();
}

$parent_task = $task->get_foreign_object("task", "parentTaskID");
$parent_task->set_tpl_values(DST_HTML_ATTRIBUTE, "parentTask_");

$taskType = $task->get_foreign_object("taskType", "taskTypeID");
$taskType->set_tpl_values(DST_HTML_ATTRIBUTE, "taskType_");

$q = sprintf("SELECT clientID FROM project LEFT JOIN task ON task.projectID = project.projectID WHERE taskID = %d",$task->get_id());
$db->query($q);
$db->next_record();
if ($db->f("clientID")) {
  $TPL["new_client_contact_link"] = "<a href=\"".$TPL["url_alloc_client"]."&clientID=".$db->f("clientID")."\">";
  $TPL["new_client_contact_link"].= "New Client Contact</a>";
}

function get_parent_taskIDs($taskID) {
  $q = sprintf("SELECT taskID,taskName,parentTaskID FROM task WHERE taskID = %d",$taskID);
  $db = new db_alloc;
  $db->query($q);
  
  while($db->next_record()) {
    $rtn[$db->f("taskName")] = stripslashes($db->f("taskID")); 
    $rtn = array_merge($rtn, get_parent_taskIDs($db->f("parentTaskID")));
  }
  return $rtn;
}

$parentTaskIDs = get_parent_taskIDs($task->get_value("parentTaskID"));
if (is_array($parentTaskIDs)) {
  $parentTaskIDs = array_reverse($parentTaskIDs,1);

  foreach ($parentTaskIDs as $tName => $tID) {
    $spaces.="&nbsp;&nbsp;&nbsp;&nbsp;";
    $TPL["hierarchy_links"] .= "<br/>".$spaces."<a href=\"".$TPL["url_alloc_task"]."&taskID=".$tID."\">".$tName."</a>";
    #$br = "<br/>";
  }
}

$TPL["hierarchy_links"].= "</br><br/><b>".$TPL["task_taskName"]."</b>";






global $taskComment_edit;
if (isset($commentID) && $taskComment_edit) {
  $comment = new comment();
  $comment->set_id($commentID);
  $comment->select();
  $TPL["task_taskComment"] = $comment->get_value('comment');
  $TPL["task_taskComment_buttons"] =
    sprintf("<input type=\"hidden\" name=\"taskComment_id\" value=\"%d\">", $commentID)
           ."<input type=\"submit\" name=\"taskComment_update\" value=\"Save Comment\">";
} else {
  $TPL["task_taskComment_buttons"] = "<input type=\"submit\" name=\"taskComment_save\" value=\"Save Comment\">";
}


if ($task->get_id()) {
  #$TPL["task_children_summary"] = $task->get_children_summary("", true);
  $taskType = $task->get_foreign_object("taskType");
  $TPL["task_taskType"] = $taskType->get_value("taskTypeName");
} else {
  $TPL["task_children_summary"] = "";
  $TPL["task_taskType"] = "Task";
}





// Detailed editable view
if ($view == "detail" || !$task->get_id()) {
  include_template("templates/taskDetailM.tpl");

// Printer friendly view
} else if ($view == "printer") {

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
  $TPL["task_taskDescription"] = nl2br(htmlentities($task->get_value("taskDescription")));

  include_template("templates/taskPrinterM.tpl");

// Default read-only view
} else {

  // Need to html-ise taskName and description
  $TPL["task_taskName"] = htmlentities($task->get_value("taskName"));
  $TPL["task_taskDescription"] = nl2br(htmlentities($task->get_value("taskDescription")));

  include_template("templates/taskM.tpl");
}




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
    $TPL["returnToParent"] = "t";

    include_template($template);
  }
}

function show_task_children($template) {
  global $TPL, $task;
  if ($TPL["task_children_summary"] || $task->get_value("taskTypeID") == 2) {
    include_template($template);
  }
}


page_close();



?>
