<?php
include("alloc.inc");

global $SCRIPT_PATH, $SERVER_NAME;


if (!isset($step)) {
  $step = 1;
}

if ($parentType == "general" && $step == 2) {
  $step++;
  $parentID = "0";
}

switch ($step) {
case 1:
  // Reminder type (project,task,client,general)
  $parent_types = array("client"=>"Client", "project"=>"Project", "task"=>"Task", "general"=>"General");
  $TPL["parentTypeOptions"] = get_options_from_array($parent_types, "", true);
  include_template("templates/reminderSelectParentTypeM.tpl");
  break;

case 2:
  // Which project,task,client. (skip for general)

  // get personID
  $personID = $current_user->get_id();
  $permissions = explode(",", $current_user->get_value("perms"));
  $parent_names = array();

  $db = new db_alloc;
  if ($parentType == "client") {
    $query = "SELECT * FROM client WHERE clientStatus!='archived' ORDER BY clientName";
    $db->query($query);
    while ($db->next_record()) {
      $client = new client;
      $client->read_db_record($db);
      $parent_names[$client->get_id()] = $client->get_value('clientName');
    }
  } else if ($parentType == "project") {
    if (in_array("admin", $permissions)) {
      $query = "SELECT * FROM project order by projectName";
    } else {
      $query = "SELECT * FROM project LEFT JOIN projectPerson ON"." project.projectID=projectPerson.projectID".sprintf(" WHERE personID='%d' ORDER BY projectName", $personID);
    }
    $db->query($query);
    while ($db->next_record()) {
      $project = new project;
      $project->read_db_record($db);
      // if project is archived then dont bother putting it in the list
      if ($project->get_value('projectStatus') != "archived") {
        $parent_names[$project->get_id()] = $project->get_value('projectName');
      }
    }
  } else if ($parentType == "task") {
    if (in_array("admin", $permissions)) {
      $query = "SELECT * FROM task";
    } else {
      $query = sprintf("SELECT * FROM task WHERE personID='%d' ORDER BY taskName", $personID);
    }
    $db->query($query);
    while ($db->next_record()) {
      $task = new task;
      $task->read_db_record($db);
      if ($task->get_status("text", "standard") != "Completed") {
        $parent_names[$task->get_id()] = $task->get_value('taskName');
      }
    }
  }
  $TPL["parentType"] = $parentType;
  $TPL["parentNameOptions"] = get_options_from_array($parent_names, "", true);
  include_template("templates/reminderSelectParentM.tpl");
  break;

case 3:
  // reminder entry form
  $reminder = new reminder;
  if (isset($reminderID)) {
    $reminder->set_id($reminderID);
    $reminder->select();
    $parentType = $reminder->get_value('reminderType');
    $parentID = $reminder->get_value('reminderLinkID');
    $TPL["reminder_title"] = "Edit Reminder:";
    $TPL["reminder_buttons"] =
      sprintf("<input type=\"hidden\" name=\"reminder_id\" value=\"%d\">", $reminderID).
      "<input type=\"submit\" name=\"reminder_update\" value=\"Update\">&nbsp;&nbsp;&nbsp;"."<input type=\"submit\" name=\"reminder_delete\" value=\"Delete\">&nbsp;&nbsp;&nbsp;"."<input type=\"submit\" name=\"reminder_cancel\" value=\"Cancel\">";
  } else {
    $reminder->set_value('reminderType', $parentType);
    $reminder->set_value('reminderLinkID', $parentID);
    $TPL["reminder_title"] = "New Reminder:";
    $TPL["reminder_buttons"] = "<input type=\"submit\" name=\"reminder_save\" value=\"Save\">"."<input type=\"submit\" name=\"reminder_cancel\" value=\"Cancel\">";
  }

  // link to parent
  if ($parentType == "client") {
    $TPL["return_address"] = $TPL["url_alloc_client"]."&clientID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Client</a>";
  } else if ($parentType == "project") {
    $TPL["return_address"] = $TPL["url_alloc_project"]."&projectID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Project</a>";
  } else if ($parentType == "task") {
    $TPL["return_address"] = $TPL["url_alloc_task"]."&taskID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Task</a>";
  }
  // recipients
  $TPL["reminder_recipients"] = $reminder->get_recipient_options();
  // date/time
  $TPL["reminder_months"] = $reminder->get_month_options();
  $TPL["reminder_days"] = $reminder->get_day_options();
  $TPL["reminder_years"] = $reminder->get_year_options();
  $TPL["reminder_hours"] = $reminder->get_hour_options();
  $TPL["reminder_minutes"] = $reminder->get_minute_options();
  $TPL["reminder_meridians"] = $reminder->get_meridian_options();
  // recuring?
  if ($reminder->get_value('reminderRecuringInterval') != "No" && $reminder->get_value('reminderRecuringInterval') != "" && $reminder->get_value('reminderRecuringValue') > 0) {
    $TPL["reminder_recuring"] = "checked";
  }
  $TPL["reminder_recuring_value"] = $reminder->get_value('reminderRecuringValue');
  $TPL["reminder_recuring_intervals"] = $reminder->get_recuring_interval_options();
  // advanced notice?
  if ($reminder->get_value('reminderAdvNoticeInterval') != "No" && $reminder->get_value('reminderAdvNoticeInterval') != "" && $reminder->get_value('reminderAdvNoticeValue') > 0) {
    $TPL["reminder_advnotice"] = "checked";
  }
  $TPL["reminder_advnotice_value"] = $reminder->get_value('reminderAdvNoticeValue');
  $TPL["reminder_advnotice_intervals"] = $reminder->get_advnotice_interval_options();
  // subject
  if ($reminder->get_value('reminderSubject') != "") {
    $TPL["reminder_default_subject"] = $reminder->get_value('reminderSubject');
  } else {
    if ($parentType == "client") {
      $client = new client;
      $client->set_id($parentID);
      $client->select();
      $TPL["reminder_default_subject"] = sprintf("[Client: %s]", $client->get_value('clientName'));
      $TPL["reminder_default_content"] = "http://".$SERVER_NAME.$SCRIPT_PATH."client/client.php?clientID=".$parentID;

    } else if ($parentType == "project") {
      $project = new project;
      $project->set_id($parentID);
      $project->select();
      $TPL["reminder_default_subject"] = sprintf("[Project: %s]", $project->get_value('projectName'));
      $TPL["reminder_default_content"] = "http://".$SERVER_NAME.$SCRIPT_PATH."project/project.php?projectID=".$parentID;

    } else if ($parentType == "task") {
      $task = new task;
      $task->set_id($parentID);
      $task->select();
      $TPL["reminder_default_subject"] = sprintf("[Task: %s]", $task->get_value('taskName'));
      $TPL["reminder_default_content"] = "http://".$SERVER_NAME.$SCRIPT_PATH."project/task.php?taskID=".$parentID;

    } else if ($parentType == "general") {
      $TPL["reminder_default_subject"] = "[General Reminder]";
    }
  }
  $TPL["reminder_default_content"].= "\n".$reminder->get_value('reminderContent');
  $TPL["parentType"] = $parentType;
  $TPL["parentID"] = $parentID;

  // return to parent
  if (isset($returnToParent) && $returnToParent == "t") {
    $TPL["returnToParent"] = "t";
  } else {
    $TPL["returnToParent"] = "f";
  }

  include_template("templates/reminderAddM.tpl");
  break;

case 4:
  // save and return to list
  if (isset($reminder_save) || isset($reminder_update)) {

    // -- all -- option
    if ($reminder_recipient == -1) {
      $reminder = new reminder;
      $reminder->set_value('reminderType', $parentType);
      $reminder->set_value('reminderLinkID', $parentID);
      $recipients = $reminder->get_recipients();
      $recipient_keys = array_keys($recipients);
      array_shift($recipient_keys);
    } else {
      $recipient_keys = array($reminder_recipient);
    }

    // make 24 hour with 12am = 0 -> 11am = 11 -> 12pm = 12 -> 11pm = 23
    if ($reminder_hour == 12) {
      $reminder_hour = 0;
    }
    if ($reminder_meridian == "pm") {
      $reminder_hour += 12;
    }
    // process list of all or list of one
    for ($i = 0; $i < count($recipient_keys); $i++) {
      $reminder = new reminder;
      $reminder->set_value('reminderType', $parentType);
      $reminder->set_value('reminderLinkID', $parentID);
      $reminder->set_value('personID', $recipient_keys[$i]);
      $reminder->set_value('reminderModifiedUser', $current_user->get_id());
      $reminder->set_modified_time();

      $reminder->set_value('reminderTime', date("Y-m-d H:i:s", mktime($reminder_hour, $reminder_minute, 0, $reminder_month, $reminder_day, $reminder_year)));
      if (isset($reminder_update)) {
        $reminder->set_id($reminder_id);
      }
      if (!isset($reminder_recuring) || !isset($reminder_recuring_value)) {
        $reminder->set_value('reminderRecuringInterval', 'No');
        $reminder->set_value('reminderRecuringValue', '0');
      } else {
        if ($reminder_recuring_value == 0 && isset($reminder_recuring_interval) && $reminder_recuring_interval != 'No') {
          $reminder_recuring_value = 1;
        }
        $reminder->set_value('reminderRecuringInterval', $reminder_recuring_interval);
        $reminder->set_value('reminderRecuringValue', $reminder_recuring_value);
      }
      $reminder->set_value('reminderAdvNoticeSent', '0');
      if (!isset($reminder_advnotice) || !isset($reminder_advnotice_value)) {
        $reminder->set_value('reminderAdvNoticeInterval', 'No');
        $reminder->set_value('reminderAdvNoticeValue', '0');
      } else {
        $reminder->set_value('reminderAdvNoticeInterval', $reminder_advnotice_interval);
        $reminder->set_value('reminderAdvNoticeValue', $reminder_advnotice_value);
      }
      $reminder->set_value('reminderSubject', $reminder_subject);
      $reminder->set_value('reminderContent', $reminder_content);

      // dont bother saving if there is no subject and content
      // all other values such as time are already set
      if (isset($reminder_subject) && isset($reminder_content)) {
        $reminder->save();
      }
    }
  } else if (isset($reminder_delete) && isset($reminder_id)) {
    $reminder = new reminder;
    $reminder->set_id($reminder_id);
    $reminder->delete();
  }

  header("Location: ".$TPL["url_alloc_eventFilterList"]);
  if ($returnToParent == "t") {
    if ($parentType == "client") {
      header("Location: ".$TPL["url_alloc_client"]."&clientID=".$parentID);
    } else if ($parentType == "project") {
      header("Location: ".$TPL["url_alloc_project"]."&projectID=".$parentID);
    } else if ($parentType == "task") {
      header("Location: ".$TPL["url_alloc_task"]."&taskID=".$parentID);
    }
  }

  break;

default:
  die("Unrecognized state");
}

page_close();



?>
