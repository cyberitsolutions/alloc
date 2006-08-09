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

// Task types
define("TT_TASK", 1);
define("TT_PHASE", 2);
define("TT_MESSAGE", 3);
define("TT_FAULT", 4);
define("TT_MILESTONE", 5);
define("PERM_PROJECT_READ_TASK_DETAIL", 256);
$default_task_options = array("show_links"=>true);

class task extends db_entity {
  var $classname = "task";
  var $data_table = "task";
  var $fire_events = true;
  var $display_field_name = "taskName";

  function task() {
    global $current_user;

      $this->db_entity();       // Call constructor of parent class
      $this->key_field = new db_text_field("taskID");
      $this->data_fields = array("taskName"=>new db_text_field("taskName", "Name", "", array("allow_null"=>false))
                                 , "taskDescription"=>new db_text_field("taskDescription")
                                 , "creatorID"=>new db_text_field("creatorID")
                                 , "closerID"=>new db_text_field("closerID")
                                 , "priority"=>new db_text_field("priority")
                                 , "timeEstimate"=>new db_text_field("timeEstimate", "Time Estimate", 0, array("empty_to_null"=>true))
                                 , "dateCreated"=>new db_text_field("dateCreated")
                                 , "dateAssigned"=>new db_text_field("dateAssigned")
                                 , "dateClosed"=>new db_text_field("dateClosed")
                                 , "dateTargetStart"=>new db_text_field("dateTargetStart")
                                 , "dateTargetCompletion"=>new db_text_field("dateTargetCompletion")
                                 , "dateActualStart"=>new db_text_field("dateActualStart")
                                 , "dateActualCompletion"=>new db_text_field("dateActualCompletion")
                                 , "taskComments"=>new db_text_field("taskComments")
                                 , "projectID"=>new db_text_field("projectID")
                                 , "parentTaskID"=>new db_text_field("parentTaskID")
                                 , "taskTypeID"=>new db_text_field("taskTypeID")
                                 , "personID"=>new db_text_field("personID")
                                 
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

  function email_task_closed() {
    global $current_user;
    if ($current_user->get_id() != $this->get_value("creatorID")) {
      $successful_recipients = $this->send_emails(array("creator"),$this,"Task Closed");
      $successful_recipients and $msg = "Emailed: ".stripslashes($successful_recipients).", Task Closed: ".stripslashes($this->get_value("taskName"));
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
    $reminder->set_value('reminderSubject', $this->get_value("taskName"));
    $reminder->set_value('reminderContent', "\nReminder Created By: ".$current_user->get_display_value()
                         ."\n\n".$message."\n\n".$this->get_value("taskDescription"));

    $reminder->set_value('reminderAdvNoticeSent', "0");
    $reminder->set_value('reminderAdvNoticeInterval', "No");
    $reminder->set_value('reminderAdvNoticeValue', "0");

    $reminder->set_value('reminderModifiedTime', date("Y-m-d H:i:s"));
    $reminder->set_value('reminderModifiedUser', $current_user->get_display_value());
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
    if ($projectID) {
      $query = sprintf("SELECT * 
                        FROM task 
                        WHERE projectID= '%d' 
                        AND taskTypeID = 2 
                        AND (dateActualCompletion IS NULL or dateActualCompletion = '') 
                        ORDER BY taskName", $projectID);
      $db->query($query);
      $options = get_option("None", "0");
      $options.= get_options_from_db($db, "taskName", "taskID", $parentTaskID,70);
    }
    return "<select name=\"parentTaskID\">".$options."</select>";
  }

  function get_task_cc_list_select($projectID="") {
    global $TPL;
    $db = new db_alloc;
    
    if (is_object($this)) {
      $projectID = $this->get_value("projectID") or $projectID = $_GET["projectID"];
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
      $taskCCListOptions = array();

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



    if (is_array($taskCCListOptions)) {
      foreach ($taskCCListOptions as $email => $name) {
        if ($email) {
          $str = trim(htmlentities($name." <".$email.">"));
          $options[urlencode(base64_encode(serialize(array("name"=>sprintf("%s",$name),"email"=>$email))))] = $str;
        }
      }
    }
    $str = "<select name=\"taskCCList[]\" size=\"5\" multiple=\"true\"  style=\"width:300px\">".get_select_options($options,$taskCCList)."</select>";
    return $str;
  }

  function set_option_tpl_values() {
    // Set template values to provide options for edit selects
    global $TPL, $current_user, $isMessage;

    $db = new db_alloc;

    if ($_GET["timeSheetID"]) {
      $ts_query = sprintf("select * from timeSheet where timeSheetID = %d",$_GET["timeSheetID"]);
      $db->query($ts_query);
      $db->next_record();
      $owner = $db->f("personID");
      $projectID = $db->f("projectID");
    } else if ($this->get_value("personID")) {
      $owner = $this->get_value("personID");
    } else if (!$this->get_id()) {
      $owner = $current_user->get_id();
    }

    $TPL["personOptions"] = get_option("None", "0", $owner == 0)."\n";
    $TPL["personOptions"].= get_select_options(person::get_username_list($owner), $owner);


    $projectID = $_GET["projectID"] or $projectID = $this->get_value("projectID");

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
    $TPL["priorityOptions"] = get_select_options(array(1=>"Priority 1", 2=>"Priority 2", 3=>"Priority 3", 4=>"Priority 4", 5=>"Priority 5"), $priority);

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
    global $view;
    if ($view == "printer") {
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
    static $people;
    $recipients = array();

    // Load up all people into array
    if (!$people) { 
      $db = new db_alloc;
      $db->query("SELECT personID, username, firstName, surname, emailAddress FROM person");
      while($db->next_record()) {
        if ($db->f("firstName") && $db->f("surname")) {
          $db->Record["fullName"] = $db->f("firstName")." ".$db->f("surname");
        } else {
          $db->Record["fullName"] = $db->f("username");
        }
        $people[$db->f("personID")] = $db->Record;
      }
    }


    foreach ($options as $selected_option) {

      // Determine recipient/s 

      if ($selected_option == "CCList") {
        $db = new db_alloc;
        $q = sprintf("SELECT * FROM taskCCList WHERE taskID = %d",$this->get_id()); 
        $db->query($q);
        while($db->next_record()) {
          $recipients[] = $db->Record;
        }
      } else if ($selected_option == "creator") {
        $recipients[] = $people[$this->get_value("creatorID")];

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
      }
    }
    return $recipients;
  }

  function send_emails($selected_option, $object, $extra="") {
    global $current_user;
    $recipients = $this->get_email_recipients($selected_option);

    $extra or $extra = "Task";
    $subject = $extra.": ".$this->get_id()." ".$this->get_value("taskName");

    foreach ($recipients as $recipient) {
      if ($object->send_email($recipient, $subject)) {
        $successful_recipients.= $commar.$recipient["fullName"];
        $commar = ", ";
      }
    }
    return $successful_recipients;
  }

  function send_email($recipient, $subject) {
    global $current_user;

    // New email object wrapper takes care of logging etc.
    $email = new alloc_email;
    $email->set_from($current_user->get_id());

    // REMOVE ME!!
    $email->ignore_no_email_urls = true;

    $p = new project;
    $p->set_id($this->get_value("projectID"));
    $p->select();
    $body = "Project: ".stripslashes($p->get_value("projectName"));
    $body.= "\nTask: ".stripslashes($this->get_value("taskName"));
    $body.= "\n".config::get_config_item("allocURL")."project/task.php?taskID=".$this->get_id();
    $body.= "\n\n".stripslashes(wordwrap($this->get_value("taskDescription")));

    $message = "\n".wordwrap($body);

    // Convert plain old recipient address blah@cyber.com.au to Alex Lance <blah@cyber.com.au>
    if ($recipient["firstName"] && $recipient["surname"] && $recipient["emailAddress"]) {
      $recipient["emailAddress"] = $recipient["firstName"]." ".$recipient["surname"]." <".$recipient["emailAddress"].">";
    } else if ($recipient["fullName"] && $recipient["emailAddress"]) {
      $recipient["emailAddress"] = $recipient["fullName"]." <".$recipient["emailAddress"].">";
    }

    if ($recipient["emailAddress"]) {
      return $email->send($recipient["emailAddress"], $subject, $message);
    }
  }

  function get_task_link($_FORM=array()) {
    $rtn = "<a href=\"".$this->get_url()."\">";
    $_FORM["showTaskID"] and $rtn.= $this->get_id()." ";
    $rtn.= $this->get_task_name();
    $rtn.= "</a>";
    return $rtn;
  }

  function get_task_name($format="html") {
    if ($this->get_value("taskTypeID") == TT_PHASE && $format == "html") {
      $rtn = "<strong>".stripslashes($this->get_value("taskName"))."</strong>";
    } else if ($this->get_value("taskTypeID") == TT_PHASE) {
      $rtn = stripslashes($this->get_value("taskName"));
    } else {
      substr($this->get_value("taskName"),0,140) != $this->get_value("taskName") and $dotdotdot = "...";
      $rtn = substr(stripslashes($this->get_value("taskName")),0,140).$dotdotdot;
    }
    return $rtn;
  }

  function get_url() {
    $sess = Session::GetSession();
    $url = "project/task.php?taskID=".$this->get_id();

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

  function get_task_list_filter($_FORM=array()) {


    if (!$_FORM["projectID"] && $_FORM["projectType"] && $_FORM["projectType"] != "all") {
      $db = new db_alloc;
      $q = project::get_project_type_query($_FORM["projectType"]);
      $db->query($q);
      while ($db->next_record()) {
        $_FORM["projectIDs"][] = $db->f("projectID");
      }

      // Oi! What a pickle. Need this flag for when someone doesn't have entries loaded in the above while loop.
      $firstOption = true;

    // If projectID is an array
    } else if ($_FORM["projectID"] && is_array($_FORM["projectID"])) {
      $_FORM["projectIDs"] = $_FORM["projectID"];

    // Else a project has been specified in the url
    } else if ($_FORM["projectID"] && is_numeric($_FORM["projectID"])) {
      $_FORM["projectIDs"][] = $_FORM["projectID"];
    }


    // If passed array projectIDs then join them up with commars and put them in an sql subset
    if (is_array($_FORM["projectIDs"]) && count($_FORM["projectIDs"])) {
      $filter["projectIDs"] = "(project.projectID IN (".implode(",",$_FORM["projectIDs"])."))";

    // If there are no projects in $_FORM["projectIDs"][] and we're attempting the first option..
    } else if ($firstOption) {
      $filter["projectIDs"] = "(project.projectID IN (0))";
    }

    // Task level filtering
    if ($_FORM["taskStatus"]) {
      $taskStatusFilter = task::get_task_statii();
      $filter[] = $taskStatusFilter[$_FORM["taskStatus"]]["sql"];
    }

    // Unset if they've only selected the topmost empty task type
    if (is_array($_FORM["taskTypeID"]) && count($_FORM["taskTypeID"])>=1 && !$_FORM["taskTypeID"][0]) {
      unset($_FORM["taskTypeID"][0]);
    }

    // If many create an SQL taskTypeID in (set) 
    if (is_array($_FORM["taskTypeID"]) && count($_FORM["taskTypeID"])) {
      $filter[] = "(taskTypeID in (".implode(",",$_FORM["taskTypeID"])."))";
    
    // Else if only one taskTypeID
    } else if ($_FORM["taskTypeID"]) {
      $filter[] = sprintf("(taskTypeID = %d)",$_FORM["taskTypeID"]);
    }

    // If personID filter and the view is byProject, then allow unassigned phases into the results 
    if ($_FORM["personID"] && $_FORM["taskView"] == "byProject") {
      $filter[] = sprintf("(personID = %d or (taskTypeID = %d and (personID IS NULL or personID = '' or personID = 0)))",$_FORM["personID"],TT_PHASE);
    
    // If personID filter and the view is prioritised, then do a strict by personID query
    } else if ($_FORM["personID"] && $_FORM["taskView"] == "prioritised") {
      $filter[] = sprintf("(personID = %d)",$_FORM["personID"]);
    }

    // This will be zero if not set. Which is fine since all top level tasks have a parentID of zero
    // This filter is unset for returning a prioritised list of tasks.
    $filter["parentTaskID"] = sprintf("(parentTaskID = %d)",$_FORM["parentTaskID"]);

    return $filter;
  }

  function get_task_list($_FORM) {

    /*
     * This is the definitive method of getting a list of tasks
     *
     # Display Options:
     *   showDates        = Show dates 1-4
     *   showDate1        = Date Target Start
     *   showDate2        = Date Target Completion
     *   showDate3        = Date Actual Start
     *   showDate4        = Date Actual Completion
     *   showProject      = The tasks Project (has different layout when prioritised vs byProject)
     *   showPriority     = The calculated overall priority, then the tasks, then the projects priority
     *   showStatus       = A colour coded textual description of the status of the task
     *   showCreator      = The tasks creator
     *   showAssigned     = The person assigned to the task
     *   showTimes        = The original estimate and the time billed and percentage
     *   showHeader       = A descriptive header row
     *   showDescription  = The tasks description
     *
     *
     * Filter Options:
     *   taskView         = byProject | prioritised
     *   return           = html | text | objects
     *   limit            = appends an SQL limit (only for prioritised and objects views)
     *   projectIDs       = an array of projectIDs
     *   taskStatus       = completed | not_completed | in_progress | due_today | new | overdue
     *   taskTypeID       = the task type
     *   personID         = person assigned 
     *   parentTaskID     = id of parent task, all top level tasks have parentTaskID of 0, so this defaults to 0
     *  
     *
     * Other:
     *   padding          = Initial indentation level (useful for byProject lists)
     *
     */
  
    
    $filter = task::get_task_list_filter($_FORM);


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
    if ($_FORM["showHeader"]) {

      $summary.= "\n<tr>";
      $_FORM["taskView"] == "prioritised" && $_FORM["showProject"]
                             and $summary.= "\n<td>&nbsp;</td>";
      $summary.= "\n<td>&nbsp;</td>";
      $_FORM["showPriority"] and $summary.= "\n<td class=\"col\"><b><nobr>Priority</nobr></b></td>"; 
      $_FORM["showPriority"] and $summary.= "\n<td class=\"col\"><b><nobr>Task Pri</nobr></b></td>"; 
      $_FORM["showPriority"] and $summary.= "\n<td class=\"col\"><b><nobr>Proj Pri</nobr></b></td>"; 
      $_FORM["showStatus"]   and $summary.= "\n<td class=\"col\"><b><nobr>Status</nobr></b></td>"; 
      $_FORM["showCreator"]  and $summary.= "\n<td class=\"col\"><b><nobr>Task Creator</nobr></b></td>";
      $_FORM["showAssigned"] and $summary.= "\n<td class=\"col\"><b><nobr>Assigned To</nobr></b></td>";
      $_FORM["showDate1"]    and $summary.= "\n<td class=\"col\"><b><nobr>Targ Start</nobr></b></td>";
      $_FORM["showDate2"]    and $summary.= "\n<td class=\"col\"><b><nobr>Targ Compl</nobr></b></td>";
      $_FORM["showDate3"]    and $summary.= "\n<td class=\"col\"><b><nobr>Act Start</nobr></b></td>";
      $_FORM["showDate4"]    and $summary.= "\n<td class=\"col\"><b><nobr>Act Compl</nobr></b></td>";
      $_FORM["showTimes"]    and $summary.= "\n<td class=\"col\"><b><nobr>Estimate</nobr></b></td>";
      $_FORM["showTimes"]    and $summary.= "\n<td class=\"col\"><b><nobr>Actual</nobr></b></td>";
      $_FORM["showTimes"]    and $summary.= "\n<td class=\"col\"><b><nobr>%</nobr></b></td>";
      $summary.="\n</tr>";
    }

    // Get a hierarchical list of tasks
    if ($_FORM["taskView"] == "byProject") {


      // If selected projects, build up an array of selected projects
      $filter["projectIDs"] and $projectIDs = " WHERE ".$filter["projectIDs"];
      $q = "SELECT projectID, projectName, clientID, projectPriority FROM project ".$projectIDs. " ORDER BY projectName";
      $db = new db_alloc;
      $db->query($q);
      
      while ($project = $db->next_record()) {
        $p = new project;
        $p->read_db_record($db);
        $project["link"] = "<strong><a href=\"".$p->get_url()."\">".$p->get_value("projectName")."</a></strong>&nbsp;&nbsp;".$p->get_navigation_links();
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

        $tasks = task::get_task_children($filter,$_FORM);

        if (count($tasks)) {
          $print = true;

          $_FORM["showProject"] and $summary.= "\n<tr>";
          $_FORM["showProject"] and $summary.= "\n  <td class=\"tasks\" colspan=\"21\">".$project["link"]."</td>";
          $_FORM["showProject"] and $summary.= "\n</tr>";

          foreach ($tasks as $task) {
            $task["projectPriority"] = $project["projectPriority"];
            $summary.= task::get_task_list_tr($task,$_FORM);
          }
          $summary.= "<td class=\"col\" colspan=\"21\">&nbsp;</td>";
        }
      }

      
      

    // Get a prioritised list of tasks
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
      $db = new db_alloc;
      $db->query($q);
      while ($row = $db->next_record()) {
        $print = true;
        $row["project_name"] = $row["projectShortName"]  or  $row["project_name"] = $row["projectName"];
        $t = new task;
        $t->read_db_record($db);
        $row["taskURL"] = $t->get_url();
        $row["taskName"] = $t->get_task_name($_FORM["return"]);
        $row["taskLink"] = $t->get_task_link($_FORM);
        $row["newSubTask"] = $t->get_new_subtask_link();
        $row["taskStatus"] = $t->get_status($_FORM["return"]);
        $_FORM["showTimes"] and $row["percentComplete"] = $t->get_percentComplete();

        if ($_FORM["return"] == "objects") {
          $tasks[$t->get_id()] = $t; 

        } else if ($_FORM["return"] == "text"){
          $summary.= task::get_task_list_tr_text($row,$_FORM);

        } else {
          $summary.= task::get_task_list_tr($row,$_FORM);
        }
      }
    } 

    if ($_FORM["taskView"] == "prioritised" && $_FORM["return"] == "objects") {
      return $tasks;

    } else if ($print && $_FORM["return"] == "html") {
      return "<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\">".$summary."</table>";

    } else if ($print && $_FORM["return"] == "text") {
      return $summary;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table align=\"center\"><tr><td colspan=\"10\" align=\"center\"><b>No Tasks Found</b></td></tr></table>";
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

    $today = date("Y-m-d");
    $task["dateTargetStart"]      == $today and $task["dateTargetStart"]      = "<b>".$task["dateTargetStart"]."</b>";
    $task["dateTargetCompletion"] == $today and $task["dateTargetCompletion"] = "<b>".$task["dateTargetCompletion"]."</b>";
    $task["dateActualStart"]      == $today and $task["dateActualStart"]      = "<b>".$task["dateActualStart"]."</b>";
    $task["dateActualCompletion"] == $today and $task["dateActualCompletion"] = "<b>".$task["dateActualCompletion"]."</b>";

    $people_cache = $_FORM["people_cache"];
    $timeUnit_cache = $_FORM["timeUnit_cache"];

    $estime = seconds_to_display_format($task["timeEstimate"]*60*60); 
    $actual = seconds_to_display_format(task::get_time_billed($task["taskID"])); 

                                  $summary[] = "<tr>";
    $_FORM["taskView"] == "prioritised" && $_FORM["showProject"]
                              and $summary[] = "  <td class=\"col\">".$task["project_name"]."&nbsp;</td>";
                                  $summary[] = "  <td class=\"col\" style=\"padding-left:".($task["padding"]*15+3)."\">".$task["taskLink"]."&nbsp;&nbsp;".$task["newSubTask"]."</td>";
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%0.2f",$task["priorityFactor"])."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%d",$task["priority"])."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td class=\"col\">".sprintf("%d",$task["projectPriority"])."&nbsp;</td>"; 
    $_FORM["showStatus"]      and $summary[] = "  <td class=\"col\">".$task["taskStatus"]."&nbsp;</td>"; 
    $_FORM["showCreator"]     and $summary[] = "  <td class=\"col\">".$people_cache[$task["creatorID"]]["name"]."&nbsp;</td>";
    $_FORM["showAssigned"]    and $summary[] = "  <td class=\"col\">".$people_cache[$task["personID"]]["name"]."&nbsp;</td>";
    $_FORM["showDate1"]       and $summary[] = "  <td class=\"col\"><nobr>".$task["dateTargetStart"]."&nbsp;</nobr></td>";
    $_FORM["showDate2"]       and $summary[] = "  <td class=\"col\"><nobr>".$task["dateTargetCompletion"]."&nbsp;</nobr></td>";
    $_FORM["showDate3"]       and $summary[] = "  <td class=\"col\"><nobr>".$task["dateActualStart"]."&nbsp;</nobr></td>";
    $_FORM["showDate4"]       and $summary[] = "  <td class=\"col\"><nobr>".$task["dateActualCompletion"]."&nbsp;</nobr></td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col\"><nobr>".$estime."&nbsp;</nobr></td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col\"><nobr>".$actual."&nbsp;</nobr></td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"col\"><nobr>".$task["percentComplete"]."&nbsp;</nobr></td>";
                                  $summary[] = "</tr>";

    if ($_FORM["showDescription"] && $task["taskDescription"]) {
                                  $summary[] = "<tr>";
       $_FORM["taskView"] == "prioritised" && $_FORM["showProject"]
                              and $summary[] = "  <td class=\"col\">&nbsp;</td>";
                                  $summary[] = "  <td style=\"padding-left:".($task["padding"]*15+4)."\" colspan=\"21\" class=\"col\">".$task["taskDescription"]."</td>";
                                  $summary[] = "</tr>";
    }

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
    $db->query($q);

    while ($row = $db->next_record()) {

      $task = new task;
      $task->read_db_record($db);

      $row["taskURL"] = $task->get_url();
      $row["taskName"] = $task->get_task_name();
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
      return "<a href=\"".$TPL["url_alloc_task"]."projectID=".$this->get_value("projectID")."&parentTaskID=".$this->get_id()."\">New Subtask</a>";
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


}


?>
