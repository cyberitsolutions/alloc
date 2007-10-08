<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

define("PERM_PROJECT_READ_TASK_DETAIL", 256);

class task extends db_entity {
  var $classname = "task";
  var $data_table = "task";
  var $display_field_name = "taskName";

  function task() {
    global $current_user;

      $this->db_entity();       // Call constructor of parent class
      $this->key_field = new db_field("taskID");
      $this->data_fields = array("taskName"=>new db_field("taskName", array("empty_to_null"=>false))
                                 , "taskDescription"=>new db_field("taskDescription")
                                 , "creatorID"=>new db_field("creatorID")
                                 , "closerID"=>new db_field("closerID")
                                 , "priority"=>new db_field("priority")
                                 , "timeEstimate"=>new db_field("timeEstimate", array("empty_to_null"=>true))
                                 , "dateCreated"=>new db_field("dateCreated")
                                 , "dateAssigned"=>new db_field("dateAssigned")
                                 , "dateClosed"=>new db_field("dateClosed")
                                 , "dateTargetStart"=>new db_field("dateTargetStart")
                                 , "dateTargetCompletion"=>new db_field("dateTargetCompletion")
                                 , "dateActualStart"=>new db_field("dateActualStart")
                                 , "dateActualCompletion"=>new db_field("dateActualCompletion")
                                 , "taskComments"=>new db_field("taskComments")
                                 , "projectID"=>new db_field("projectID")
                                 , "parentTaskID"=>new db_field("parentTaskID")
                                 , "taskTypeID"=>new db_field("taskTypeID")
                                 , "personID"=>new db_field("personID")
                                 , "managerID"=>new db_field("managerID")
                                 
      );

    if (isset($current_user)) {
      $this->set_value("creatorID", $current_user->get_id());
    }

    $this->permissions[PERM_PROJECT_READ_TASK_DETAIL] = "Read details";
  }

  function close_off_children_recursive() {
    // mark all children as complete
    global $current_user;
    $db = new db_alloc;
    if ($this->get_id()) {
      $query = "SELECT * FROM task WHERE parentTaskID = ".$this->get_id();
      $db->query($query);
                                                                                                                               
      while ($db->next_record()) {
        $task = new task;
        $task->read_db_record($db);
        $orig_dateActualCompletion = $task->get_value("dateActualCompletion");
        $task->get_value("dateActualStart")      || $task->set_value("dateActualStart", date("Y-m-d"));
        $task->get_value("dateActualCompletion") || $task->set_value("dateActualCompletion", date("Y-m-d"));
        $task->get_value("closerID")             || $task->set_value("closerID", $current_user->get_id());
        $task->get_value("dateClosed")           || $task->set_value("dateClosed",date("Y-m-d H:i:s"));           
        $task->save();

        // If it isn't already closed, then send emails..
        if (!$orig_dateActualCompletion) {
          $m = $task->email_task_closed();
          $m and $msg[] = $m;
        }
        $arr = $task->close_off_children_recursive();
        if (is_array($arr)) {
          $msg = array_merge($msg,$arr);
        }
      }
    }
    return $msg;
  }

  function email_task_reassigned($old_assignee) {
    global $current_user;
    $recipients = array();
    # Mail to old
    if ($current_user->get_id() != $old_assignee) {
      $recipients[] = $old_assignee;
    }
    if ($current_user->get_id() != $this->get_value("personID")) {
      $recipients[] = "assignee";
    }
    if ($current_user->get_id() != $this->get_value("creatorID")) {
      $recipients[] = "creator";
    }
    if ($current_user->get_id() != $this->get_value("managerID")) {
      $recipients[] = "manager";
    }
  
    if ($recipients) {
      $successful_recipients = $this->send_emails($recipients,"task_reassigned");
      $successful_recipients and $msg = "Email sent: ".stripslashes($successful_recipients).", Task Reassigned: ".stripslashes($this->get_value("taskName"));
    }
 
    return $msg;
  }

  function email_task_closed() {
    global $current_user;
    if ($current_user->get_id() != $this->get_value("creatorID")) {
      $recipients[] = "creator";
    }
    if ($current_user->get_id() != $this->get_value("managerID")) {
      $recipients[] = "manager";
    }
    if ($current_user->get_id() != $this->get_value("personID")) {
      $recipients[] = "assignee";
    }

    if ($recipients) {
      $successful_recipients = $this->send_emails($recipients,"task_closed");
      $successful_recipients and $msg = "Email sent: ".stripslashes($successful_recipients).", Task Closed: ".stripslashes($this->get_value("taskName"));
    }
    return $msg; 
  }

  function new_message_task() {
    // Create a reminder with its regularity being based upon what the task priority is

    if ($this->get_value("priority") == 1) {
      $reminderInterval = "Day";
      $intervalValue = 1;
      $message = "A priority 1 message has been created for you.  You will continue to receive these ";
      $message.= "emails until you kill off this task either by deleting it or putting a date in its ";
      $message.= "'Date Actual Completion' box.";
    } else {
      $reminderInterval = "Day";
      $intervalValue = $this->get_value("priority");
      $message = "A priority ".$this->get_value("priority")." message has been created for you.  You will ";
      $message.= "continue to receive these ";
      $message.= "every ".$this->get_value("priority")." days until you kill this task either by deleting it ";
      $message.= "or putting a date in its 'Date Actual Completion' box.";
    }
    $people[] = $this->get_value("personID");
    $this->create_reminders($people, $message, $reminderInterval, $intervalValue);
  }

