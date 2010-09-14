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

define("REMINDER_METAPERSON_TASK_ASSIGNEE", 2);
define("REMINDER_METAPERSON_TASK_MANAGER", 3);

class reminder extends db_entity {
  public $data_table = "reminder";
  public $display_field_name = "reminderSubject";
  public $key_field = "reminderID";
  public $data_fields = array("reminderType"
                             ,"reminderLinkID"
                             ,"personID"
                             ,"metaPerson"
                             ,"reminderTime"
                             ,"reminderRecuringInterval"
                             ,"reminderRecuringValue"
                             ,"reminderAdvNoticeSent"
                             ,"reminderAdvNoticeInterval"
                             ,"reminderAdvNoticeValue"
                             ,"reminderSubject"
                             ,"reminderContent"
                             ,"reminderModifiedTime"
                             ,"reminderModifiedUser"
                             );

  // set the modified time to now
  function set_modified_time() {
    $this->set_value("reminderModifiedTime", date("Y-m-d H:i:s"));
  }

  function get_recipients() {
    $db = new db_alloc;
    $recipients = array("-1"=>"-- all --");
    $type = $this->get_value('reminderType');
    if ($type == "project") {
      $query = sprintf("SELECT * 
                          FROM projectPerson 
                     LEFT JOIN person ON projectPerson.personID=person.personID 
                         WHERE projectPerson.projectID = %d 
                      ORDER BY person.username",$this->get_value('reminderLinkID'));

    } else if ($type == "task") {
      // Modified query option: to send to all people on the project that this task is from.
      $recipients = array("-3" => "Task Manager"
                         ,"-2" => "Task Assignee"
                         ,"-1" => "-- all --");

      $db->query("SELECT projectID FROM task WHERE taskID = %s",$this->get_value('reminderLinkID'));
      $db->next_record();

      if ($db->f('projectID')) {
        $query = sprintf("SELECT * 
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
      $person = new person;
      $person->read_db_record($db);
      $recipients[$person->get_id()] = $person->get_name();
    }

    return $recipients;
  }

  function get_recipient_options() {
    global $current_user;
    $fail = false;

    $recipients = $this->get_recipients();
    $type = $this->get_value('reminderType');
    $recipient = -1 * $this->get_value('metaPerson');
    $recipient or $recipient = $this->get_value('personID');
    
    //project reminder
    if (!$recipient && $type == "project") {

    //task reminder
    } else if(!$recipient && $type == "task") {
      $task = new task;
      $task->set_id($this->get_value('reminderLinkID'));
      $task->select();
      //get the task assignee
      $recipient = $task->get_value('personID');
      //if the assignee is not part of the project choose the project manager
    } 

    //default -  set to logged in user
    if(!$recipient) {
      if ($_GET["personID"]){
        $recipient = $_GET["personID"];
      } else {
        $recipient = $current_user->get_id();
      }
    }
    return page::select_options($recipients, $recipient);
  }

  function get_day_options() {
    $days = array("1"=>"1", "2"=>"2", "3"=>"3", "4"=>"4", "5"=>"5", "6"=>"6", "7"=>"7",
                  "8"=>"8", "9"=>"9", "10"=>"10", "11"=>"11", "12"=>"12", "13"=>"13",
                  "14"=>"14", "15"=>"15", "16"=>"16", "17"=>"17", "18"=>"18", "19"=>"19", 
                  "20"=>"20", "21"=>"21", "22"=>"22", "23"=>"23", "24"=>"24", "25"=>"25", 
                  "26"=>"26", "27"=>"27", "28"=>"28", "29"=>"29", "30"=>"30", "31"=>"31");
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $day = date("d", $date);
    } else {
      $day = date("d", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($days, $day);
  }

  function get_month_options() {
    $months = array("1"=>"January", "2"=>"February", "3"=>"March", "4"=>"April", "5"=>"May", "6"=>"June", "7"=>"July", "8"=>"August", "9"=>"September", "10"=>"October", "11"=>"November", "12"=>"December");
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $month = date("m", $date);
    } else {
      $month = date("m", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($months, $month);
  }

  function get_year_options() {
    $years = array();
    for ($i = date("Y")-10; $i < date("Y") + 21; $i++) {
      $years[$i] = $i;
    }
    if ($this->get_value('reminderTime') != "") {
      $date = strtotime($this->get_value('reminderTime'));
      $year = date("Y", $date);
    } else {
      $year = date("Y", mktime(date("H"), date("i") + 5 - (date("i") % 5), 0, date("m"), date("d"), date("Y")));
    }
    return page::select_options($years, $year);
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
      $project = new project;
      $project->set_id($this->get_value('reminderLinkID'));
      if ($project->select() == false || $project->get_value('projectStatus') == "Archived") {
        return false;
      }
    } else if ($type == "task") {
      $task = new task;
      $task->set_id($this->get_value('reminderLinkID'));
      if ($task->select() == false || $task->get_value("taskStatus") == 'closed') {
        return false;
      }
    } else if ($type == "client") {
      $client = new client;
      $client->set_id($this->get_value('reminderLinkID'));
      if ($client->select() == false || $client->get_value('clientStatus') == "Archived") {
        return false;
      }
    }
    return true;
  }

  // mail out reminder and update to next date if repeating or remove if onceoff
  // checks to make sure that it is the right time to send reminder should be 
  // dome before calling this function
  function mail_reminder() {
    // if no longer alive then dont send, just delete
    if (!$this->is_alive()) {
      $this->delete();
    } else {
    

      $date = strtotime($this->get_value('reminderTime'));
      // Only send reminder if it is time to send it
      if (date("YmdHis", $date) <= date("YmdHis")) {
     
        $people = get_cached_table("person");
        $person = $people[$this->get_effective_person_id()];
    
        if ($person['emailAddress']) {
          $email = sprintf("%s %s <%s>"
                          , $person['firstName']
                          , $person['surname']
                          , $person['emailAddress']);

          $subject = $this->get_value('reminderSubject');
          $content = $this->get_value('reminderContent');

          // Update reminder
          if ($this->get_value('reminderRecuringInterval') == "No") {
            if ($this->delete()) {
              $e = new alloc_email($email, $subject, $content, "reminder");
              $e->send();
            }
          } else if ($this->get_value('reminderRecuringValue') != 0) {

            $interval = $this->get_value('reminderRecuringValue');
            $intervalUnit = $this->get_value('reminderRecuringInterval');
            $newtime = $this->get_next_reminder_time($date,$interval,$intervalUnit);

            $this->set_value('reminderTime', date("Y-m-d H:i:s", $newtime));
            // reset advanced notice
            $this->set_value('reminderAdvNoticeSent', 0);
            if ($this->save()) {
              $e = new alloc_email($email, $subject, $content, "reminder");
              $e->send();
            }
          }
        } 
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
    &&  $this->get_value('reminderAdvNoticeSent') == 0) {

      $date = strtotime($this->get_value('reminderTime'));
      $interval = -$this->get_value('reminderAdvNoticeValue');
      $intervalUnit = $this->get_value('reminderAdvNoticeInterval');
      $advnotice_time = $this->get_next_reminder_time($date,$interval,$intervalUnit);

      // only sent advanced notice if it is time to send it
      if (date("YmdHis", $advnotice_time) <= date("YmdHis")) {

        $people = get_cached_table("person");
        $person = $people[$this->get_effective_person_id()];


        if ($person['emailAddress'] != "") {
          $email = sprintf("%s %s <%s>"
                          ,$person['firstName']
                          ,$person['surname']
                          ,$person['emailAddress']);

          $subject = sprintf("Adv Notice: %s"
                            ,$this->get_value('reminderSubject'));
          $content = $this->get_value('reminderContent');
          
          $e = new alloc_email($email, $subject, $content, "reminder_advnotice");
          $e->send();
          $this->set_value('reminderAdvNoticeSent', 1);
          $this->save();
        } 
      }
    }
  }

  // get the personID of the person who'll actually recieve this reminder
  // (i.e., convert "Task Assignee" into "Bob")
  function get_effective_person_id() {
    if($this->get_value('personID') === null) {
      // OK, slightly more complicated, we need to get the relevant link entity
      $metaperson = $this->get_value('metaPerson');
      $type = $this->get_value("reminderType");
      if($type == "task") {
        $task = new task;
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
        die("Unknown metaperson.");
      }
    } else {
      return $this->get_value('personID');
    }
  }

  // gets a human-friendly description of the recipient, either the recipient name or in the form Task Manager (Bob)
  function get_recipient_description() {
      $person = new person;
      $person->set_id($this->get_effective_person_id());
      $person->select();
      if($this->get_value("metaPerson") === null) {
        return $person->get_name();
      } else {
        return sprintf("%s (%s)", reminder::get_metaperson_name($this->get_value("metaPerson")), $person->get_name());
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

}



?>
