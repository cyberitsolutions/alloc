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

// -1 used to be ALL, but was made redundant with the multiselect
define("REMINDER_METAPERSON_TASK_ASSIGNEE", 2);
define("REMINDER_METAPERSON_TASK_MANAGER", 3);

class reminder extends db_entity {
  public $data_table = "reminder";
  public $display_field_name = "reminderSubject";
  public $key_field = "reminderID";
  public $data_fields = array("reminderType"
                             ,"reminderLinkID"
                             ,"reminderTime"
                             ,"reminderHash"
                             ,"reminderRecuringInterval"
                             ,"reminderRecuringValue"
                             ,"reminderAdvNoticeSent"
                             ,"reminderAdvNoticeInterval"
                             ,"reminderAdvNoticeValue"
                             ,"reminderSubject"
                             ,"reminderContent"
                             ,"reminderCreatedTime"
                             ,"reminderCreatedUser"
                             ,"reminderModifiedTime"
                             ,"reminderModifiedUser"
                             ,"reminderActive" => array("empty_to_null"=>true)
                             );

  // set the modified time to now
  function set_modified_time() {
    $this->set_value("reminderModifiedTime", date("Y-m-d H:i:s"));
  }

  function delete() {
    $q = prepare("DELETE FROM reminderRecipient WHERE reminderID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    return parent::delete();
  }

  function get_recipients() {
    $db = new db_alloc();
    $type = $this->get_value('reminderType');
    if ($type == "project") {
      $query = prepare("SELECT * 
                          FROM projectPerson 
                     LEFT JOIN person ON projectPerson.personID=person.personID 
                         WHERE projectPerson.projectID = %d 
                      ORDER BY person.username",$this->get_value('reminderLinkID'));

    } else if ($type == "task") {
      // Modified query option: to send to all people on the project that this task is from.
      $recipients = array("-3" => "Task Manager"
                         ,"-2" => "Task Assignee");

      $db->query("SELECT projectID FROM task WHERE taskID = %d",$this->get_value('reminderLinkID'));
      $db->next_record();

      if ($db->f('projectID')) {
        $query = prepare("SELECT * 
                            FROM projectPerson 
                       LEFT JOIN person ON projectPerson.personID=person.personID 
                           WHERE projectPerson.projectID = %d 
                        ORDER BY person.username",$db->f('projectID'));

      } else {
        $query = "SELECT * FROM person WHERE personActive = 1 ORDER BY username";
      }

    } else {
      $query = "SELECT * FROM person WHERE personActive = 1 ORDER BY username";
    }
    $db->query($query);
    while ($db->next_record()) {
      $person = new person();
      $person->read_db_record($db);
      $recipients[$person->get_id()] = $person->get_name();
    }

    return $recipients;
  }

  function get_recipient_options() {
    $current_user = &singleton("current_user");

    $recipients = $this->get_recipients();
    $type = $this->get_value('reminderType');

    $selected = array();
    $db = new db_alloc();
    $query = "SELECT * from reminderRecipient WHERE reminderID = %d";
    $db->query($query, $this->get_id());
    while ($db->next_record()) {
      if ($db->f('metaPersonID'))
        $selected[] = $db->f('metaPersonID');
      else
        $selected[] = $db->f('personID');
    }

    if(!$selected && $_GET["personID"]) {
      $selected[] = $_GET["personID"];
    }
    if (!$this->get_id()) {
      $selected[] = $current_user->get_id();
    }
    return array($recipients, $selected);
  }

  function get_hour_options() {
    $hours = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7", "8"=>"8", "9"=>"9", "10"=>"10", "11"=>"11", "12"=>"12");
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $hour = date("h", $date);
    } else {
      $hour = date("h", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($hours, $hour);
  }

  function get_minute_options() {
    $minutes = array("0"=>"00", "10"=>"10", "20"=>"20", "30"=>"30", "40"=>"40", "50"=>"50");
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $minute = date("i", $date);
    } else {
      $minute = date("i", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($minutes, $minute);
  }

  function get_meridian_options() {
    $meridians = array("am"=>"AM", "pm"=>"PM");
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $meridian = date("a", $date);
    } else {
      $meridian = date("a", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($meridians, $meridian);
  }

  function get_recuring_interval_options() {
    $recuring_interval_options = array("Hour"=>"Hour(s)", "Day"=>"Day(s)", "Week"=>"Week(s)", "Month"=>"Month(s)", "Year"=>"Year(s)");
    $recuring_interval = $this->get_value('reminderRecuringInterval');
    if ($recuring_interval == "") {
      $recuring_interval = "Week";
    }
    return page::select_options($recuring_interval_options, $recuring_interval);
  }

  function get_advnotice_interval_options() {
    $advnotice_interval_options = array("Minute"=>"Minute(s)", "Hour"=>"Hour(s)", "Day"=>"Day(s)", "Week"=>"Week(s)", "Month"=>"Month(s)", "Year"=>"Year(s)");
    $advnotice_interval = $this->get_value('reminderAdvNoticeInterval');
    if ($advnotice_interval == "") {
      $advnotice_interval = "Hour";
    }
    return page::select_options($advnotice_interval_options, $advnotice_interval);
  }

  function is_alive() {
    $type = $this->get_value('reminderType');
    if ($type == "project") {
      $project = new project();
      $project->set_id($this->get_value('reminderLinkID'));
      if ($project->select() == false || $project->get_value('projectStatus') == "Archived") {
        return false;
      }
    } else if ($type == "task") {
      $task = new task();
      $task->set_id($this->get_value('reminderLinkID'));
      if ($task->select() == false || substr($task->get_value("taskStatus"),0,6) == 'closed') {
        return false;
      }
    } else if ($type == "client") {
      $client = new client();
      $client->set_id($this->get_value('reminderLinkID'));
      if ($client->select() == false || $client->get_value('clientStatus') == "Archived") {
        return false;
      }
    }
    return true;
  }

  function deactivate() {
    $this->set_value("reminderActive",0);
    return $this->save();
  }

  // mail out reminder and update to next date if repeating or remove if onceoff
  // checks to make sure that it is the right time to send reminder should be 
  // dome before calling this function
  function mail_reminder() {

    // check for a reminder.reminderHash that links off to a token.tokenHash
    // this lets us trigger reminders on complex actions, for example create
    // a reminder that sends when a task status changes from pending to open

    // Note this->reminderTime is going to always be null for the token that
    // link to task->moved_from_pending_to_open().
    // Whereas the task->reopen_pending_task() will have a reminderTime set.

    $ok = true;
    if ($this->get_value("reminderHash")) {
      $token = new token();
      if ($token->set_hash($this->get_value("reminderHash"))) {
        list($entity,$method) = $token->execute();
        if (is_object($entity) && $entity->get_id()) {
          if (!$entity->$method()) {
            $token->decrement_tokenUsed(); // next time, gadget
            $ok = false;
          }
        }
      }
    }

    if ($ok) {
      $recipients = $this->get_all_recipients();
      foreach ((array)$recipients as $person) {
        if ($person['emailAddress']) {
          $email = sprintf("%s %s <%s>", $person['firstName'], $person['surname'], $person['emailAddress']);
          $subject = $this->get_value('reminderSubject');
          $content = $this->get_value('reminderContent');
          $e = new email_send($email, $subject, $content, "reminder");
          $e->send();
        }
      } 

      // Update reminder (reminderTime can be blank for task->moved_from_pending_to_open)
      if ($this->get_value('reminderRecuringInterval') == "No") {
        $this->deactivate();

      } else if ($this->get_value('reminderRecuringValue') != 0) {
        $interval = $this->get_value('reminderRecuringValue');
        $intervalUnit = $this->get_value('reminderRecuringInterval');
        $newtime = $this->get_next_reminder_time(strtotime($this->get_value('reminderTime')),$interval,$intervalUnit);
        $this->set_value('reminderTime', date("Y-m-d H:i:s", $newtime));
        $this->set_value('reminderAdvNoticeSent', 0);
        $this->save();
      }
    }
  }

  function get_next_reminder_time($reminderTime,$interval,$intervalUnit) {

    $date_H = date("H",$reminderTime);
    $date_i = date("i",$reminderTime);
    $date_s = date("s",$reminderTime);
    $date_m = date("m",$reminderTime);
    $date_d = date("d",$reminderTime);
    $date_Y = date("Y",$reminderTime);

     switch ($intervalUnit) {
       case "Minute": $date_i = date("i", $reminderTime) + $interval;       break;
       case "Hour":   $date_H = date("H", $reminderTime) + $interval;       break;
       case "Day":    $date_d = date("d", $reminderTime) + $interval;       break;
       case "Week":   $date_d = date("d", $reminderTime) + (7 * $interval); break;
       case "Month":  $date_m = date("m", $reminderTime) + $interval;       break;
       case "Year":   $date_Y = date("Y", $reminderTime) + $interval;       break;
     }

     return mktime($date_H,$date_i,$date_s,$date_m,$date_d,$date_Y);
  }


  // checks advanced notice time if any and mails advanced notice if it is time
  function mail_advnotice() {
    $date = strtotime($this->get_value('reminderTime'));
    // if no advanced notice needs to be sent then dont bother
    if ($this->get_value('reminderAdvNoticeInterval') != "No" 
    &&  $this->get_value('reminderAdvNoticeSent') == 0 && !$this->get_value("reminderHash")) {

      $date = strtotime($this->get_value('reminderTime'));
      $interval = -$this->get_value('reminderAdvNoticeValue');
      $intervalUnit = $this->get_value('reminderAdvNoticeInterval');
      $advnotice_time = $this->get_next_reminder_time($date,$interval,$intervalUnit);

      // only sent advanced notice if it is time to send it
      if (date("YmdHis", $advnotice_time) <= date("YmdHis")) {
        $recipients = $this->get_all_recipients();

        $subject = sprintf("Adv Notice: %s"
                          ,$this->get_value('reminderSubject'));
        $content = $this->get_value('reminderContent');

	foreach ($recipients as $person) {
          if ($person['emailAddress']) {
            $email = sprintf("%s %s <%s>"
                          ,$person['firstName']
                          ,$person['surname']
                          ,$person['emailAddress']);
            $e = new email_send($email, $subject, $content, "reminder_advnotice");
            $e->send();
          }
        }
        $this->set_value('reminderAdvNoticeSent', 1);
        $this->save();
      }
    }
  }

  // get the personID of the person who'll actually recieve this reminder
  // (i.e., convert "Task Assignee" into "Bob")
  function get_effective_person_id($recipient) {
    if($recipient->get_value('personID') == null) { //nulls don't come through correctly?
      // OK, slightly more complicated, we need to get the relevant link entity
      $metaperson = -$recipient->get_value('metaPersonID');
      $type = $this->get_value("reminderType");
      if($type == "task") {
        $task = new task();
        $task->set_id($this->get_value('reminderLinkID'));
        $task->select();

        switch($metaperson) {
          case REMINDER_METAPERSON_TASK_ASSIGNEE:
            return $task->get_value('personID');
          break;
          case REMINDER_METAPERSON_TASK_MANAGER:
            return $task->get_value('managerID');
          break;
        }
      } else {
        // we should never actually get here...
        alloc_error("Unknown metaperson.");
      }
    } else {
      return $recipient->get_value('personID');
    }
  }

  // gets a human-friendly description of the recipient, either the recipient name or in the form Task Manager (Bob)
  function get_recipient_description() {
      $people =& get_cached_table("person");
      $name = $people[$this->get_effective_person_id()]["name"];
      if($this->get_value("metaPerson") === null) {
        return $name;
      } else {
        return sprintf("%s (%s)", reminder::get_metaperson_name($this->get_value("metaPerson")), $name);
      }
  }

  // gets the human-friendly name of the meta person (e.g. R_MP_TASK_ASSIGNEE to "Task assignee")
  function get_metaperson_name($metaperson) {
    switch($metaperson) {
      case REMINDER_METAPERSON_TASK_ASSIGNEE:
        return "Task Assignee";
      break;
      case REMINDER_METAPERSON_TASK_MANAGER:
        return "Task Manager";
      break;
    }
  }

  function get_all_recipients() {
    $db = new db_alloc();
    $query = "SELECT * FROM reminderRecipient WHERE reminderID = %d";
    $db->query($query, $this->get_id());
    $people =& get_cached_table("person");
    $recipients = array();
    $person = new reminderRecipient();
    while ($db->next_record()) {
      $person->read_db_record($db);
      $id = $this->get_effective_person_id($person);
      // hash on person ID prevents multiple emails to the same person
      $recipients[$id] = $people[$id];
    }
    return $recipients;
  }

  function update_recipients($recipients) {
    $db = new db_alloc();
    $query = "DELETE FROM reminderRecipient WHERE reminderID = %d";
    $db->query($query, $this->get_id());
    foreach ((array)$recipients as $r) {
      $recipient = new reminderRecipient();
      $recipient->set_value('reminderID', $this->get_id());
      if ($r < 0) {
        $recipient->set_value('metaPersonID', $r);
        $recipient->set_value('personID', null);
      } else {
        $recipient->set_value('personID', $r);
      }
      $recipient->save();
    }
    return;
  }

  function get_list_filter($filter=array()) {
    $filter["type"] and $sql[] = prepare("reminderType='%s'",$filter["type"]);
    $filter["id"]   and $sql[] = prepare("reminderLinkID=%d",$filter["id"]);
    $filter["filter_recipient"] and $sql[] = prepare("personID = %d", $filter["filter_recipient"]);
    imp($filter["filter_reminderActive"]) and $sql[] = prepare("reminderActive = %d",$filter["filter_reminderActive"]);
    return $sql;
  }

  function get_list($_FORM) {
    $filter = reminder::get_list_filter($_FORM);
    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }
    $db = new db_alloc();
    $q = "SELECT reminder.*,reminderRecipient.*,token.*,tokenAction.*, reminder.reminderID as rID
            FROM reminder
       LEFT JOIN reminderRecipient ON reminder.reminderID = reminderRecipient.reminderID
       LEFT JOIN token ON reminder.reminderHash = token.tokenHash
       LEFT JOIN tokenAction ON token.tokenActionID = tokenAction.tokenActionID
           ".$f."
        GROUP BY reminder.reminderID
        ORDER BY reminderTime,reminderType";
    $db->query($q);
    while ($row = $db->row()) {
      $reminder = new reminder();
      $reminder->read_db_record($db);
      $rows[] = $row;
    }
    return $rows;
  }

  function get_list_html($type=null,$id=null) {
    global $TPL;
    $_REQUEST["type"] = $type;
    $_REQUEST["id"] = $id;
    $TPL["reminderRows"] = reminder::get_list($_REQUEST);
    $type and $TPL["returnToParent"] = $type;
    $type or  $TPL["returnToParent"] = "list";
    include_template(dirname(__FILE__)."/../templates/reminderListS.tpl");
  }

}



?>