  function new_fault_task() {
    // Create a reminder with its regularity being based upon what the task priority is
    $db = new db_alloc;

    if ($this->get_value("priority") == 1) {
      if ($this->get_value("projectID")) {
        $db->query("SELECT * from projectPerson WHERE projectID = ".$this->get_value("projectID"));
        while ($db->next_record()) {
          $people[] = $db->f("personID");
        }
      } else {
        $people[] = $this->get_value("personID");
      }
      $message = "THIS IS IMPORTANT.\nThis is a priority 1 fault/task/alert.  See the task immediately for details.";
      $message.= "\nYou will receive one of these emails every four hours until the task has a date in its 'Actual ";
      $message.= "Completion' box.";
      // $message.= "\n\n<a href=\"" . $this->get_url() . "\">";
      $reminderInterval = "Hour";
      $intervalValue = 4;
    } else if ($this->get_value("priority") == 2) {
      if ($this->get_value("projectID")) {
        $db->query("SELECT * 
                      FROM projectPerson LEFT JOIN projectPersonRole on projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID 
                     WHERE (projectPersonRole.projectPersonRoleHandle = 'isManager'  OR projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient') AND projectID = ".$this->get_value("projectID"));
        while ($db->next_record()) {
          $people[] = $db->f("personID");
        }
      } else {
        $people[] = $this->get_value("personID");
      }
      $message = "This is a priority 2 fault/task/alert.  See the task immediately for details.";
      $message.= "You will receive an email once a day everyday until the task is resolved.";
      // $message.= "\n\n<a href=\"" . $this->get_url() . "\">";
      $reminderInterval = "Day";
      $intervalValue = 1;
    }

    $this->create_reminders($people, $message, $reminderInterval, $intervalValue);
  }

  function create_reminders($people, $message, $reminderInterval, $intervalValue) {
    if (is_array($people)) {
      foreach($people as $personID) {
        $person = new person;
        $person->set_id($personID);
        $person->select();
        if ($person->get_value("emailAddress")) {
          $this->create_reminder($personID, $message, $reminderInterval, $intervalValue);
        }
      }
    }
  }

  function create_reminder($personID, $message, $reminderInterval, $intervalValue) {
    global $current_user;
    $reminder = new reminder;
    $reminder->set_value('reminderType', "task");
    $reminder->set_value('reminderLinkID', $this->get_id());
    $reminder->set_value('reminderRecuringInterval', $reminderInterval);
    $reminder->set_value('reminderRecuringValue', $intervalValue);
    $reminder->set_value('reminderSubject', "Task Reminder: ".$this->get_id()." ".$this->get_value("taskName"));
    $reminder->set_value('reminderContent', "\nReminder Created by ".$current_user->get_username(1)
                         ."\n\n".$message."\n\n".$this->get_value("taskDescription"));

    $reminder->set_value('reminderAdvNoticeSent', "0");
    $reminder->set_value('reminderAdvNoticeInterval', "No");
    $reminder->set_value('reminderAdvNoticeValue', "0");

    $reminder->set_value('reminderTime', date("Y-m-d H:i:s"));
    $reminder->set_value('personID', $personID);

    $reminder->save();
  }

  function is_owner($person = "") {
    // A user owns a task if the 'own' the project
    if ($this->get_id()) {
      // Check for existing task
      $p = $this->get_foreign_object("project");
    } else if ($_POST["projectID"]) {
      // Or maybe they are creating a new task
      $p = new project;
      $p->set_id($_POST["projectID"]);
    }

    // if this task doesn't exist (no ID) 
    // OR the person has isManager or canEditTasks for this tasks project 
    // OR if this person is the Creator of this task.
    // OR if this person is the For Person of this task.
    // OR if this person has super 'manage' perms
    if (
       !$this->get_id() 
    || (is_object($p) && ($p->has_project_permission($person, array("isManager", "canEditTasks"))) 
    || $this->get_value("creatorID") == $person->get_id()
    || $this->get_value("personID") == $person->get_id()
    || $person->have_role("manage")
    )) {
      return true;
    }
  }

  function has_attachment_permission($person) {
    return $this->is_owner($person);
  }

  function has_attachment_permission_delete($person) {
    return $this->is_owner($person);
  }

  function get_parent_task_select($projectID="") {
    global $TPL;
    
    $options = get_option("None", "0");
    if (is_object($this)) {
      $projectID = $this->get_value("projectID");
      $parentTaskID = $this->get_value("parentTaskID");
    }

    $projectID or $projectID = $_GET["projectID"];
    $parentTaskID or $parentTaskID = $_GET["parentTaskID"];

    $db = new db_alloc;
    $options = get_option("None", "0");
    if ($projectID) {
      $query = sprintf("SELECT * 
                        FROM task 
                        WHERE projectID= '%d' 
                        AND taskTypeID = 2 
                        AND (dateActualCompletion IS NULL or dateActualCompletion = '') 
                        ORDER BY taskName", $projectID);
      $db->query($query);
      $options.= get_options_from_db($db, "taskName", "taskID", $parentTaskID,70);
    }
    return "<select name=\"parentTaskID\">".$options."</select>";
  }

  function get_task_cc_list_select($projectID="") {
    global $TPL;
    $db = new db_alloc;
    $taskCCListOptions = array();
    
    if (is_object($this)) {
      $projectID = $_GET["projectID"] or $projectID = $this->get_value("projectID");
      $q = sprintf("SELECT fullName,emailAddress FROM taskCCList WHERE taskID = %d",$this->get_id());
      $db->query($q);  
      while ($db->next_record()) {
        $taskCCList[] = urlencode(base64_encode(serialize(array("name"=>sprintf("%s",stripslashes($db->f("fullName"))),"email"=>$db->f("emailAddress")))));

        // And add the list of people who are already in the taskCCList for this task, just in case they get deleted from the client pages
        // This email address will be overwritten by later entries
        $taskCCListOptions[$db->f("emailAddress")] = stripslashes($db->f("fullName"));
      }
    }

    if ($projectID) {

      // Get primary client contact from Project page
      $q = sprintf("SELECT projectClientName,projectClientEMail FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $taskCCListOptions[$db->f("projectClientEMail")] = stripslashes($db->f("projectClientName"));
  
      // Get all other client contacts from the Client pages for this Project
      $q = sprintf("SELECT clientID FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $clientID = $db->f("clientID");
      $q = sprintf("SELECT clientContactName, clientContactEmail FROM clientContact WHERE clientID = %d",$clientID);
      $db->query($q);
      while ($db->next_record()) {
        $taskCCListOptions[$db->f("clientContactEmail")] = stripslashes($db->f("clientContactName"));
      }

      // Get all the project people for this tasks project
      $q = sprintf("SELECT emailAddress, firstName, surname 
                     FROM projectPerson LEFT JOIN person on projectPerson.personID = person.personID 
                    WHERE projectPerson.projectID = %d",$projectID);
      $db->query($q);
      while ($db->next_record()) {
        $taskCCListOptions[$db->f("emailAddress")] = stripslashes($db->f("firstName")." ".$db->f("surname"));
      }
    }

    $extra_interested_parties = config::get_config_item("defaultInterestedParties") or $extra_interested_parties=array();
    foreach ($extra_interested_parties as $name => $email) {
      $taskCCListOptions[$email] = stripslashes($name);
    }


    if (is_array($taskCCListOptions)) {
      asort($taskCCListOptions);

      foreach ($taskCCListOptions as $email => $name) {
        if ($email) {
          $name = trim($name);
          $str = trim(htmlentities($name." <".$email.">"));
          $options[urlencode(base64_encode(serialize(array("name"=>sprintf("%s",$name),"email"=>$email))))] = $str;
        }
      }
    }
    $str = "<select name=\"taskCCList[]\" size=\"5\" multiple=\"true\"  style=\"width:300px\">".get_select_options($options,$taskCCList)."</select>";
    return $str;
  }

  function get_personList_dropdown($projectID,$taskID=false) {
    global $current_user;
 
    $db = new db_alloc;

    if ($_GET["timeSheetID"]) {
      $ts_query = sprintf("SELECT * FROM timeSheet WHERE timeSheetID = %d",$_GET["timeSheetID"]);
      $db->query($ts_query);
      $db->next_record();
      $owner = $db->f("personID");

    } else if (is_object($this) && $this->get_value("personID")) {
      $owner = $this->get_value("personID");

    } else if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $owner = $t->get_value("personID");

    } else if (!is_object($this) || !$this->get_id()) {
      $owner = $current_user->get_id();
    }

    $peoplenames = person::get_username_list($owner);

    if ($projectID) {
      $q = sprintf("SELECT * 
                      FROM projectPerson 
                 LEFT JOIN person ON person.personID = projectPerson.personID 
                     WHERE person.personActive = 1 
                       AND projectID = %d
                  ORDER BY firstName, username
                   ",$projectID);
      $db->query($q);
      while ($row = $db->row()) {
        $ops[$row["personID"]] = $peoplenames[$row["personID"]];
      }
    } else {
      $ops = $peoplenames;
    }

    $ops[$owner] or $ops[$owner] = $peoplenames[$owner];
   
    $str = '<select name="personID">';
    $str.= get_option("None", "0", $owner == 0)."\n";
    $str.= get_select_options($ops, $owner);
    $str.= '</select>';
    return $str;
  }

  function get_managerPersonList_dropdown($projectID,$taskID=false) {
    global $current_user;
 
    $db = new db_alloc;

    if ($_GET["timeSheetID"]) {
      $ts_query = sprintf("SELECT * FROM timeSheet WHERE timeSheetID = %d",$_GET["timeSheetID"]);
      $db->query($ts_query);
      $db->next_record();
      $owner = $db->f("personID");

    } else if (is_object($this) && $this->get_value("managerID")) {
      $owner = $this->get_value("managerID");

    } else if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $owner = $t->get_value("managerID");

    } else if (!is_object($this) || !$this->get_id()) {
      $owner = $current_user->get_id();
    }

    $peoplenames = person::get_username_list($owner);

    if ($projectID) {
      $q = sprintf("SELECT * 
                      FROM projectPerson 
                 LEFT JOIN person ON person.personID = projectPerson.personID 
                     WHERE person.personActive = 1 
                       AND projectID = %d
                  ORDER BY firstName, username
                   ",$projectID);
      $db->query($q);
      while ($row = $db->row()) {
        $ops[$row["personID"]] = $peoplenames[$row["personID"]];
      }
    } else {
      $ops = $peoplenames;
    }

    $ops[$owner] or $ops[$owner] = $peoplenames[$owner];
   
    $str = '<select name="managerID">';
    $str.= get_option("None", "0", $owner == 0)."\n";
    $str.= get_select_options($ops, $owner);
    $str.= '</select>';
    return $str;
  }

  function set_option_tpl_values() {
    // Set template values to provide options for edit selects
    global $TPL, $current_user, $isMessage;

    $db = new db_alloc;

    $projectID = $_GET["projectID"] or $projectID = $this->get_value("projectID");
    $TPL["personOptions"] = task::get_personList_dropdown($projectID);
    $TPL["managerPersonOptions"] = task::get_managerPersonList_dropdown($projectID);

    // TaskType Options
    $taskType = new taskType;
    $TPL["taskTypeOptions"] = $taskType->get_dropdown_options("taskTypeID","taskTypeName",$this->get_value("taskTypeID"));

    // Project Options - Select all projects 
    $query = sprintf("SELECT * FROM project WHERE projectStatus = 'current' ORDER BY projectName");
    $db->query($query);
    $TPL["projectOptions"] = get_option("None", "0", $projectID == 0)."\n";
    $TPL["projectOptions"].= get_options_from_db($db, "projectName", "projectID", $projectID,60);

    // TaskCommentTemplateOptions - Select all task comment templates
    $query = sprintf("SELECT * FROM taskCommentTemplate ORDER BY taskCommentTemplateName");
    $db->query($query);
    $TPL["taskCommentTemplateOptions"] = get_option("Comment Templates", "0")."\n";
    $TPL["taskCommentTemplateOptions"].= get_options_from_db($db, "taskCommentTemplateName", "taskCommentTemplateID",false);

    $priority = $this->get_value("priority") or $priority = 3;
    $taskPriorities = config::get_config_item("taskPriorities") or $taskPriorities = array();
    $tp = array();
    foreach($taskPriorities as $key => $arr) {
      $tp[$key] = $arr["label"];
    }
    $TPL["priorityOptions"] = get_select_options($tp,$priority);
    $priority and $TPL["priorityLabel"] = " <div style=\"display:inline; color:".$taskPriorities[$priority]["colour"]."\">[".$tp[$priority]."]</div>";

    // We're building these two with the <select> tags because they will be replaced by an AJAX created dropdown when
    // The projectID changes.
    $TPL["parentTaskOptions"] = $this->get_parent_task_select();
    $TPL["taskCCListOptions"] = $this->get_task_cc_list_select();
    

    $db->query(sprintf("SELECT fullName,emailAddress FROM taskCCList WHERE taskID = %d ORDER BY fullName",$this->get_id()));
    while ($db->next_record()) {
      $str = trim(htmlentities($db->f("fullName")." <".$db->f("emailAddress").">"));
      $value = urlencode(base64_encode(serialize(array("name"=>sprintf("%s",$db->f("fullName")),"email"=>$db->f("emailAddress")))));
      $TPL["taskCCList_hidden"].= $commar.$str."<input type=\"hidden\" name=\"taskCCList[]\" value=\"".$value."\">";
      $commar = "<br/>";
    }
    

    // The options for the email dropdown boxes
    #$TPL["task_createdBy"] and $creator_extra = " (".$TPL["task_createdBy"].")";
    #$TPL["person_username"] and $person_extra = " (".$TPL["person_username"].")";
    #$emailOptions[] = "Email Sending Options";
    #$TPL["task_createdBy_personID"] != $current_user->get_id() and $emailOptions["creator"] = "Email Task Creator".$creator_extra;
    #$TPL["person_username_personID"] != $current_user->get_id() and $emailOptions["assignee"] = "Email Task Assignee".$person_extra;
    #$emailOptions["isManager"] = "Email Project Managers";
    #$emailOptions["canEditTasks"] = "Email Project Engineers";
    #$emailOptions["all"] = "Email Project Managers & Engineers";

    // Email dropdown options for the comment box
    #if ($current_user->get_id() == $this->get_value("creatorID")) {
      #$taskCommentEmailSelected = "assignee";
    #}   
    #if ($current_user->get_id() == $this->get_value("personID")) {
      #$taskCommentEmailSelected = "creator";
    #}   
    #$TPL["taskCommentEmailOptions"] = get_select_options($emailOptions,$taskCommentEmailSelected);



    // If we're viewing the printer friendly view
    if ($_GET["media"] == "print") {
      // Parent Task label
      $t = new task;
      $t->set_id($this->get_value("parentTaskID"));
      $t->select();
      $TPL["parentTask"] = $t->get_display_value();

      // Task Type label
      $tt = new taskType;
      $tt->set_id($this->get_value("taskTypeID"));
      $tt->select();
      $TPL["taskType"] = $tt->get_display_value();

      // Priority
      $TPL["priority"] = $this->get_value("priority");

      // Assignee label
      $p = new person;
      $p->set_id($this->get_value("personID"));
      $p->select();
      $TPL["person"] = $p->get_display_value();
  
      // Project label
      $p = new project;
      $p->set_id($this->get_value("projectID"));
      $p->select();
      $TPL["projectName"] = $p->get_display_value();
    }

  }

  function get_email_recipients($options=array()) {
    #static $people;
    $recipients = array();

    // Load up all people into array
    #if (!$people) { 
      #$db = new db_alloc;
      #$db->query("SELECT personID, username, firstName, surname, emailAddress FROM person");
      #while($db->next_record()) {
        #if ($db->f("firstName") && $db->f("surname")) {
          #$db->Record["fullName"] = $db->f("firstName")." ".$db->f("surname");
        #} else {
          #$db->Record["fullName"] = $db->f("username");
        #}
        #$people[$db->f("personID")] = $db->Record;
      #}
    #}

    $people = get_cached_table("person");


    foreach ($options as $selected_option) {

      // Determine recipient/s 

      if ($selected_option == "CCList") {
        $db = new db_alloc;
        $q = sprintf("SELECT * FROM taskCCList WHERE taskID = %d",$this->get_id()); 
        $db->query($q);
        while($row = $db->next_record()) {
          $row["isCC"] = true;
          $row["name"] = $row["fullName"];
          $recipients[] = $row;
        }
      } else if ($selected_option == "creator") {
        $recipients[] = $people[$this->get_value("creatorID")];

      } else if ($selected_option == "manager") {
        $recipients[] = $people[$this->get_value("managerID")];

      } else if ($selected_option == "assignee") {
        $recipients[] = $people[$this->get_value("personID")];

      } else if ($selected_option == "isManager" || $selected_option == "canEditTasks" || $selected_option == "all") {
        $q = sprintf("SELECT personID,projectPersonRoleHandle 
                        FROM projectPerson 
                   LEFT JOIN projectPersonRole ON projectPersonRole.projectPersonRoleID = projectPerson.projectPersonRoleID 
                       WHERE projectID = %d", $this->get_value("projectID"));
        if ($selected_option != "all") {
          $q .=  sprintf(" AND projectPersonRole.projectPersonRoleHandle = '%s'",$selected_option);
        }

        $db->query($q);
        while ($db->next_record()) {
          $recipients[] = $people[$db->f("personID")];
        }
      } else if (is_int($selected_option)){
        $recipients[] = $people[$selected_option];
      }
    }
    return $recipients;
  }

  function send_emails($selected_option, $type="", $body="", $from=false) {
    global $current_user;
    $recipients = $this->get_email_recipients($selected_option);


    // Build up To: and Bcc: headers
    foreach ($recipients as $recipient) {
      unset($recipient_full_name);

      if ($recipient["firstName"] && $recipient["surname"]) {
        $recipient_full_name = $recipient["firstName"]." ".$recipient["surname"];
      } else if ($recipient["fullName"]) {
        $recipient_full_name = $recipient["fullName"];
      } else if ($recipient["name"]) {
        $recipient_full_name = $recipient["name"];
      }

      if ($recipient_full_name && $recipient["emailAddress"] && $recipient_full_name != $from) { 
        $to_address.= $commar.$recipient_full_name.": ;";
        $bcc.= $commar.$recipient["emailAddress"];
        $successful_recipients.= $commar.$recipient_full_name;
        $commar = ", ";
      }
    }

    $headers["Bcc"] = $bcc;
    if ($headers["Bcc"]) {

      $types = array('task_created'    => "Task Created"
                    ,'task_closed'     => "Task Closed"
                    ,'task_comments'   => "Task Comments"
                    ,'task_reassigned' => "Task Reassigned"
                    );
    
      $subject = $types[$type];
                    

      // New email object wrapper takes care of logging etc.
      $email = new alloc_email;

      // REMOVE ME!!
      $email->ignore_no_email_urls = true;
      $email->ignore_no_email_hosts = true;

      $p = new project;
      $p->set_id($this->get_value("projectID"));
      $p->select();

      $from_name = $from or $from_name = $current_user->get_username(1);
      
      $message.= "\n".stripslashes($subject." by ".$from_name);
      $body and $message.= "\n\n".stripslashes(wordwrap($body));
      $message.= "\n\n".config::get_config_item("allocURL")."task/task.php?taskID=".$this->get_id();
      $message.= "\n\nProject: ".stripslashes($p->get_value("projectName"));
      $message.= "\n   Task: ".stripslashes($this->get_value("taskName"));
      $message.= "\n   Desc: ".stripslashes($this->get_value("taskDescription"));


      $message.= "\n\n-- \nIf you have any questions, please reply to this email or contact: ";
      config::get_config_item("companyName")            and $message.= "\n".config::get_config_item("companyName");
      config::get_config_item("companyContactAddress")  and $message.= "\n".config::get_config_item("companyContactAddress");
      config::get_config_item("companyContactEmail")    and $message.= "\nE: ".config::get_config_item("companyContactEmail");
      config::get_config_item("companyContactPhone")    and $message.= "\nP: ".config::get_config_item("companyContactPhone");
      config::get_config_item("companyContactFax")      and $message.= "\nF: ".config::get_config_item("companyContactFax");
      config::get_config_item("companyContactHomePage") and $message.= "\nW: ".config::get_config_item("companyContactHomePage");

      $message = "\n".wordwrap($message)."\n\n";

      $token = new token;
      if ($token->select_token_by_entity("task",$this->get_id())) {
        $hash = $token->get_value("tokenHash");
      } else {
        $token->set_value("tokenEntity","task");
        $token->set_value("tokenEntityID",$this->get_id());
        $token->set_value("tokenActionID",1);
        $token->set_value("tokenActive",1);
        $token->set_value("tokenCreatedBy",$current_user->get_id());
        $token->set_value("tokenCreatedDate",date("Y-m-d H:i:s"));
        $hash = $token->generate_hash();
        $token->set_value("tokenHash",$hash);
        $token->save();
      }

      if ($hash && config::get_config_item("allocEmailKeyMethod") == "headers") {
        $email->set_message_id($hash);
      } else if ($hash && config::get_config_item("allocEmailKeyMethod") == "subject") {
        $email->set_message_id();
        $subject_extra = " {Key:".$hash."}";
      }

      $headers["Reply-To"] = "All parties via ".ALLOC_DEFAULT_FROM_ADDRESS;
      $headers["From"] = $from_name." via ".ALLOC_DEFAULT_FROM_ADDRESS;
      $subject = $subject.": ".$this->get_id()." ".stripslashes($this->get_value("taskName")).$subject_extra;
      if ($email->send($to_address, $subject, $message, $type, $headers)) {
        return $successful_recipients;
      }
    }   
  }

  function get_task_link($_FORM=array()) {
    $rtn = "<a href=\"".$this->get_url()."\">";
    $rtn.= $this->get_task_name($_FORM);
    $rtn.= "</a>";
    return $rtn;
  }

  function get_task_name($_FORM=array()) {

    $_FORM["showTaskID"] and $id = $this->get_id()." ";

    if ($this->get_value("taskTypeID") == TT_PHASE && $_FORM["return"] == "html") {
      $rtn = "<strong>".$id.stripslashes($this->get_value("taskName"))."</strong>";
    } else if ($this->get_value("taskTypeID") == TT_PHASE) {
      $rtn = $id.stripslashes($this->get_value("taskName"));
    } else {
      substr($this->get_value("taskName"),0,140) != $this->get_value("taskName") and $dotdotdot = "...";
      $rtn = $id.substr(stripslashes($this->get_value("taskName")),0,140).$dotdotdot;
    }
    return $rtn;
  }

  function get_url() {
    global $sess;
    $sess or $sess = new Session;

    $url = "task/task.php?taskID=".$this->get_id();

    if ($sess->Started()) {
      $url = $sess->url(SCRIPT_PATH.$url);

    // This for urls that are emailed
    } else {
      static $prefix;
      $prefix or $prefix = config::get_config_item("allocURL");
      $url = $prefix.$url;
    }
    return $url;
  }

function get_task_statii_array() {
  $taskStatii = task::get_task_statii();
  $taskStatiiArray[""] = "";
  foreach($taskStatii as $key => $arr) {
    $taskStatiiArray[$key] = $arr["label"];
  }
  return $taskStatiiArray;
}

  function get_task_statii() {
    $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 2, date("Y")))." 00:00:00";
    if (date("D") == "Mon") {
      $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 4, date("Y")))." 00:00:00";
    } 

    $taskStatusFilter = array("not_completed" =>array("label"=>"Incomplete"
                                                     ,"sql"  =>"(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '')")
                             ,"in_progress"   =>array("label"=>"Started"
                                                     ,"sql"  =>"((task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') AND (task.dateActualStart IS NOT NULL AND task.dateActualStart != ''))")
                             ,"new"           =>array("label"=>"New Tasks"
                                                     ,"sql"  =>"(task.dateActualCompletion IS NULL AND task.dateCreated >= '".$past."')")
                             ,"due_today"     =>array("label"=>"Due Today"
                                                     ,"sql"  =>"(task.dateActualCompletion IS NULL AND task.dateTargetCompletion = '".date("Y-m-d")."')")
                             ,"overdue"       =>array("label"=>"Overdue"
                                                     ,"sql"  =>"((task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') 
                                                                AND 
                                                                (task.dateTargetCompletion IS NOT NULL AND task.dateTargetCompletion != '' AND '".date("Y-m-d")."' > task.dateTargetCompletion))")
                             ,"completed"     =>array("label"=>"Completed"
                                                     ,"sql"  =>"(task.dateActualCompletion IS NOT NULL AND task.dateActualCompletion != '')")
                             );
    return $taskStatusFilter;
  }

  function get_task_list_filter($filter=array()) {


    if (!$filter["projectID"] && $filter["projectType"] && $filter["projectType"] != "all") {
      $db = new db_alloc;
      $q = project::get_project_type_query($filter["projectType"],$filter["current_user"]);
      $db->query($q);
      while ($db->next_record()) {
        $filter["projectIDs"][] = $db->f("projectID");
      }

      // Oi! What a pickle. Need this flag for when someone doesn't have entries loaded in the above while loop.
      $firstOption = true;

    // If projectID is an array
    } else if ($filter["projectID"] && is_array($filter["projectID"])) {
      $filter["projectIDs"] = $filter["projectID"];

    // Else a project has been specified in the url
    } else if ($filter["projectID"] && is_numeric($filter["projectID"])) {
      $filter["projectIDs"][] = $filter["projectID"];
    }


    // If passed array projectIDs then join them up with commars and put them in an sql subset
    if (is_array($filter["projectIDs"]) && count($filter["projectIDs"])) {
      $sql["projectIDs"] = "(project.projectID IN (".implode(",",$filter["projectIDs"])."))";

    // If there are no projects in $filter["projectIDs"][] and we're attempting the first option..
    } else if ($firstOption) {
      $sql["projectIDs"] = "(project.projectID IN (0))";
    }

    // Task level filtering
    if ($filter["taskStatus"]) {
      $taskStatusFilter = task::get_task_statii();
      $sql[] = $taskStatusFilter[$filter["taskStatus"]]["sql"];
    }

    // Unset if they've only selected the topmost empty task type
    if (is_array($filter["taskTypeID"]) && count($filter["taskTypeID"])>=1 && !$filter["taskTypeID"][0]) {
      unset($filter["taskTypeID"][0]);
    }

    // If many create an SQL taskTypeID in (set) 
    if (is_array($filter["taskTypeID"]) && count($filter["taskTypeID"])) {
      $sql[] = "(taskTypeID in (".implode(",",$filter["taskTypeID"])."))";
    
    // Else if only one taskTypeID
    } else if ($filter["taskTypeID"]) {
      $sql[] = sprintf("(taskTypeID = %d)",$filter["taskTypeID"]);
    }


    // If personID filter and the view is byProject, then allow unassigned phases into the results 
    if ($filter["personID"] && $filter["taskView"] == "byProject") {
      $sql["personID"] = sprintf("(personID = %d or (taskTypeID = %d and (personID IS NULL or personID = '' or personID = 0)))",$filter["personID"],TT_PHASE);
    
    // If personID filter and the view is prioritised, then do a strict by personID query
    } else if ($filter["personID"] && $filter["taskView"] == "prioritised") {
      $sql["personID"] = sprintf("(personID = %d)",$filter["personID"]);
    }

    // If creatorID filter and the view is byProject, then allow unassigned phases into the results 
    if ($filter["creatorID"] && $filter["taskView"] == "byProject") {
      $sql["creatorID"] = sprintf("(creatorID = %d or (taskTypeID = %d and (creatorID IS NULL or creatorID = '' or creatorID = 0)))",$filter["creatorID"],TT_PHASE);
    
    // If creatorID filter and the view is prioritised, then do a strict by creatorID query
    } else if ($filter["creatorID"] && $filter["taskView"] == "prioritised") {
      $sql["creatorID"] = sprintf("(creatorID = %d)",$filter["creatorID"]);
    }

    // If managerID filter and the view is byProject, then allow unassigned phases into the results 
    if ($filter["managerID"] && $filter["taskView"] == "byProject") {
      $sql["managerID"] = sprintf("(managerID = %d or (taskTypeID = %d and (managerID IS NULL or managerID = '' or managerID = 0)))",$filter["managerID"],TT_PHASE);
    
    // If managerID filter and the view is prioritised, then do a strict by managerID query
    } else if ($filter["managerID"] && $filter["taskView"] == "prioritised") {
      $sql["managerID"] = sprintf("(managerID = %d)",$filter["managerID"]);
    }


    if ($filter["taskTimeSheetStatus"] == "open") {
      unset($sql["personID"]);
      $sql[] = sprintf("(dateActualCompletion IS NULL OR dateActualCompletion = '')");

    // Needs to override the personID setting from above
    } else if ($filter["taskTimeSheetStatus"] == "not_assigned"){ 
      unset($sql["personID"]);
      $sql[] = sprintf("((dateActualCompletion IS NULL OR dateActualCompletion = '') AND personID != %d)",$filter["personID"]);

    } else if ($filter["taskTimeSheetStatus"] == "recent_closed"){
      unset($sql["personID"]);
      $sql[] = sprintf("(taskTypeID = %d OR dateActualCompletion >= DATE_SUB(CURDATE(),INTERVAL 14 DAY))",TT_PHASE);

    } else if ($filter["taskTimeSheetStatus"] == "all") {
    }


    // This will be zero if not set. Which is fine since all top level tasks have a parentID of zero
    // This filter is unset for returning a prioritised list of tasks.
    $sql["parentTaskID"] = sprintf("(parentTaskID = %d)",$filter["parentTaskID"]);

    #die("<pre>".print_r($sql,1)."</pre>");
    return $sql;
  }

  function get_task_list($_FORM) {

    /*
     * This is the definitive method of getting a list of tasks that need a sophisticated level of filtering
     *
     * Display Options:
     *   showDates            = Show dates 1-4
     *   showDate1            = Date Target Start
     *   showDate2            = Date Target Completion
     *   showDate3            = Date Actual Start
     *   showDate4            = Date Actual Completion
     *   showProject          = The tasks Project (has different layout when prioritised vs byProject)
     *   showPriority         = The calculated overall priority, then the tasks, then the projects priority
     *   showStatus           = A colour coded textual description of the status of the task
     *   showCreator          = The tasks creator
     *   showAssigned         = The person assigned to the task
     *   showTimes            = The original estimate and the time billed and percentage
     *   showHeader           = A descriptive header row
     *   showDescription      = The tasks description
     *   showComments         = The tasks comments
     *   showTaskID           = Prefix the taskName with the task ID
     *   showManager          = Show the tasks manager
     *
     *
     * Filter Options:
     *   taskView             = byProject | prioritised
     *   return               = html | text | objects | dropdown_options
     *   limit                = appends an SQL limit (only for prioritised and objects views)
     *   projectIDs           = an array of projectIDs
     *   taskStatus           = completed | not_completed | in_progress | due_today | new | overdue
     *   taskTimeSheetStatus  = my_open | not_assigned | my_closed | my_recently_closed | all
     *   taskTypeID           = the task type
     *   current_user         = lets us set and fake a current_user id for when generating task emails and there is no $current_user object
     *   creatorID            = task creator
     *   managerID            = person managing task
     *   personID             = person assigned to the task
     *   parentTaskID         = id of parent task, all top level tasks have parentTaskID of 0, so this defaults to 0
     *  
     *
     * Other:
     *   padding              = Initial indentation level (useful for byProject lists)
     *
     */
 
    $filter = task::get_task_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    isset($_FORM["limit"]) && $_FORM["limit"] != "all" and $limit = sprintf("limit %d",$_FORM["limit"]); # needs to use isset cause of zeroes is a valid number 
    $_FORM["return"] or $_FORM["return"] = "html";

    if ($_FORM["showDates"]) {
      $_FORM["showDate1"] = true;
      $_FORM["showDate2"] = true;
      $_FORM["showDate3"] = true;
      $_FORM["showDate4"] = true;
    }

    $_FORM["people_cache"] = get_cached_table("person");
    $_FORM["timeUnit_cache"] = get_cached_table("timeUnit");

    // A header row
    $summary.= task::get_task_list_tr_header($_FORM);


    // Get a hierarchical list of tasks
    if ($_FORM["taskView"] == "byProject") {

      // If selected projects, build up an array of selected projects
      $filter["projectIDs"] and $projectIDs = " WHERE ".$filter["projectIDs"];
      $q = "SELECT projectID, projectName, clientID, projectPriority FROM project ".$projectIDs. " ORDER BY projectName";
      $debug and print "\n<br>QUERY: ".$q;
      $db = new db_alloc;
      $db->query($q);
      
      while ($project = $db->next_record()) {
        $p = new project;
        $p->read_db_record($db);
        $project["link"] = "<strong><a href=\"".$p->get_url()."\">".$p->get_value("projectName")."</a></strong>&nbsp;&nbsp;&nbsp;&nbsp;".$p->get_navigation_links();
        $projects[] = $project;
      }
    
  
      // This will add a dummy row for tasks that don't have a project
      $_FORM["projectType"] == "all" and $projects[] = array("link"=>"<strong>Miscellaneous Tasks</strong>");

      $projects or $projects = array();

      // Loop through all projects
      foreach ($projects as $project) {

        if ($project["projectID"]) {
          $filter["projectID"] = sprintf("(projectID = %d)",$project["projectID"]);
        } else {
          $filter["projectID"] = sprintf("(projectID IS NULL OR projectID = 0)");
        }

        $t = task::get_task_children($filter,$_FORM);
        $debug and print "<br/><b>Found ".count($t)." tasks.<b/>";

        if (count($t)) {
          $print = true;

          $_FORM["showProject"] and $summary.= "\n<tr>";
          $_FORM["showProject"] and $summary.= "\n  <th class=\"col tasks\" colspan=\"21\">".$project["link"]."</th>";
          $_FORM["showProject"] and $summary.= "\n</tr>";

          foreach ($t as $task) {
            $task["projectPriority"] = $project["projectPriority"];
            if ($_FORM["return"] == "objects"){
              $tasks[$task["taskID"]] = $task;
            } else if ($_FORM["return"] == "dropdown_options"){
              $summary_ops[$task["taskID"]] = str_repeat("&nbsp;&nbsp;&nbsp;",$task["padding"]).$task["taskName"];
            } else {
              $summary.= task::get_task_list_tr($task,$_FORM);
            }
          }
        }
      }


    // If they don't want a hierarichal list, get a prioritised list of tasks..
    } else if ($_FORM["taskView"] == "prioritised") {
          
      unset($filter["parentTaskID"]);
      if (is_array($filter) && count($filter)) {
        $filter = " WHERE ".implode(" AND ",$filter);
      }

      $q = "SELECT task.*, projectName, projectShortName, clientID, projectPriority, 
                   IF(task.dateTargetCompletion IS NULL, \"-\",
                     TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) as daysUntilDue,
                     priority * POWER(projectPriority, 2) * 
                       IF(task.dateTargetCompletion IS NULL, 
                         8,
                         ATAN(
                              (TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) / 20
                             ) / 3.14 * 8 + 4
                         ) / 10 as priorityFactor
              FROM task LEFT JOIN project ON task.projectID = project.projectID 
             ".$filter." ORDER BY priorityFactor ".$limit;
      $debug and print "\n<br>QUERY: ".$q;
      $db = new db_alloc;
      $db->query($q);
      while ($row = $db->next_record()) {
        $print = true;
        $row["project_name"] = $row["projectShortName"]  or  $row["project_name"] = $row["projectName"];
        $t = new task;
        $t->read_db_record($db);
        $row["taskURL"] = $t->get_url();
        $row["taskName"] = $t->get_task_name($_FORM);
        $row["taskLink"] = $t->get_task_link($_FORM);
        $row["newSubTask"] = $t->get_new_subtask_link();
        $row["taskStatus"] = $t->get_status($_FORM["return"]);
        $row["object"] = $t;
        $_FORM["showTimes"] and $row["percentComplete"] = $t->get_percentComplete();

        if ($_FORM["return"] == "objects") {
          $tasks[$row["taskID"]] = $row;

        } else if ($_FORM["return"] == "text"){
          $summary.= task::get_task_list_tr_text($row,$_FORM);

        } else {
          $summary.= task::get_task_list_tr($row,$_FORM);
        }
      }
    } 



    // Decide what to actually return
    if ($_FORM["return"] == "objects") {
      return $tasks;

    } else if ($print && $_FORM["return"] == "html") {
      return "<table class=\"tasks\" border=\"0\" cellspacing=\"0\">".$summary."</table>";

    } else if ($print && $_FORM["return"] == "text") {
      return $summary;

    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Tasks Found</b></td></tr></table>";
    } 
  } 

  function get_task_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $_FORM["showPriority"] and $summary = "\n<th class=\"col\">Priority</th>";
      $_FORM["showPriority"] and $summary.= "\n<th class=\"col\">Task Pri</th>";
      $_FORM["showPriority"] and $summary.= "\n<th class=\"col\">Proj Pri</th>";
      $_FORM["showStatus"]   and $summary.= "\n<th class=\"col\">Status</th>";
      $_FORM["showCreator"]  and $summary.= "\n<th class=\"col\">Task Creator</th>";
      $_FORM["showManager"]  and $summary.= "\n<th class=\"col\">Task Manager</th>";
      $_FORM["showAssigned"] and $summary.= "\n<th class=\"col\">Assigned To</th>";
      $_FORM["showDate1"]    and $summary.= "\n<th class=\"col\">Targ Start</th>";
      $_FORM["showDate2"]    and $summary.= "\n<th class=\"col\">Targ Compl</th>";
      $_FORM["showDate3"]    and $summary.= "\n<th class=\"col\">Act Start</th>";
      $_FORM["showDate4"]    and $summary.= "\n<th class=\"col\">Act Compl</th>";
      $_FORM["showTimes"]    and $summary.= "\n<th class=\"col\">Estimate</th>";
      $_FORM["showTimes"]    and $summary.= "\n<th class=\"col\">Actual</th>";
      $_FORM["showTimes"]    and $summary.= "\n<th class=\"col\">%</th>";

      if ($summary) {
        $rtn = "\n<tr>";
        $_FORM["taskView"] == "prioritised" && $_FORM["showProject"]
                               and $rtn.= "\n<td>&nbsp;</td>";
        $rtn.= "\n<td>&nbsp;</td>";
        $rtn.= $summary;
        $rtn.="\n</tr>";
      }

      return $rtn;
    }
  }

  function get_task_list_tr_text($task,$_FORM) {
    $summary[] = "";
    $summary[] = "";
    $summary[] = "Project: ".$task["project_name"];
    $summary[] = "Task: ".$task["taskName"];
    $summary[] = $task["taskStatus"];
    $summary[] = $task["taskURL"];
    return implode("\n",$summary);
  }

  function get_task_list_tr($task,$_FORM) {

    static $odd_even;
    $odd_even = $odd_even == "even" ? "odd" : "even";

    $today = date("Y-m-d");
    $task["dateTargetStart"]      == $today and $task["dateTargetStart"]      = "<b>".$task["dateTargetStart"]."</b>";
    $task["dateTargetCompletion"] == $today and $task["dateTargetCompletion"] = "<b>".$task["dateTargetCompletion"]."</b>";
    $task["dateActualStart"]      == $today and $task["dateActualStart"]      = "<b>".$task["dateActualStart"]."</b>";
    $task["dateActualCompletion"] == $today and $task["dateActualCompletion"] = "<b>".$task["dateActualCompletion"]."</b>";

    $people_cache = $_FORM["people_cache"];
    $timeUnit_cache = $_FORM["timeUnit_cache"];


    if ($_FORM["showDescription"] || $_FORM["showComments"]) {
      if ($task["taskDescription"]) {
        $str[] = stripslashes($task["taskDescription"]);
      }
      if ($_FORM["showComments"]) {
        $comments = util_get_comments("task",$task["taskID"]);
        if ($comments) {
          $str[] = stripslashes($comments);
        }
      }
      if (is_array($str) && count($str)) {
        $str = "<br/>".implode("<br/>",$str);
      }
    }

                                  $summary[] = "<tr class=\"".$odd_even."\">";
    $_FORM["taskView"] == "prioritised" && $_FORM["showProject"]
                              and $summary[] = "  <td class=\"col\">".$task["project_name"]."&nbsp;</td>";
                                  $summary[] = "  <td class=\"col\" style=\"padding-left:".($task["padding"]*15+3)."px\">".$task["taskLink"]."&nbsp;&nbsp;".$task["newSubTask"].$str."</td>";
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%0.2f",$task["priorityFactor"])."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%d",$task["priority"])."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%d",$task["projectPriority"])."&nbsp;</td>"; 
    $_FORM["showStatus"]      and $summary[] = "  <td class=\"col\">".$task["taskStatus"]."&nbsp;</td>"; 
    $_FORM["showCreator"]     and $summary[] = "  <td class=\"col\">".$people_cache[$task["creatorID"]]["name"]."&nbsp;</td>";
    $_FORM["showManager"]     and $summary[] = "  <td class=\"col\">".$people_cache[$task["managerID"]]["name"]."&nbsp;</td>";
    $_FORM["showAssigned"]    and $summary[] = "  <td class=\"col\">".$people_cache[$task["personID"]]["name"]."&nbsp;</td>";
    $_FORM["showDate1"]       and $summary[] = "  <td class=\"col nobr\">".$task["dateTargetStart"]."&nbsp;</td>";
    $_FORM["showDate2"]       and $summary[] = "  <td class=\"col nobr\">".$task["dateTargetCompletion"]."&nbsp;</td>";
    $_FORM["showDate3"]       and $summary[] = "  <td class=\"col nobr\">".$task["dateActualStart"]."&nbsp;</td>";
    $_FORM["showDate4"]       and $summary[] = "  <td class=\"col nobr\">".$task["dateActualCompletion"]."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col nobr\">".seconds_to_display_format($task["timeEstimate"]*60*60)."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col nobr\">".seconds_to_display_format(task::get_time_billed($task["taskID"]))."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col nobr\">".$task["percentComplete"]."&nbsp;</td>";
                                  $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
  }  

  function get_task_children($filter="",$_FORM=array()) {

    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }
    # The str_replace lets us use this same filter from above. 
    $db = new db_alloc;
    $q = sprintf("SELECT * FROM task %s ORDER BY taskName",str_replace("project.projectID","projectID",$f));
    $_FORM["debug"] and print "\n<br>QUERY: ".$q;
    $db->query($q);
    #echo "<br/>q: ".$q;

    while ($row = $db->next_record()) {

      $task = new task;
      $task->read_db_record($db);

      $row["taskURL"] = $task->get_url();
      $row["taskName"] = $task->get_task_name($_FORM);
      $row["taskLink"] = $task->get_task_link($_FORM);
      $row["newSubTask"] = $task->get_new_subtask_link();
      $row["taskStatus"] = $task->get_status();
      $_FORM["showTimes"] and $row["percentComplete"] = $task->get_percentComplete();
      $row["padding"] = $_FORM["padding"];
      $row["object"] = $task;


      if ($row["taskTypeID"] == TT_PHASE) {
        $_FORM["padding"]+=1;
        $filter["parentTaskID"] = sprintf("(parentTaskID = %d)",$row["taskID"]);
        $arr = task::get_task_children($filter, $_FORM);
        if ((is_array($arr) && count($arr)) || ($row["personID"] == $_FORM["personID"])) {
          $tasks[$row["taskID"]] = $row;
        }
        if (is_array($arr) && count($arr)) {
          $tasks = array_merge($tasks,$arr);
        }
        $_FORM["padding"]-=1;

      } else {
       $tasks[$row["taskID"]] = $row;
      }
    }

    return $tasks;
  }

