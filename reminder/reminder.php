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

$TPL["main_alloc_title"] = "Add reminder - ".APPLICATION_NAME;

$reminderID = $_POST["reminderID"] or $reminderID = $_GET["reminderID"];
$step = $_POST["step"] or $step = $_GET["step"];
$parentType = $_POST["parentType"] or $parentType = $_GET["parentType"];
$parentID = $_POST["parentID"] or $parentID = $_GET["parentID"];
$returnToParent = $_POST["returnToParent"] or $returnToParent = $_GET["returnToParent"];
$TPL["returnToParent"] = $returnToParent;
$parentID = sprintf("%d",$parentID);

// Hacks to get reminders to work from the task calendar
$_GET["reminderTime"] and $TPL["reminderTime"] = $_GET["reminderTime"];
$_GET["personID"] and $TPL["personID"] = $_GET["personID"];

$step or $step = 1;

if ($parentType == "general" && $step == 2) {
  $step++;
  $parentID = "0";
}

switch ($step) {
case 1:
  // Reminder type (project,task,client,general)
  $parent_types = array("client"=>"Client", "project"=>"Project", "task"=>"Task", "general"=>"General");
  $TPL["parentTypeOptions"] = page::select_options($parent_types);
  include_template("templates/reminderSelectParentTypeM.tpl");
  break;

case 2:
  // Which project,task,client. (skip for general)

  // get personID
  $personID = $current_user->get_id();
  $parent_names = array();

  $db = new db_alloc();
  if ($parentType == "client") {
    $query = "SELECT * FROM client WHERE clientStatus!='Archived' ORDER BY clientName";
    $db->query($query);
    while ($db->next_record()) {
      $client = new client();
      $client->read_db_record($db);
      $parent_names[$client->get_id()] = $client->get_value('clientName');
    }
  } else if ($parentType == "project") {
    if ($current_user->have_role("admin")) {
      $query = "SELECT * FROM project WHERE projectStatus != 'Archived' ORDER BY projectName";
    } else {
      $query = prepare("SELECT * 
                          FROM project 
                     LEFT JOIN projectPerson ON project.projectID=projectPerson.projectID 
                         WHERE personID='%d' 
                           AND projectStatus != 'Archived'
                      ORDER BY projectName", $personID);
    }
    $db->query($query);
    while ($db->next_record()) {
      $project = new project();
      $project->read_db_record($db);
      $parent_names[$project->get_id()] = $project->get_value('projectName');
    }

  } else if ($parentType == "task") {
    if ($current_user->have_role("admin")) {
      $query = "SELECT * FROM task";
    } else {
      $query = prepare("SELECT * FROM task WHERE personID=%d ORDER BY taskName", $personID);
    }
    $db->query($query);
    while ($db->next_record()) {
      $task = new task();
      $task->read_db_record($db);
      if (substr($task->get_value("taskStatus"),0,6) != "closed") {
        $parent_names[$task->get_id()] = $task->get_value('taskName');
      }
    }
  }
  $TPL["parentType"] = $parentType;
  $TPL["parentNameOptions"] = page::select_options($parent_names);
  include_template("templates/reminderSelectParentM.tpl");
  break;

case 3:
  // reminder entry form
  $reminder = new reminder();
  if (isset($reminderID)) {
    $reminder->set_id($reminderID);
    $reminder->select();
    $parentType = $reminder->get_value('reminderType');
    $parentID = $reminder->get_value('reminderLinkID');
    $TPL["reminder_title"] = "Edit Reminder";
    $TPL["reminder_buttons"] = <<<EOD
<input type="hidden" name="reminder_id" value="{$reminderID}">
<button type="submit" name="reminder_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
<button type="submit" name="reminder_update" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
EOD;

  } else {
    $reminder->set_value('reminderType', $parentType);
    $reminder->set_value('reminderLinkID', $parentID);
    $TPL["reminder_title"] = "New Reminder";
    $TPL["reminder_buttons"] = <<<EOD2
<button type="submit" name="reminder_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
EOD2;
  }

  // link to parent
  if ($parentType == "client") {
    $TPL["return_address"] = $TPL["url_alloc_client"]."clientID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Client</a>";
  } else if ($parentType == "project") {
    $TPL["return_address"] = $TPL["url_alloc_project"]."projectID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Project</a>";
  } else if ($parentType == "task") {
    $TPL["return_address"] = $TPL["url_alloc_task"]."taskID=".$parentID;
    $TPL["reminder_goto_parent"] = "<a href=\"".$TPL["return_address"]."\">Goto Task</a>";
  }
  // recipients
  list($TPL["reminder_recipients"],$TPL["selected_recipients"]) = $reminder->get_recipient_options();
  $recipients_display = array();
  foreach ($TPL["selected_recipients"] as $recipient) {
    $recipients_display []= $TPL["reminder_recipients"][$recipient];
  }
  $TPL['recipients_display'] = implode($recipients_display, ", ");
  // date/time
  $_GET["reminderTime"] && $reminder->set_value("reminderTime",$_GET["reminderTime"]);
  $TPL["reminderTime"] = $reminder->get_value("reminderTime");
  $TPL["reminderHash"] = $reminder->get_value("reminderHash");
  $TPL["reminderAdvNoticeInterval"] = $reminder->get_value("reminderAdvNoticeInterval");
  $TPL["reminderRecuringInterval"] = $reminder->get_value("reminderRecuringInterval");
  $TPL["reminderID"] = $reminder->get_id();

  list($d,$t) = explode(" ",$reminder->get_value("reminderTime"));
  $TPL["reminder_date"] = $d or $TPL["reminder_date"] = date("Y-m-d");

  $TPL["reminder_hours"] = $reminder->get_hour_options();
  $TPL["reminder_minutes"] = $reminder->get_minute_options();
  $TPL["reminder_meridians"] = $reminder->get_meridian_options();
  $TPL["reminder_recuring_value"] = $reminder->get_value('reminderRecuringValue');
  $TPL["reminder_recuring_intervals"] = $reminder->get_recuring_interval_options();
  // advanced notice?
  $TPL["reminder_advnotice_value"] = $reminder->get_value('reminderAdvNoticeValue');
  $TPL["reminder_advnotice_intervals"] = $reminder->get_advnotice_interval_options();
  // subject
  if ($reminder->get_value('reminderSubject') != "") {
    $TPL["reminder_default_subject"] = $reminder->get_value('reminderSubject');
  } else {
    if ($parentType == "client") {
      $TPL["reminder_default_subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_reminderClient"), "client", $parentID);
      $TPL["reminder_default_content"] = config::get_config_item("allocURL")."client/client.php?clientID=".$parentID;

    } else if ($parentType == "project") {
      $TPL["reminder_default_subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_reminderProject"), "project", $parentID);
      $TPL["reminder_default_content"] = config::get_config_item("allocURL")."project/project.php?projectID=".$parentID;

    } else if ($parentType == "task") {
      $TPL["reminder_default_subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_reminderTask"), "task", $parentID);
      $TPL["reminder_default_content"] = config::get_config_item("allocURL")."task/task.php?taskID=".$parentID;

    } else if ($parentType == "general") {
      $TPL["reminder_default_subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_reminderOther"), "");
    }
  }
  $TPL["reminder_default_content"].= "\n".$reminder->get_value('reminderContent');
  $TPL["parentType"] = $parentType;
  $TPL["parentID"] = $parentID;
  $TPL["reminderActive"] = $reminder->get_value("reminderActive");
  if (!is_object($reminder) || !$reminder->get_id()) {
    $TPL["reminderActive"] = true;
  }

  if ($reminder->get_value("reminderHash")) {
    $db = new db_alloc();
    $r = $db->qr("SELECT tokenAction
                    FROM token 
               LEFT JOIN tokenAction ON token.tokenActionID = tokenAction.tokenActionID
                   WHERE token.tokenHash = '%s'",$reminder->get_value("reminderHash"));
    $TPL["tokenName"] = $r["tokenAction"];
  }
  include_template("templates/reminderM.tpl");
  break;

case 4:
  // save and return to list
  if ($_POST["reminder_save"] || $_POST["reminder_update"]) {

    $recipient_keys = $_POST["reminder_recipient"];
    // make 24 hour with 12am = 0 -> 11am = 11 -> 12pm = 12 -> 11pm = 23
    if ($_POST["reminder_hour"] == 12) {
      $_POST["reminder_hour"] = 0;
    }
    if ($_POST["reminder_meridian"] == "pm") {
      $_POST["reminder_hour"] += 12;
    }
    $reminder = new reminder();

    if (isset($_POST["reminder_update"])) {
      $reminder->set_id($_POST["reminder_id"]);
      $reminder->select();
      if ($reminder->get_value("reminderHash")) {
        $token = new token();
        $token->set_hash($reminder->get_value("reminderHash"),false);
        if ($token->get_value("tokenActionID") == 3) {
          $reminder->set_value("reminderTime","");
          $no = true;
        }
      }
    }

    $reminder->set_value('reminderType', $parentType);
    $reminder->set_value('reminderLinkID', $parentID);
    $reminder->set_value('reminderModifiedUser', $current_user->get_id());
    $reminder->set_modified_time();
    $no or $reminder->set_value('reminderTime',$_POST["reminder_date"]." ".$_POST["reminder_hour"].":".$_POST["reminder_minute"].":00");
    $reminder->set_value('reminderHash',$_POST["reminderHash"]);
      


    if (!$_POST["reminder_recuring_value"]) {
      $reminder->set_value('reminderRecuringInterval', 'No');
      $reminder->set_value('reminderRecuringValue', '0');
    } else {
      if ($_POST["reminder_recuring_value"] == 0 && $_POST["reminder_recuring_interval"] && $_POST["reminder_recuring_interval"] != 'No') {
        $_POST["reminder_recuring_value"] = 1;
      }
      $reminder->set_value('reminderRecuringInterval', $_POST["reminder_recuring_interval"]);
      $reminder->set_value('reminderRecuringValue', $_POST["reminder_recuring_value"]);
    }
    $reminder->set_value('reminderAdvNoticeSent', '0');
    if (!$_POST["reminder_advnotice_value"]) {
      $reminder->set_value('reminderAdvNoticeInterval', 'No');
      $reminder->set_value('reminderAdvNoticeValue', '0');
    } else {
      $reminder->set_value('reminderAdvNoticeInterval', $_POST["reminder_advnotice_interval"]);
      $reminder->set_value('reminderAdvNoticeValue', $_POST["reminder_advnotice_value"]);
    }
    $reminder->set_value('reminderSubject', $_POST["reminder_subject"]);
    $reminder->set_value('reminderContent', rtrim($_POST["reminder_content"]));
    $reminder->set_value('reminderActive', sprintf("%d",$_POST["reminderActive"]));
    $reminder->save();
    $reminder->update_recipients($recipient_keys);
    $returnToParent = "reminder";
    $reminderID = $reminder->get_id();
    $TPL["message_good"][] = "Reminder saved.";

  } else if ($_POST["reminder_delete"] && $_POST["reminder_id"]) {
    $reminder = new reminder();
    $reminder->set_id($_POST["reminder_id"]);
    $reminder->delete();
  }

  $headers = array("client"   => $TPL["url_alloc_client"]."clientID=".$parentID."&sbs_link=reminders"
                  ,"project"  => $TPL["url_alloc_project"]."projectID=".$parentID."&sbs_link=reminders"
                  ,"task"     => $TPL["url_alloc_task"]."taskID=".$parentID."&sbs_link=reminders"
                  ,"home"     => $TPL["url_alloc_home"]
                  ,"calendar" => $TPL["url_alloc_taskCalendar"]."personID=".$_POST["personID"]
                  ,"list"     => $TPL["url_alloc_reminderList"]
                  ,"reminder" => $TPL["url_alloc_reminder"]."reminderID=".$reminderID."&step=3"
                  ,""         => $TPL["url_alloc_reminderList"]
                  );

  alloc_redirect($headers[$returnToParent]);

  break;

default:
  alloc_error("Unrecognized state");
}


?>
