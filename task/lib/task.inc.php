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
                                 , "duplicateTaskID"=>new db_field("duplicateTaskID")
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

    $label = $this->get_priority_label();

    $reminder = new reminder;
    $reminder->set_value('reminderType', "task");
    $reminder->set_value('reminderLinkID', $this->get_id());
    $reminder->set_value('reminderRecuringInterval', $reminderInterval);
    $reminder->set_value('reminderRecuringValue', $intervalValue);
    $reminder->set_value('reminderSubject', "Task Reminder: ".$this->get_id()." ".$this->get_value("taskName")." [".$label."]");
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
    $interestedParty = array();
    $interestedPartyOptions = array();
    
    if (is_object($this)) {
      $interestedPartyOptions = $this->get_all_task_parties($projectID);
    } else {
      $interestedPartyOptions = task::get_all_task_parties($projectID);
    }

    #echo "<pre>".print_r($interestedPartyOptions,1)."</pre>";
  
    if (is_array($interestedPartyOptions)) {

      foreach ($interestedPartyOptions as $email => $info) {
        $name = $info["name"];
        $identifier = $info["identifier"];

        if ($info["role"] == "interested" && $info["selected"]) {
          $interestedParty[] = $identifier;
        }

        if ($email) {
          $name = trim($name);
          $str = trim(htmlentities($name." <".$email.">"));
          $options[$identifier] = $str;
        }
      }
    }
    $str = "<select name=\"interestedParty[]\" size=\"8\" multiple=\"true\"  style=\"width:300px\">".get_select_options($options,$interestedParty)."</select>";
    return $str;
  }

  function get_all_task_parties($projectID="") {
    $db = new db_alloc;
    $interestedPartyOptions = array();
  
    if ($_GET["projectID"]) {
      $projectID = $_GET["projectID"];
    } else if (!$projectID && is_object($this)) {
      $projectID = $this->get_value("projectID");
    }

    if ($projectID) {
      // Get primary client contact from Project page
      $q = sprintf("SELECT projectClientName,projectClientEMail FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $interestedPartyOptions[$db->f("projectClientEMail")] = array("name"=>$db->f("projectClientName"),"external"=>1);
  
      // Get all other client contacts from the Client pages for this Project
      $q = sprintf("SELECT clientID FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $clientID = $db->f("clientID");
      $q = sprintf("SELECT clientContactName, clientContactEmail, clientContactID 
                      FROM clientContact 
                     WHERE clientID = %d",$clientID);
      $db->query($q);
      while ($db->next_record()) {
        $interestedPartyOptions[$db->f("clientContactEmail")] = array("name"=>$db->f("clientContactName"),"external"=>1,"clientContactID"=>$db->f("clientContactID"));
      }

      // Get all the project people for this tasks project
      $q = sprintf("SELECT emailAddress, firstName, surname, person.personID
                     FROM projectPerson 
                LEFT JOIN person on projectPerson.personID = person.personID 
                    WHERE projectPerson.projectID = %d AND person.personActive = 1 ",$projectID);
      $db->query($q);
      while ($db->next_record()) {
        $interestedPartyOptions[$db->f("emailAddress")] = array("name"=>$db->f("firstName")." ".$db->f("surname"),"personID"=>$db->f("personID"));
      }
    }

    $extra_interested_parties = config::get_config_item("defaultInterestedParties") or $extra_interested_parties=array();
    foreach ($extra_interested_parties as $name => $email) {
      $interestedPartyOptions[$email] = array("name"=>$name);
    }

    if (is_object($this)) {
      if ($this->get_value("creatorID")) {
        $p = new person;
        $p->set_id($this->get_value("creatorID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"creator", "personID"=>$this->get_value("creatorID"));
      }
      if ($this->get_value("personID")) {
        $p = new person;
        $p->set_id($this->get_value("personID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"assignee", "selected"=>true, "personID"=>$this->get_value("personID"));
      }
      if ($this->get_value("managerID")) {
        $p = new person;
        $p->set_id($this->get_value("managerID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"manager", "selected"=>true, "personID"=>$this->get_value("managerID"));
      }
      $this_id = $this->get_id();
    }
    // return an aggregation of the current task/proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("task",$this_id,$interestedPartyOptions);
    return $interestedPartyOptions;
  }

  function get_encoded_interested_party_identifier($info=array()) {
    return urlencode(base64_encode(serialize($info)));
  }

  function get_decoded_interested_party_identifier($blob) {
    return unserialize(base64_decode(urldecode($blob)));
  }

  function get_personList_dropdown($projectID,$taskID=false, $formName="personID") {
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
   
    $str = '<select name="'. $formName . '">';
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
    $query = sprintf("SELECT * FROM project WHERE projectStatus IN ('current', 'potential') ORDER BY projectName");
    $db->query($query);
    $TPL["projectOptions"] = get_option("None", "0", $projectID == 0)."\n";
    $TPL["projectOptions"].= get_options_from_db($db, "projectName", "projectID", $projectID,60);
    
    $commentTemplate = new commentTemplate();
    $TPL["commentTemplateOptions"] = $commentTemplate->get_dropdown_options("commentTemplateID","commentTemplateName","","Comment Templates");

    $priority = $this->get_value("priority") or $priority = 3;
    $taskPriorities = config::get_config_item("taskPriorities") or $taskPriorities = array();
    foreach ($taskPriorities as $k => $v) {
      $tp[$k] = $v["label"];
    }
    $TPL["priorityOptions"] = get_select_options($tp,$priority);
    $priority and $TPL["priorityLabel"] = " <div style=\"display:inline; color:".$taskPriorities[$priority]["colour"]."\">[".$this->get_priority_label()."]</div>";

    // We're building these two with the <select> tags because they will be
    // replaced by an AJAX created dropdown when the projectID changes.
    $TPL["parentTaskOptions"] = $this->get_parent_task_select();
    $TPL["interestedPartyOptions"] = $this->get_task_cc_list_select();

    $db->query(sprintf("SELECT fullName,emailAddress FROM interestedParty WHERE entity='task' AND entityID = %d ORDER BY fullName",$this->get_id()));
    while ($db->next_record()) {
      $str = trim(htmlentities($db->f("fullName")." <".$db->f("emailAddress").">"));
      $value = interestedParty::get_encoded_interested_party_identifier($db->f("fullName"), $db->f("emailAddress"));
      $TPL["interestedParty_hidden"].= $commar.$str."<input type=\"hidden\" name=\"interestedParty[]\" value=\"".$value."\">";
      $TPL["interestedParty_text"].= $commar.$str;
      $commar = "<br/>";
    }

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

  function get_task_comments_array() {
    $rows = comment::util_get_comments_array("task",$this->get_id());
    $rows or $rows = array();
    return $rows;
  }

  function get_task_link($_FORM=array()) {
    $rtn = "<a href=\"".$this->get_url()."\">";
    $rtn.= $this->get_task_name($_FORM);
    $rtn.= "</a>";
    return $rtn;
  }

  function get_task_image() {
    global $TPL;
    $taskTypes = get_cached_table("taskType");
    return "<img class=\"taskType\" title=\"".$taskTypes[$this->get_value("taskTypeID")]["taskTypeName"]."\" src=\"".$TPL["url_alloc_images"]."taskType_".$this->get_value("taskTypeID").".gif\">";
  }

  function get_task_name($_FORM=array()) {

    $_FORM["prefixTaskID"] and $id = $this->get_id()." ";

    if ($this->get_value("taskTypeID") == TT_PHASE && ($_FORM["return"] == "html" || $_FORM["return"] == "objectsAndHtml")) {
      $rtn = "<strong>".$id.$this->get_value("taskName")."</strong>";
    } else if ($this->get_value("taskTypeID") == TT_PHASE) {
      $rtn = $id.$this->get_value("taskName");
    } else {
      substr($this->get_value("taskName"),0,140) != $this->get_value("taskName") and $dotdotdot = "...";
      $rtn = $id.substr($this->get_value("taskName"),0,140).$dotdotdot;
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
      $q = project::get_project_type_query($filter["projectType"],$filter["current_user"],"current");
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

    // If personID filter
    if ($filter["personID"]) {
      $sql["personID"] = sprintf("(personID = %d)",$filter["personID"]);
    }
    // If creatorID filter
    if ($filter["creatorID"]) {
      $sql["creatorID"] = sprintf("(creatorID = %d)",$filter["creatorID"]);
    }
    // If managerID filter
    if ($filter["managerID"]) {
      $sql["managerID"] = sprintf("(managerID = %d)",$filter["managerID"]);
    }

    // These filters are for the time sheet dropdown list
    if ($filter["taskTimeSheetStatus"] == "open") {
      unset($sql["personID"]);
      $sql[] = sprintf("(task.dateActualCompletion IS NULL OR task.dateActualCompletion = '')");

    } else if ($filter["taskTimeSheetStatus"] == "not_assigned"){ 
      unset($sql["personID"]);
      $sql[] = sprintf("((task.dateActualCompletion IS NULL OR task.dateActualCompletion = '') AND personID != %d)",$filter["personID"]);

    } else if ($filter["taskTimeSheetStatus"] == "recent_closed"){
      unset($sql["personID"]);
      $sql[] = sprintf("(task.dateActualCompletion >= DATE_SUB(CURDATE(),INTERVAL 14 DAY))");

    } else if ($filter["taskTimeSheetStatus"] == "all") {
    }

    $filter["parentTaskID"] and $sql["parentTaskID"] = sprintf("(parentTaskID = %d)",$filter["parentTaskID"]);
    return $sql;
  }

  function get_recursive_child_tasks($taskID_of_parent, $rows=array(), $padding=0) {
    $rtn = array();
    $rows or $rows = array();
    foreach($rows as $taskID => $v) {
      $parentTaskID = $v["parentTaskID"];
      $row = $v["row"];

      if ($taskID_of_parent == $parentTaskID) {
        $row["padding"] = $padding;
        $rtn[$taskID]["row"] = $row;
        unset($rows[$taskID]);
        $padding+=1;
        $children = task::get_recursive_child_tasks($taskID,$rows,$padding);
        $padding-=1;

        if (count($children)) {
          $rtn[$taskID]["children"] = $children;
        }
      }
    }
    return $rtn;
  }

  function build_recursive_task_list($t=array(),$_FORM=array()) {
    $tasks or $tasks = array();
    $summary_ops or $summary_ops = array();
    foreach ($t as $r) {
      $row = $r["row"];
      $done[$row["taskID"]] = true; // To track orphans

      list($t,$s,$o) = task::load_task_list_row_details($row,$_FORM);
      $t and $tasks += $t;
      $s and $summary.= $s;
      $o and $summary_ops += $o;

      if ($r["children"]) {
        list($t,$s,$o,$d) = task::build_recursive_task_list($r["children"],$_FORM);
        $t and $tasks += $t;
        $s and $summary.= $s;
        $o and $summary_ops += $o;
        $d and $done += $d;
      }
    }
    return array($tasks,$summary,$summary_ops,$done);
  }

  function load_task_list_row_details($row,$_FORM=array()) {
    $summary_ops[$row["taskID"]] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$row["padding"]).$row["taskID"]." ".$row["taskName"];
    $tasks[$row["taskID"]] = $row;
    $summary.= task::get_task_list_tr($row,$_FORM);
    return array($tasks,$summary,$summary_ops);
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
     *   showTaskID           = The task ID
     *   showManager          = Show the tasks manager
     *
     *
     * Filter Options:
     *   taskView             = byProject | prioritised
     *   return               = html | text | objects | dropdown_options | objectsAndHtml
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

    // needs to use isset cause of zeroes is a valid number
    isset($_FORM["limit"]) && $_FORM["limit"] != "all" and $limit = sprintf("limit %d",$_FORM["limit"]); 
    $_FORM["return"] or $_FORM["return"] = "html";

    if ($_FORM["showDates"]) {
      $_FORM["showDate1"] = true;
      $_FORM["showDate2"] = true;
      $_FORM["showDate3"] = true;
      $_FORM["showDate4"] = true;
    }

    $_FORM["people_cache"] = get_cached_table("person");
    $_FORM["timeUnit_cache"] = get_cached_table("timeUnit");
    $_FORM["taskPriorities"] = config::get_config_item("taskPriorities");
    $_FORM["projectPriorities"] = config::get_config_item("projectPriorities");


    // Get a hierarchical list of tasks
    if ($_FORM["taskView"] == "byProject") {
      if (is_array($filter) && count($filter)) {
        $f = " WHERE ".implode(" AND ",$filter);
      }
      $db = new db_alloc;
      $q = sprintf("SELECT task.*, projectName, projectPriority
                      FROM task
                 LEFT JOIN project ON project.projectID = task.projectID
                           %s
                  GROUP BY task.taskID
                  ORDER BY projectName,taskName
                   ",$f);
      
      $_FORM["debug"] and print "\n<br>QUERY: ".$q;
      $db->query($q);
      while ($row = $db->next_record()) {
        $task = new task;
        $task->read_db_record($db);
        $row["taskURL"] = $task->get_url();
        $row["project_name"] = $db->f("projectName");
        $row["projectPriority"] = $db->f("projectPriority");
        $row["taskName"] = $task->get_task_name($_FORM);
        $row["taskLink"] = $task->get_task_link($_FORM);
        $row["taskTypeImage"] = $task->get_task_image();
        $row["newSubTask"] = $task->get_new_subtask_link();
        $_FORM["showStatus"] and $row["taskStatus"] = $task->get_status();
        $_FORM["showTimes"] and $row["percentComplete"] = $task->get_percentComplete();
        $_FORM["showPriority"] and $row["priorityFactor"] = task::get_overall_priority($row["projectPriority"], $row["priority"] ,$row["dateTargetCompletion"]);
        $row["padding"] = $_FORM["padding"];
        $row["object"] = $task;
        $row["taskID"] = $task->get_id();
        $row["parentTaskID"] = $task->get_value("parentTaskID");
        $rows[$task->get_id()] = array("parentTaskID"=>$row["parentTaskID"],"row"=>$row);
      }
    
      $rows or $rows = array();
      $tasks or $tasks = array();
      $summary_ops or $summary_ops = array();
  
      $parentTaskID = $_FORM["parentTaskID"] or $parentTaskID = 0;
      $t = task::get_recursive_child_tasks($parentTaskID,$rows);
      list($tasks,$summary,$summary_ops,$done) = task::build_recursive_task_list($t,$_FORM);

      // This bit appends the orphan tasks onto the end..
      foreach ($rows as $taskID => $r) {
        $row = $r["row"];
        $row["padding"] = 0;
        if (!$done[$taskID]) {
          list($t,$s,$o) = task::load_task_list_row_details($row,$_FORM);
          $t and $tasks += $t;
          $s and $summary.= $s;
          $o and $summary_ops += $o;
        }
        }
  
      if ((is_array($tasks) && count($tasks)) || $s || (is_array($summary_ops) && count($summary_ops))) {
        $print = true;
      }


    // Else get a prioritised list of tasks..
    } else if ($_FORM["taskView"] == "prioritised") {
          
      unset($filter["parentTaskID"]);
      if (is_array($filter) && count($filter)) {
        $filter = " WHERE ".implode(" AND ",$filter);
      }

      $q = "SELECT task.*, projectName, projectShortName, clientID, projectPriority, 
                   IF(task.dateTargetCompletion IS NULL, \"-\",
                     TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) as daysUntilDue
              FROM task LEFT JOIN project ON task.projectID = project.projectID 
             ".$filter." ".$limit;
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
        $row["taskTypeImage"] = $t->get_task_image();
        $row["newSubTask"] = $t->get_new_subtask_link();
        $_FORM["showStatus"] and $row["taskStatus"] = $t->get_status($_FORM["return"]);
        $row["object"] = $t;
        $_FORM["showTimes"] and $row["percentComplete"] = $t->get_percentComplete();
        $_FORM["showPriority"] and $row["priorityFactor"] = task::get_overall_priority($row["projectPriority"], $row["priority"], $row["dateTargetCompletion"]);
        $tasks[$row["taskID"]] = $row;
      }
    } 


    if ($_FORM["taskView"] == "prioritised") {

      if (is_array($tasks) && count($tasks)) {
        usort($tasks, array("task", "priority_compare"));
      } else {
        $tasks = array();
      }
        
      if ($_FORM["return"] == "text"){
        foreach ($tasks as $row) {
          $summary.= task::get_task_list_tr_text($row,$_FORM);
        }

      } else {
        foreach ($tasks as $row) {
          $summary.= task::get_task_list_tr($row,$_FORM);
        }
      }
    }

    $header = task::get_task_list_header($_FORM);
    $footer = task::get_task_list_footer($_FORM);

    // Decide what to actually return
    if ($print && $_FORM["return"] == "objectsAndHtml") { // sheesh
      return array($tasks,$header.$summary.$footer);

    } else if (!$print && $_FORM["return"] == "objectsAndHtml") { 
      $rtn = "<table style=\"width:100%\"><tr><td style=\"text-align:center\"><b>No Tasks Found</b></td></tr></table>";
      return array(array(),$rtn);
      
    } else if ($print && $_FORM["return"] == "objects") {
      return $tasks;

    } else if ($print && $_FORM["return"] == "html") {
      return $header.$summary.$footer;

    } else if ($print && $_FORM["return"] == "text") {
      return $summary;

    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if (!$print && ($_FORM["return"] == "html" || $_FORM["return"] == "objectsAndHtml")) {
      return "<table style=\"width:100%\"><tr><td style=\"text-align:center\"><b>No Tasks Found</b></td></tr></table>";
    } 
  } 

  function priority_compare($a, $b) {
    return $a["priorityFactor"] > $b["priorityFactor"];
  }

  function get_overall_priority($projectPriority=0,$taskPriority=0,$dateTargetCompletion) {
    if ($dateTargetCompletion) {
      $daysUntilDue = (format_date("U",$dateTargetCompletion) - mktime()) / 60 / 60 / 24;
      $mult = atan($daysUntilDue / 20) / 3.14 * 8 + 4;
    } else {
      $mult = 8;
    }

    $priorityFactor = ($taskPriority * pow($projectPriority,2)) * $mult / 10;
    return $priorityFactor;
  }

  function get_task_list_header($_FORM) {
    global $TPL;
    if ($_FORM["showHeader"]) {
      if($_FORM["showEditControls"]) {
        $summary[] = "<form action=\"" . $TPL["url_form_action"] . "\" method=\"POST\">";
      }
      #$_FORM["taskView"] == "byProject" and $summary[] = "<br>".$_FORM["projectLinks"];
      $summary[] = $TPL["table_list"];
      $summary[] = "<tr>";
      $_FORM["showEditControls"]and $summary[]="<th width=\"1%\"></th>";//check boxes (for when Task Edit Controls are visible)
                                 $summary[] = "<th width=\"1%\"></th>"; //taskTypeImage
      $_FORM["showTaskID"]   and $summary[] = "<th>ID</th>";
                                 $summary[] = "<th>Task</th>";
      $_FORM["showProject"]  and $summary[] = "<th>Project</th>";
      $_FORM["showPriority"] and $summary[] = "<th>Priority</th>";
      $_FORM["showPriority"] and $summary[] = "<th>Task Pri</th>";
      $_FORM["showPriority"] and $summary[] = "<th>Proj Pri</th>";
      $_FORM["showStatus"]   and $summary[] = "<th>Status</th>";
      $_FORM["showCreator"]  and $summary[] = "<th>Task Creator</th>";
      $_FORM["showManager"]  and $summary[] = "<th>Task Manager</th>";
      $_FORM["showAssigned"] and $summary[] = "<th>Assigned To</th>";
      $_FORM["showDate1"]    and $summary[] = "<th>Targ Start</th>";
      $_FORM["showDate2"]    and $summary[] = "<th>Targ Compl</th>";
      $_FORM["showDate3"]    and $summary[] = "<th>Act Start</th>";
      $_FORM["showDate4"]    and $summary[] = "<th>Act Compl</th>";
      $_FORM["showTimes"]    and $summary[] = "<th>Estimate</th>";
      $_FORM["showTimes"]    and $summary[] = "<th>Actual</th>";
      $_FORM["showTimes"]    and $summary[] = "<th>%</th>";
      $_FORM["showEditControls"]and $summary[]="<th>Assign To</th>";
      $summary[] ="</tr>";

      return implode("\n",$summary);
    }
  }

  function get_task_list_footer($_FORM) {
    $ret = array("</table>");
    if($_FORM["showEditControls"]) {
      $ret[] = "</td></tr><tr><td>";
      $ret[] = "Select: <span class=\"jslink\" onclick=\"checkbox_select_all('select');\">All</span> , <span class=\"jslink\" onclick=\"checkbox_select_none('select');\">None</span>, <span class=\"jslink\" onclick=\"checkbox_select_inverse('select');\">Inverse</span>";
      $ret[] = "</td><td>With selected tasks: <select name=\"update_action\">
        <option value=\"nothing\">-</options>
        <option value=\"close\">Close tasks</options>
        <option value=\"targetStartToday\">Set target start to today</options>
      </select>";
      $ret[] = "</td></tr><tr><td colspan=\"2\">";
      $ret[] = "<input type=\"submit\" name=\"run_mass_update\" value=\"Update\" />";
      $ret[] = "</form>";
    }
    return implode("\n",$ret);
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
    global $TPL;

    $today = date("Y-m-d");
    $task["dateTargetStart"]      == $today and $task["dateTargetStart"]      = "<b>".$task["dateTargetStart"]."</b>";
    $task["dateTargetCompletion"] == $today and $task["dateTargetCompletion"] = "<b>".$task["dateTargetCompletion"]."</b>";
    $task["dateActualStart"]      == $today and $task["dateActualStart"]      = "<b>".$task["dateActualStart"]."</b>";
    $task["dateActualCompletion"] == $today and $task["dateActualCompletion"] = "<b>".$task["dateActualCompletion"]."</b>";

    $people_cache = $_FORM["people_cache"];
    $timeUnit_cache = $_FORM["timeUnit_cache"];


    if ($_FORM["showDescription"] || $_FORM["showComments"]) {
      if ($task["taskDescription"]) {
        $str[] = $task["taskDescription"];
      }
      if ($_FORM["showComments"]) {
        $comments = comment::util_get_comments("task",$task["taskID"]);
        if ($comments) {
          $str[] = $comments;
        }
      }
      if (is_array($str) && count($str)) {
        $str = "<br/>".implode("<br/>",$str);
      }
    }

    $task["timeEstimate"] !== NULL and $timeEstimate = $task["timeEstimate"]*60*60;

                                  $summary[] = "<tr>";
    $_FORM["showEditControls"]and $summary[] = "  <td class=\"nobr\"><input type=\"checkbox\" name=\"select[".$task["taskID"]."]\"></td>";
                                  $summary[] = "  <td sorttable_customkey=\"".$task["taskTypeID"]."\">".$task["taskTypeImage"]."</td>";
    $_FORM["showTaskID"]      and $summary[] = "  <td>".$task["taskID"]."&nbsp;</td>";
                                  $summary[] = "  <td style=\"padding-left:".($task["padding"]*15+3)."px\">".$task["taskLink"]."&nbsp;&nbsp;".$task["newSubTask"].$str."</td>";
    $_FORM["showProject"]     and $summary[] = "  <td><a href=\"".$TPL["url_alloc_project"]."projectID=".$task["projectID"]."\">".$task["project_name"]."</a>&nbsp;</td>";
    $_FORM["showPriority"]    and $summary[] = "  <td>".sprintf("%0.2f",$task["priorityFactor"])."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td style=\"color:".$_FORM["taskPriorities"][$task["priority"]]["colour"]."\">".$_FORM["taskPriorities"][$task["priority"]]["label"]."&nbsp;</td>"; 
    $_FORM["showPriority"]    and $summary[] = "  <td style=\"color:".$_FORM["projectPriorities"][$task["projectPriority"]]["colour"]."\">".$_FORM["projectPriorities"][$task["projectPriority"]]["label"]."&nbsp;</td>"; 
    $_FORM["showStatus"]      and $summary[] = "  <td>".$task["taskStatus"]."&nbsp;</td>"; 
    $_FORM["showCreator"]     and $summary[] = "  <td>".$people_cache[$task["creatorID"]]["name"]."&nbsp;</td>";
    $_FORM["showManager"]     and $summary[] = "  <td>".$people_cache[$task["managerID"]]["name"]."&nbsp;</td>";
    $_FORM["showAssigned"]    and $summary[] = "  <td>".$people_cache[$task["personID"]]["name"]."&nbsp;</td>";
    $_FORM["showDate1"]       and $summary[] = "  <td class=\"nobr\">".$task["dateTargetStart"]."&nbsp;</td>";
    $_FORM["showDate2"]       and $summary[] = "  <td class=\"nobr\">".$task["dateTargetCompletion"]."&nbsp;</td>";
    $_FORM["showDate3"]       and $summary[] = "  <td class=\"nobr\">".$task["dateActualStart"]."&nbsp;</td>";
    $_FORM["showDate4"]       and $summary[] = "  <td class=\"nobr\">".$task["dateActualCompletion"]."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"nobr\">".seconds_to_display_format($timeEstimate)."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"nobr\">".seconds_to_display_format(task::get_time_billed($task["taskID"]))."&nbsp;</td>";
    $_FORM["showTimes"]       and $summary[] = "  <td class=\"nobr\">".$task["percentComplete"]."&nbsp;</td>";
    $_FORM["showEditControls"]and $summary[] = "  <td class=\"nobr\">".task::get_personList_dropdown($task["projectID"], $task["taskID"], 'assigneeID[' . $task["taskID"] . ']')."</td>";
                                  $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
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
      $this->get_value("dateActualCompletion") and $closed_text = "<del>" and $closed_text_end = "</del> Closed";
 
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

  function get_priority_label() {
    $taskPriorities = config::get_config_item("taskPriorities");
    return $taskPriorities[$this->get_value("priority")]["label"];
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
        if ($format == "html" || $format == "objectsAndHtml") {
          $status = "<strong class=\"overdue\">$status</strong>";
        }
      } else if ($date_target_completion && date("Y-m-d", $date_forecast_completion) > $date_target_completion) {
        $status = "Behind target - $status";
        if ($format == "html" || $format == "objectsAndHtml") {
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
      if ($format == "html" || $format == "objectsAndHtml") {
        $status = "<strong class=\"behind-target\">$status</strong>";
      }
    } else if ($actual == NOT_STARTED && $target == COMPLETED) {
      $status = "Overdue to start and be completed by $date_target_completion";
      if ($format == "html" || $format == "objectsAndHtml") {
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
        ,"showEditControls"
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

    // } else if (!$_FORM["projectID"] && $_FORM["projectType"]) {
    //   $q = project::get_project_type_query($_FORM["projectType"]);
    //   $db = new db_alloc;
    //   $db->query($q);
    //   while($row = $db->row()) {
    //     $_FORM["projectID"][] = $row["projectID"];
    //   }

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
    $_FORM["showEditControls"]and $rtn["showEditControls_checked"]= " checked";
    
    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function send_emails($selected_option, $type="", $body="", $from=array()) {
    global $current_user;

    $recipients = comment::get_email_recipients($selected_option,$from);
    list($to_address,$bcc,$successful_recipients) = comment::get_email_recipient_headers($recipients, $from);

    if ($successful_recipients) {
      $email = new alloc_email();
      $bcc && $email->add_header("Bcc",$bcc);
      $from["references"] && $email->add_header("References",$from["references"]);
      $from["in-reply-to"] && $email->add_header("In-Reply-To",$from["in-reply-to"]);
      $from["precedence"] && $email->add_header("Precedence",$from["precedence"]);
      
      $email->set_to_address($to_address);
    
      $from_name = $from["name"] or $from_name = $current_user->get_username(1);

      // REMOVE ME!!
      $email->ignore_no_email_urls = true;
      $email->ignore_no_email_hosts = true;

      $hash = $from["hash"];

      if ($hash && config::get_config_item("allocEmailKeyMethod") == "headers") {
        $email->set_message_id($hash);
      } else if ($hash && config::get_config_item("allocEmailKeyMethod") == "subject") {
        $email->set_message_id();
        $subject_extra = "{Key:".$hash."}";
      }

      $subject = "Task Comment: ".$this->get_id()." ".$this->get_value("taskName")." [".$this->get_priority_label()."] ".$subject_extra;
      $email->set_subject($subject);
      $email->set_body($body);
      $email->set_message_type($type);

      if (defined("ALLOC_DEFAULT_FROM_ADDRESS") && ALLOC_DEFAULT_FROM_ADDRESS) {
        $email->set_reply_to("All parties via ".ALLOC_DEFAULT_FROM_ADDRESS);
        $email->set_from($from_name." via ".ALLOC_DEFAULT_FROM_ADDRESS);
      } else {
        $f = $current_user->get_from() or $f = config::get_config_item("allocEmailAdmin");
        $email->set_reply_to($f);
        $email->set_from($f);
      }

      if ($from["commentID"]) {
        $files = get_attachments("comment",$from["commentID"]);
        if (is_array($files)) {
          foreach ($files as $file) {
            $email->add_attachment($file["path"]);
          }
        }
      }

      if ($email->send(false)) {
        return $successful_recipients;
      }
    }   
  }

  function add_comment_from_email($email) {

    // Make a new comment
    $comment = new comment;
    $comment->set_value("commentType","task");
    $comment->set_value("commentLinkID",$this->get_id());
    $comment->set_value("commentEmailUID",$email->msg_uid);
    $comment->save();
    $commentID = $comment->get_id();

    // Save the email attachments into a directory
    $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$comment->get_id();
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }
    $file = $dir.DIRECTORY_SEPARATOR."mail.eml";
    $decoded = $email->save_email($file);

    // Try figure out and populate the commentCreatedUser/commentCreatedUserClientContactID fields
    list($from_address,$from_name) = parse_email_address($decoded[0]["Headers"]["from:"]);

    $person = new person;
    $personID = $person->find_by_name($from_name);
    $personID or $personID = $person->find_by_email($from_address);

    $cc = new clientContact();
    $clientContactID = $cc->find_by_name($from_name, $this->get_value("projectID"));
    $clientContactID or $clientContactID = $cc->find_by_email($from_address, $this->get_value("projectID"));

    if ($personID) {
      $comment->set_value('commentCreatedUser', $personID);
    } else if ($clientContactID) {
      $comment->set_value('commentCreatedUserClientContactID', $clientContactID);
    }

    // If we don't have a $from_name, but we do have a personID or clientContactID, get proper $from_name
    if (!$from_name && $personID) {
      $from_name = person::get_fullname($personID);

    } else if (!$from_name && $clientContactID) {
      $cc = new clientContact;
      $cc->set_id($clientContactID);
      $cc->select();
      $from_name = $cc->get_value("clientContactName");

    } else if (!$from_name) {
      $from_name = $from_address;
    }

    // If user wants to un/subscribe to this comment
    $subject = $decoded[0]["Headers"]["subject:"];
    $ip_action = interestedParty::adjust_by_email_subject($subject,"task",$this->get_id(),$from_name,$from_address,$personID,$clientContactID);

    // Load up some variables for later in send_emails()
    $from["email"] = $from_address;
    $from["name"] = $from_name;
    $from["references"] = $decoded[0]["Headers"]["references:"];
    $from["in-reply-to"] = $decoded[0]["Headers"]["in-reply-to:"];
    $from["precedence"] = $decoded[0]["Headers"]["precedence:"];

    // Don't update last modified fields...
    $comment->skip_modified_fields = true;

    // Update comment with the text body and the creator
    $body = trim(mime_parser::get_body_text($decoded));
    $comment->set_value("comment",$body);
    $comment->set_value("commentCreatedUserText",trim($decoded[0]["Headers"]["from:"]));
    $comment->save();
    $from["commentID"] = $comment->get_id();
    $from["parentCommentID"] = $comment->get_id();
    $from["entity"] = "task";
    $from["entityID"] = $this->get_id();

    #$recipients[] = "assignee";
    #$recipients[] = "manager";
    #$recipients[] = "creator";
    $recipients[] = "interested";

    $token = new token;
    if ($token->select_token_by_entity_and_action("task",$comment->get_value("commentLinkID"),"add_comment_from_email")) {
      $from["hash"] = $token->get_value("tokenHash");
    }

    if ($ip_action == "subscribed") {
      $comment->set_value("comment",$from_name." is now a party to this conversation.\n\n".$comment->get_value("comment"));
      $comment->save();
    } else if ($ip_action == "unsubscribed") {
      $comment->set_value("comment",$from_name." is no longer a party to this conversation.\n\n".$comment->get_value("comment"));
      $comment->save();
    }

    if ($ip_action != "unsubscribed") { // no email sent for unsubscription requests
      $successful_recipients = $this->send_emails($recipients,"task_comments",$comment->get_value("comment"),$from);
    } 

    if ($successful_recipients) {
      $comment->set_value("commentEmailRecipients",$successful_recipients);
      $comment->save();
    }
  }



}


?>