  function get_new_subtask_link() {
    global $TPL;
    if (is_object($this) && $this->get_value("taskTypeID") == TT_PHASE) {
      return "<a class=\"noprint\" href=\"".$TPL["url_alloc_task"]."projectID=".$this->get_value("projectID")."&parentTaskID=".$this->get_id()."\">New Subtask</a>";
    }
  }

  function get_time_billed($taskID="", $recurse=false) {
    static $results;
    if (is_object($this) && !$taskID) {
      $taskID = $this->get_id();
    }
    if ($results[$taskID]) {
      return $results[$taskID];
    }
    if ($taskID) {
      $db = new db_alloc;
      // Get tally from timeSheetItem table
      $db->query("SELECT sum(timeSheetItemDuration*timeUnitSeconds) as sum_of_time
                    FROM timeSheetItem 
               LEFT JOIN timeUnit ON timeSheetItemDurationUnitID = timeUnitID 
                   WHERE taskID = %d
               GROUP BY taskID",$taskID);
      while ($db->next_record()) {
        $results[$taskID] = $db->f("sum_of_time");
        return $db->f("sum_of_time");
      }
      return "0.00";
    }
  }

  function get_percentComplete($get_num=false) {

    $timeActual = sprintf("%0.2f",$this->get_time_billed());
    $timeEstimate = sprintf("%0.2f",$this->get_value("timeEstimate")*60*60);

    if ($timeEstimate>0 && is_object($this)) {

      $percent = $timeActual / $timeEstimate * 100;
      $this->get_value("dateActualCompletion") and $closed_text = "<del>" and $closed_text_end = "</del> (Closed)";
 
      // Return number
      if ($get_num) {
        $this->get_value("dateActualCompletion") || $percent>100 and $percent = 100;
        return $percent;
       
      // Else if task <= 100%
      } else if ($percent <= 100) {
        return $closed_text.sprintf("%d%%",$percent).$closed_text_end;
                    
       
      // Else if task > 100%
      } else if ($percent > 100) {
        return "<span class='bad'>".$closed_text.sprintf("%d%%",$percent).$closed_text_end."</span>";
      }
    }
  }

  function get_forecast_completion() {
    // Get the date the task is forecast to be completed given an actual start 
    // date and percent complete
    $date_actual_start = $this->get_value("dateActualStart");
    $percent_complete = $this->get_percentComplete(true);

    if (!($date_actual_start && $percent_complete)) {
      // Can't calculate forecast date without date_actual_start and % complete
      return 0;
    }

    $date_actual_start = get_date_stamp($date_actual_start);
    $time_spent = mktime() - $date_actual_start;
    $time_per_percent = $time_spent / $percent_complete;
    $percent_left = 100 - $percent_complete;
    $time_left = $percent_left * $time_per_percent;
    $date_forecast_completion = mktime() + $time_left;
    return $date_forecast_completion;
  }

  function get_status($format = "html", $type = "standard") {
    $today = date("Y-m-d");
    define("UNKNOWN", 0);
    define("NOT_STARTED", 1);
    define("STARTED", 2);
    define("COMPLETED", 3);

    $date_target_start = $this->get_value("dateTargetStart");
    $date_target_completion = $this->get_value("dateTargetCompletion");
    $date_actual_start = $this->get_value("dateActualStart");
    $date_actual_completion = $this->get_value("dateActualCompletion");

    // First figure out where we should be with this task
    if ($date_target_completion != "" && $date_target_completion <= $today) {
      $target = COMPLETED;
    } else if ($date_target_start != "" && $date_target_start <= $today) {
      $target = STARTED;
    } else if ($date_target_start) {
      $target = NOT_STARTED;
    } else {
      $target = UNKNOWN;
    }

    // Now figure out where we are
    if ($date_actual_completion) {
      $actual = COMPLETED;
    } else if ($date_actual_start) {
      $actual = STARTED;
    } else {
      $actual = NOT_STARTED;
    }

    // Now compare the target and the actual and provide the results
    if ($actual == COMPLETED) {
      if ($type != "brief") {
        $status = "Completed on ".$date_actual_completion;
      } else {
        $status = "Completed";
      }
    } else if ($actual == STARTED) {
      $date_forecast_completion = $this->get_forecast_completion();

      $status = "Started ".$date_actual_start.", ";

      #if ($type != "brief") {
      #  if ($percent_complete == "") {
      #    $status.= "% complete not set, ";
      #  } else {
      #    $status.= "$percent_complete% complete, ";
      #  }
      #}

      if ($date_target_completion != "") {
        $status.= "Target completion $date_target_completion ";
      } else {
    
      }

      if ($type != "brief") {
        if ($date_forecast_completion == 0) {
          $status.= "forecast completion date not available";
        } else {
          $status.= "forecast completion date of	".date("Y-m-d", $date_forecast_completion);
        }
      }

      if ($target == COMPLETED) {
        if ($type == "brief") {
          $status = "Overdue for completion on ".$date_target_completion;
        } else {
          $status = "Overdue for completion - $status";
        }
        if ($format == "html") {
          $status = "<strong class=\"overdue\">$status</strong>";
        }
      } else if ($date_target_completion && date("Y-m-d", $date_forecast_completion) > $date_target_completion) {
        $status = "Behind target - $status";
        if ($format == "html") {
          $status = "<strong class=\"behind-target\">$status</strong>";
        }
      }

    // New one
    } else if ($actual == NOT_STARTED && $target == UNKNOWN) {
      if ($target_completion_date) {
        $status = "Not started, due to be completed by $target_completion_date, no target start date";
      } else {
        $status = "Not started, no targets";
      }
    } else if ($actual == NOT_STARTED && $target == NOT_STARTED) {
      $status = "Due to start on ".$date_target_start;
      if ($date_target_completion) {
        $status.= " and to be completed by ".$date_target_completion;
      } else {
        $status.= ", no target completion date";
      }
    } else if ($actual == NOT_STARTED && $target == STARTED) {
      $status = "Overdue to start on ".$date_target_start;
      if ($format == "html") {
        $status = "<strong class=\"behind-target\">$status</strong>";
      }
    } else if ($actual == NOT_STARTED && $target == COMPLETED) {
      $status = "Overdue to start and be completed by $date_target_completion";
      if ($format == "html") {
        $status = "<strong class=\"overdue\">$status</strong>";
      }
    } else {
      $status = "Unexpected target/actual combination: $target/$actual";
    }

    // $status .= " ($target/$actual)";
    return $status;
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array("projectID"
        ,"taskStatus"
        ,"taskTypeID"
        ,"personID"
        ,"creatorID"
        ,"managerID"
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
        ,"showTaskID"
        ,"showManager"
        ,"showHeader"
        ,"showProject"
        ,"padding"
        ,"url_form_action"
        ,"form_name"
        ,"dontSave"
        );

    $_FORM = get_all_form_data($page_vars,$defaults);

    if ($_FORM["projectID"] && !is_array($_FORM["projectID"])) {
      $p = $_FORM["projectID"];
      unset($_FORM["projectID"]);
      $_FORM["projectID"][] = $p;

    } else if (!$_FORM["projectID"] && $_FORM["projectType"]) {
      $q = project::get_project_type_query($_FORM["projectType"]);
      $db = new db_alloc;
      $db->query($q);
      while($row = $db->row()) {
        $_FORM["projectID"][] = $row["projectID"];
      }

    } else if (!$_FORM["projectType"]){
      $_FORM["projectType"] = "mine";
    }

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["projectType"] = "mine";
        $_FORM["taskStatus"] = "not_completed";
        $_FORM["personID"] = $current_user->get_id();
      }

    } else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
      $url = $_FORM["url_form_action"];
      unset($_FORM["url_form_action"]);
      $current_user->prefs[$_FORM["form_name"]] = $_FORM;
      $_FORM["url_form_action"] = $url;
    }

    // If have check Show Description checkbox then display the Long Description and the Comments
    if ($_FORM["showDescription"]) {
      $_FORM["showComments"] = true;
    } else {
      unset($_FORM["showComments"]);
    }
    $_FORM["taskView"] or $_FORM["taskView"] = "byProject";
    return $_FORM;
  }

  function load_task_filter($_FORM) {

    $db = new db_alloc;

    // Load up the forms action url
    $rtn["url_form_action"] = $_FORM["url_form_action"];

    // Load up the filter bits
    $rtn["projectOptions"] = project::get_project_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);

    $_FORM["projectType"] and $rtn["projectType_checked_".$_FORM["projectType"]] = " checked"; 

    $rtn["personOptions"] = "\n<option value=\"\"> ";
    $rtn["personOptions"].= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

    $rtn["creatorPersonOptions"] = "\n<option value=\"\"> ";
    $rtn["creatorPersonOptions"].= get_select_options(person::get_username_list($_FORM["creatorID"]), $_FORM["creatorID"]);

    $rtn["managerPersonOptions"] = "\n<option value=\"\"> ";
    $rtn["managerPersonOptions"].= get_select_options(person::get_username_list($_FORM["managerID"]), $_FORM["managerID"]);

    $taskType = new taskType;
    $rtn["taskTypeOptions"] = "\n<option value=\"\"> ";
    $rtn["taskTypeOptions"].= $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);


    $_FORM["taskView"] and $rtn["taskView_checked_".$_FORM["taskView"]] = " checked";

    $taskStatii = task::get_task_statii_array();
    $rtn["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);

    $_FORM["showDescription"] and $rtn["showDescription_checked"] = " checked";
    $_FORM["showDates"]       and $rtn["showDates_checked"]       = " checked";
    $_FORM["showCreator"]     and $rtn["showCreator_checked"]     = " checked";
    $_FORM["showAssigned"]    and $rtn["showAssigned_checked"]    = " checked";
    $_FORM["showTimes"]       and $rtn["showTimes_checked"]       = " checked";
    $_FORM["showPercent"]     and $rtn["showPercent_checked"]     = " checked";
    $_FORM["showPriority"]    and $rtn["showPriority_checked"]    = " checked";
    $_FORM["showStatus"]      and $rtn["showStatus_checked"]      = " checked";
    $_FORM["showTaskID"]      and $rtn["showTaskID_checked"]      = " checked";
    $_FORM["showManager"]     and $rtn["showManager_checked"]     = " checked";
    
    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function add_comment_from_email($email) {

    $comment = new comment;
    $comment->set_value("commentType","task");
    $comment->set_value("commentLinkID",$this->get_id());
    $comment->save();
    $commentID = $comment->get_id();

    $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$comment->get_id();
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }
    $file = $dir.DIRECTORY_SEPARATOR."mail.eml";
    $decoded = $email->save_email($file);
    
    $email_bits = explode(" ",$decoded[0]["Headers"]["from:"]);
    $from_address = str_replace(array("<",">"),"",array_pop($email_bits));
    is_array($email_bits) and $from_person = implode(" ",$email_bits);
    
    $person = new person;
    $personID = $person->find_by_name($from_person);
  
    #die("personID ".$personID);

    if (!$personID) {
      $personID = $person->find_by_email($from_address);
    }

    if ($personID) {
      $comment->set_value('commentCreatedUser', $personID);

    } else {
      $cc = new clientContact();
      $clientContactID = $cc->find_by_name($from_person, $this->get_value("projectID"));

      if (!$clientContactID) {
        $clientContactID = $cc->find_by_email($from_address, $this->get_value("projectID"));
      }

      if ($clientContactID) {
        $comment->set_value('commentCreatedUserClientContactID', $clientContactID);
      }
    }
    // Don't update last modified fields...
    $comment->skip_modified_fields = true;

    $body = trim(mime_parser::get_body_text($decoded));
    $comment->set_value("comment",trim($body));
    $comment->set_value("commentCreatedUserText",trim($decoded[0]["Headers"]["from:"]));
    $comment->save();

    $recipients[] = "assignee";
    $recipients[] = "manager";
    $recipients[] = "creator";
    $recipients[] = "CCList";
    $successful_recipients = $this->send_emails($recipients,"task_comments",$body,$from_person);

    if ($successful_recipients) {
      $comment->set_value("commentEmailRecipients",$successful_recipients);
      $comment->save();
    }
  }

}


?>
