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
  public $classname = "task";
  public $data_table = "task";
  public $display_field_name = "taskName";
  public $key_field = "taskID";
  public $data_fields = array("taskName" => array("audit"=>true)
                             ,"taskDescription" => array("audit"=>true)
                             ,"creatorID"
                             ,"closerID"
                             ,"priority" => array("audit"=>true)
                             ,"timeLimit" => array("audit"=>true)
                             ,"timeBest" => array("audit"=>true)
                             ,"timeWorst" => array("audit"=>true)
                             ,"timeExpected" => array("audit"=>true)
                             ,"dateCreated"
                             ,"dateAssigned"
                             ,"dateClosed"
                             ,"dateTargetStart" => array("audit"=>true)
                             ,"dateTargetCompletion" => array("audit"=>true)
                             ,"dateActualStart" => array("audit"=>true)
                             ,"dateActualCompletion" => array("audit"=>true)
                             ,"taskComments"
                             ,"taskStatus" => array("audit"=>true)
                             ,"taskModifiedUser"
                             ,"projectID" => array("audit"=>true)
                             ,"parentTaskID" => array("audit"=>true)
                             ,"taskTypeID" => array("audit"=>true)
                             ,"personID" => array("audit"=>true)
                             ,"managerID" => array("audit"=>true)
                             ,"estimatorID" => array("audit"=>true)
                             ,"duplicateTaskID" => array("audit"=>true)
                             );
  public $permissions = array(PERM_PROJECT_READ_TASK_DETAIL => "read details");

  function save() {
    global $current_user, $TPL;

    // The data prior to the save
    $old = $this->all_row_fields;

    // Set the task creator
    $this->get_value("creatorID") || $this->set_value("creatorID",$current_user->get_id());

    // Set the task's status and sub-status
    list($taskStatus, $taskSubStatus) = explode("_",$this->get_value("taskStatus"));
    if (!$this->post_save_hook && in_array($taskStatus,array("closed","close","open","pending"))) {
      $this->$taskStatus($taskSubStatus);
    }

    // If a dateActualCompletion has just been entered
    if (!$this->post_save_hook && !$old["dateActualCompletion"] && $this->get_value("dateActualCompletion")) {
      $this->close("complete");

    // Else if there was a dateActualCompletion and they have just *unset* it...
    } else if (!$this->post_save_hook && $old["dateActualCompletion"] && !$this->get_value("dateActualCompletion")) {
      $this->open("inprogress");
    }

    // If they've just plugged a dateActualStart in and the task is notstarted, then change the status to Open: In Progress
    if (!$this->post_save_hook && !$old["dateActualStart"] && $this->get_value("dateActualStart") && $old["taskStatus"] == "open_notstarted") {
      $this->open("inprogress");
    }

    // If task exists and the personID has changed, update the dateAssigned
    if ($this->get_id()) {
      if (sprintf("%d",$this->get_value("personID")) != sprintf("%d",$old["personID"])) {
        $this->set_value("dateAssigned",date("Y-m-d H:i:s"));
      }
    // Else if task doesn't exist and there is a personID set, set the dateAssigned as well
    } else if ($this->get_value("personID")) {
      $this->set_value("dateAssigned",date("Y-m-d H:i:s"));
    }

    $this->get_value("taskDescription") and $this->set_value("taskDescription",rtrim($this->get_value("taskDescription")));


    // Copy the taskExpected over to the taskLimit if we're creating the task and taskLimit isn't set
    if (!$this->get_id() && !imp($this->get_value("timeLimit")) && imp($this->get_value("timeExpected"))) {
      $this->set_value("timeLimit",$this->get_value("timeExpected"));
    }

    // If we don't have a taskLimit try and inherit the project's defaultTaskLimit
    if (!$this->get_id() && !imp($this->get_value("timeLimit"))) {
      $project = $this->get_foreign_object("project");
      if ($project && imp($project->get_value("defaultTaskLimit"))) {
        $this->set_value("timeLimit",$project->get_value("defaultTaskLimit"));
      }
    }

    $rtn = parent::save();

    // If the task has just been closed, opened, pending or duplicated, then audit the change.
    if ($this->post_save_hook) {
      $psh = $this->post_save_hook;
      $this->$psh();
    }

    return $rtn;
  }

  function validate() {
    $this->get_value("taskName") or $err[] = "Please enter a name for the Task.";
    $this->get_value("priority")    || $this->set_value("priority",3);
    $this->get_value("dateCreated") || $this->set_value("dateCreated",date("Y-m-d H:i:s"));
    $this->get_value("taskStatus")  || $this->set_value("taskStatus","open_notstarted");
    $this->get_value("taskTypeID")  || $this->set_value("taskTypeID","Task");
    return parent::validate($err);
  }

  function closed($t="complete") { return $this->close($t); }  // wrapper
 
  function close($taskSubStatus = "complete") {
    global $current_user;
    $old = $this->all_row_fields;
    $cur_status = $old["taskStatus"].$old["duplicateTaskID"];
    $new_status = "closed_".$taskSubStatus.$this->get_value("duplicateTaskID");

    if ($cur_status != $new_status) { 
      $this->post_save_hook = "mark_closed";
      $taskSubStatus == "duplicate" and $this->post_save_hook = "mark_dupe";
      $this->get_value("dateActualStart")      || $this->set_value("dateActualStart", date("Y-m-d"));
      $this->get_value("dateActualCompletion") || $this->set_value("dateActualCompletion", date("Y-m-d"));
      $this->get_value("closerID")             || $this->set_value("closerID", $current_user->get_id());
      $this->get_value("dateClosed")           || $this->set_value("dateClosed",date("Y-m-d H:i:s"));           
      $this->set_value("taskStatus","closed_".$taskSubStatus);
      if ($this->get_value("taskTypeID") == "Parent") {
        $this->close_off_children_recursive();
      }
    }
  }

  function open($taskSubStatus = "inprogress") {
    $old = $this->all_row_fields;
    $cur_status = $old["taskStatus"];
    $new_status = "open_".$taskSubStatus;

    if ($cur_status != $new_status) { 
      if (!$this->get_value("taskStatus") || substr($this->get_value("taskStatus"),0,4)!="open") {
        $this->post_save_hook = "mark_reopened";
      }
      $this->set_value("closerID",null);
      $this->set_value("dateClosed","");
      $this->set_value("dateActualCompletion","");
      $this->set_value("duplicateTaskID","");
      $this->set_value("taskStatus","open_".$taskSubStatus);
      if (!$this->get_value("dateActualStart") && $taskSubStatus == "inprogress") {
        $this->set_value("dateActualStart",date("Y-m-d"));
      }
    }
  }

  function pending($taskSubStatus = "info") {
    $old = $this->all_row_fields;
    $cur_status = $old["taskStatus"];
    $new_status = "pending_".$taskSubStatus;

    if ($cur_status != $new_status) { 
      $this->post_save_hook = "mark_pending";
      $this->set_value("dateActualCompletion", "");
      $this->set_value("duplicateTaskID","");
      $this->set_value("closerID",null);
      $this->set_value("dateClosed","");
      $this->set_value("taskStatus","pending_".$taskSubStatus);
    }
  }

  function close_off_children_recursive() {
    // mark all children as complete
    global $current_user;
    $db = new db_alloc;
    if ($this->get_id()) {
      $query = sprintf("SELECT * FROM task WHERE parentTaskID = %d",$this->get_id());
      $db->query($query);
                                                                                                                               
      while ($db->next_record()) {
        $task = new task;
        $task->read_db_record($db);
        $task->close();
        $task->save();
      }
    }
  }

  function create_task_reminder() {
    // Create a reminder for this task based on the priority.
    global $current_user;

    // Get the task type
    $taskTypeName = $this->get_value("taskTypeID");
    $label = $this->get_priority_label();
    $reminderInterval = "Day";
    $intervalValue = $this->get_value("priority");
    $taskTypeName == "Parent" and $taskTypeName.= " Task";

    $subject = $taskTypeName." Reminder: ".$this->get_id()." ".$this->get_name()." [".$label."]";
    $message = "\n\n".$subject;
    $message.= "\n\n".$this->get_url(true);
    $this->get_value("taskDescription") and $message.= "\n\n".$this->get_value("taskDescription");
    $message.= "\n\n-- \nReminder created by ".$current_user->get_name()." at ".date("Y-m-d H:i:s");
    $people[] = $this->get_value("personID");
    $this->create_reminder(null, $message, $reminderInterval, $intervalValue, REMINDER_METAPERSON_TASK_ASSIGNEE, $subject);
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

  function create_reminder($personID=null, $message, $reminderInterval, $intervalValue, $metaPerson=null, $subject="") {
    $label = $this->get_priority_label();

    $reminder = new reminder;
    $reminder->set_value('reminderType', "task");
    $reminder->set_value('reminderLinkID', $this->get_id());
    $reminder->set_value('reminderRecuringInterval', $reminderInterval);
    $reminder->set_value('reminderRecuringValue', $intervalValue);
    $reminder->set_value('reminderSubject', $subject);
    $reminder->set_value('reminderContent', $message);

    $reminder->set_value('reminderAdvNoticeSent', "0");
    $reminder->set_value('reminderAdvNoticeInterval', "No");
    $reminder->set_value('reminderAdvNoticeValue', "0");

    $reminder->set_value('reminderTime', date("Y-m-d H:i:s"));
    if ($personID) {
      $reminder->set_value('personID', $personID);
    } else if ($metaPerson) {
      $reminder->set_value('metaPerson', $metaPerson);
    }
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
    // OR if we're skipping the perms checking because i.e. we're having our task status updated by a timesheet
    if (
       !$this->get_id() 
    || (is_object($p) && ($p->has_project_permission($person, array("isManager", "canEditTasks"))) 
    || $this->get_value("creatorID") == $person->get_id()
    || $this->get_value("personID") == $person->get_id()
    || $person->have_role("manage")
    || $this->skip_perms_check
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

  function update_children($field,$value="") {
    $q = sprintf("SELECT * FROM task WHERE parentTaskID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($db->row()) {
      $t = new task;
      $t->read_db_record($db);
      $t->set_value($field,$value);
      $t->save();
      if ($t->get_value("taskTypeID") == "Parent") {
        $t->update_children($field,$value);
      }
    }
  }

  function get_parent_task_select($projectID="") {
    global $TPL;
    
    if (is_object($this)) {
      $projectID = $this->get_value("projectID");
      $parentTaskID = $this->get_value("parentTaskID");
    }

    $projectID or $projectID = $_GET["projectID"];
    $parentTaskID or $parentTaskID = $_GET["parentTaskID"];

    $db = new db_alloc;
    if ($projectID) {
      list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();
      // Status may be closed_<something>
      $query = sprintf("SELECT taskID AS value, taskName AS label
                        FROM task 
                        WHERE projectID= '%d' 
                        AND taskTypeID = 'Parent'
                        AND (taskStatus NOT IN (".$ts_closed.") OR taskID = %d)
                        ORDER BY taskName", $projectID, $parentTaskID);
      $options = page::select_options($query, $parentTaskID,70);
    }
    return "<select name=\"parentTaskID\"><option value=\"\">".$options."</select>";
  }

  function get_task_cc_list_select($projectID="") {
    $interestedParty = array();
    $interestedPartyOptions = array();
    
    if (is_object($this)) {
      $interestedPartyOptions = $this->get_all_parties($projectID);
    } else {
      $interestedPartyOptions = task::get_all_parties($projectID);
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
          $str = trim(page::htmlentities($name." <".$email.">"));
          $options[$identifier] = $str;
        }
      }
    }
    $str = "<select name=\"interestedParty[]\" size=\"6\" multiple=\"true\"  style=\"width:95%\">".page::select_options($options,$interestedParty,100,false)."</select>";
    return $str;
  }

  function get_all_parties($projectID="") {
    $db = new db_alloc;
    $interestedPartyOptions = array();
  
    if ($_GET["projectID"]) {
      $projectID = $_GET["projectID"];
    } else if (!$projectID && is_object($this)) {
      $projectID = $this->get_value("projectID");
    }

    if ($projectID) {
      $interestedPartyOptions = project::get_all_parties($projectID);
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

  function get_personList_dropdown($projectID,$field,$taskID=false) {
    global $current_user;
 
    $db = new db_alloc;

    if ($_GET["timeSheetID"]) {
      $ts_query = sprintf("SELECT * FROM timeSheet WHERE timeSheetID = %d",$_GET["timeSheetID"]);
      $db->query($ts_query);
      $db->next_record();
      $owner = $db->f("personID");

    } else if (is_object($this) && $this->get_value($field)) {
      $owner = $this->get_value($field);

    } else if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $owner = $t->get_value($field);

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
   
    $str = page::select_options($ops, $owner);
    return $str;
  }

  function get_project_options($projectID="") {
    $projectID or $projectID = $_GET["projectID"];
    // Project Options - Select all projects 
    $db = new db_alloc;
    $query = sprintf("SELECT projectID AS value, projectName AS label 
                        FROM project 
                       WHERE projectStatus IN ('Current', 'Potential') 
                    ORDER BY projectName");
    $str = page::select_options($query, $projectID, 60);
    return $str;
  }

  function set_option_tpl_values() {
    // Set template values to provide options for edit selects
    global $TPL, $current_user, $isMessage;
    $db = new db_alloc;
    $projectID = $_GET["projectID"] or $projectID = $this->get_value("projectID");
    $TPL["personOptions"] = "<select name=\"personID\"><option value=\"\">".task::get_personList_dropdown($projectID, "personID")."</select>";
    $TPL["managerPersonOptions"] = "<select name=\"managerID\"><option value=\"\">".task::get_personList_dropdown($projectID, "managerID")."</select>";
    $TPL["estimatorPersonOptions"] = "<select name=\"estimatorID\"><option value=\"\">".task::get_personList_dropdown($projectID, "estimatorID")."</select>";

    // TaskType Options
    $taskType = new meta("taskType");
    $taskType_array = $taskType->get_assoc_array("taskTypeID","taskTypeID");
    $TPL["taskTypeOptions"] = page::select_options($taskType_array,$this->get_value("taskTypeID"));

    // Project dropdown
    $TPL["projectOptions"] = task::get_project_options($projectID);
    
    $priority = $this->get_value("priority") or $priority = 3;
    $taskPriorities = config::get_config_item("taskPriorities") or $taskPriorities = array();
    foreach ($taskPriorities as $k => $v) {
      $tp[$k] = $v["label"];
    }
    $TPL["priorityOptions"] = page::select_options($tp,$priority);
    $priority and $TPL["priorityLabel"] = " <div style=\"display:inline; color:".$taskPriorities[$priority]["colour"]."\">[".$this->get_priority_label()."]</div>";

    // We're building these two with the <select> tags because they will be
    // replaced by an AJAX created dropdown when the projectID changes.
    $TPL["parentTaskOptions"] = $this->get_parent_task_select();
    $TPL["interestedPartyOptions"] = $this->get_task_cc_list_select();

    $db->query(sprintf("SELECT fullName,emailAddress FROM interestedParty WHERE entity='task' AND entityID = %d ORDER BY fullName",$this->get_id()));
    while ($db->next_record()) {
      $str = trim(page::htmlentities($db->f("fullName")." <".$db->f("emailAddress").">"));
      $value = interestedParty::get_encoded_interested_party_identifier($db->f("fullName"), $db->f("emailAddress"));
      $TPL["interestedParty_hidden"].= $commar.$str."<input type=\"hidden\" name=\"interestedParty[]\" value=\"".$value."\">";
      $TPL["interestedParty_text"].= $commar.$str;
      $commar = "<br>";
    }

    $TPL["task_taskStatusLabel"] = $this->get_task_status("label");
    $TPL["task_taskStatusColour"] = $this->get_task_status("colour");
    $TPL["task_taskStatusValue"] = $this->get_value("taskStatus");
    $TPL["task_taskStatusOptions"] = page::select_options($this->get_task_statii_array(true),$this->get_value("taskStatus"));

    // If we're viewing the printer friendly view
    if ($_GET["media"] == "print") {
      // Parent Task label
      $t = new task;
      $t->set_id($this->get_value("parentTaskID"));
      $t->select();
      $TPL["parentTask"] = $t->get_display_value();

      // Task Type label
      $TPL["taskType"] = $this->get_value("taskTypeID"); 

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
    $_FORM["return"] or $_FORM["return"] = "html";
    $rtn = "<a href=\"".$this->get_url()."\">";
    $rtn.= $this->get_name($_FORM);
    $rtn.= "</a>";
    return $rtn;
  }

  function get_task_image() {
    global $TPL;
    return "<img class=\"taskType\" alt=\"".$this->get_value("taskTypeID")."\" title=\"".$this->get_value("taskTypeID")."\" src=\"".$TPL["url_alloc_images"]."taskType_".$this->get_value("taskTypeID").".gif\">";
  }

  function get_name($_FORM=array()) {

    $_FORM["prefixTaskID"] and $id = $this->get_id()." ";

    if ($this->get_value("taskTypeID") == "Parent" && $_FORM["return"] == "html") {
      $rtn = "<strong>".$id.$this->get_value("taskName",DST_HTML_DISPLAY)."</strong>";
    } else if ($_FORM["return"] == "html") {
      $rtn = $id.$this->get_value("taskName",DST_HTML_DISPLAY);
    } else {
      $rtn = $id.$this->get_value("taskName");
    }
    return $rtn;
  }

  function get_url($absolute=false) {
    global $sess;
    $sess or $sess = new Session;

    $url = "task/task.php?taskID=".$this->get_id();

    if ($sess->Started() && !$absolute) {
      $url = $sess->url(SCRIPT_PATH.$url);

    // This for urls that are emailed
    } else {
      static $prefix;
      $prefix or $prefix = config::get_config_item("allocURL");
      $url = $prefix.$url;
    }
    return $url;
  }

  function get_task_statii_array($flat=false) {
    // This gets an array that is useful for building the two types of dropdown lists that taskStatus uses
    $taskStatii = task::get_task_statii();
    if ($flat) {
      $m = new meta("taskStatus");
      $taskStatii = $m->get_assoc_array();
      foreach ($taskStatii as $status => $arr) {
        $taskStatiiArray[$status] = task::get_task_status_thing("label",$status);
      }
    } else {
      $taskStatiiArray[""] = ""; // blank entry
      foreach ($taskStatii as $status => $sub) {
        $taskStatiiArray[$status] = ucwords($status);
        foreach ($sub as $subStatus => $arr) {
          $taskStatiiArray[$status."_".$subStatus] = "&nbsp;&nbsp;&nbsp;&nbsp;".$arr["label"];
        }
      }
    } 

    return $taskStatiiArray;
  }

  function get_task_statii() {
    // looks like:
    //$arr["open"]["notstarted"] = array("label"=>"Not Started","colour"=>"#ffffff");
    //$arr["open"]["inprogress"] = array("label"=>"In Progress","colour"=>"#ffffff");
    //etc
    static $rows;
    if (!$rows) {
      $m = new meta("taskStatus");
      $rows = $m->get_assoc_array();
    }
    foreach ($rows as $taskStatusID => $arr) {
      list($s,$ss) = explode("_",$taskStatusID);
      $rtn[$s][$ss] = array("label"=>$arr["taskStatusLabel"],"colour"=>$arr["taskStatusColour"]);
    }
    return $rtn;
  }

  function get_task_status($thing="") {
    return task::get_task_status_thing($thing,$this->get_value("taskStatus"));
  }

  function get_task_status_thing($thing="",$status="") {
    list($taskStatus,$taskSubStatus) = explode("_",$status);
    $arr = task::get_task_statii();
    if ($thing && $arr[$taskStatus][$taskSubStatus][$thing]) {
      return $arr[$taskStatus][$taskSubStatus][$thing];
    }
  }

  function get_task_status_in_set_sql() {
    $m = new meta("taskStatus");
    $arr = $m->get_assoc_array();
    foreach ($arr as $taskStatusID => $r) {
      $id = strtolower(substr($taskStatusID,0,4));
      if ($id == "open") {
        $sql_open.= $commar1.'"'.$taskStatusID.'"';
        $commar1 = ",";
      } else if ($id == "clos") {
        $sql_clos.= $commar2.'"'.$taskStatusID.'"';
        $commar2 = ",";
      } else if ($id == "pend") {
        $sql_pend.= $commar3.'"'.$taskStatusID.'"';
        $commar3 = ",";
      }
    }
    return array($sql_open,$sql_pend,$sql_clos);
  }

  function get_taskStatus_sql($s) {
    if ($s) {
      if (is_array($s)) {
        $taskStatusArray = $s;
      } else {
        $taskStatusArray[] = $s;
      }
      $subsql = array();
      foreach ($taskStatusArray as $status) {
        list($taskStatus,$taskSubStatus) = explode("_",$status);
        if($taskSubStatus) {
          $subsql[] = sprintf("(task.taskStatus = '%s')",db_esc($status));
        } else {
          $subsql[] = sprintf("(SUBSTRING(task.taskStatus,1,%d) = '%s')",strlen($status),db_esc($status));
        }
      }
      return '('.implode(" OR ",$subsql).')';
    }
  }

  function get_list_filter($filter=array()) {

    // This takes care of projectID singular and plural
    $projectIDs = project::get_projectID_sql($filter);
    $projectIDs and $sql["projectIDs"] = $projectIDs;

    list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();

    // New Tasks
    if ($filter["taskDate"] == "new") {
      $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 2, date("Y")))." 00:00:00";
      date("D") == "Mon" and $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 4, date("Y")))." 00:00:00";
      $sql[] = sprintf("(task.taskStatus NOT IN (".$ts_closed.") AND task.dateCreated >= '".$past."')");

    // Due Today
    } else if ($filter["taskDate"] == "due_today") {
      $sql[] = "(task.taskStatus NOT IN (".$ts_closed.") AND task.dateTargetCompletion = '".date("Y-m-d")."')";

    // Overdue
    } else if ($filter["taskDate"] == "overdue") {
      $sql[] = "(task.taskStatus NOT IN (".$ts_closed.")
                AND 
                (task.dateTargetCompletion IS NOT NULL AND task.dateTargetCompletion != '' AND '".date("Y-m-d")."' > task.dateTargetCompletion))";
  
    // Date Created
    } else if ($filter["taskDate"] == "d_created") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateCreated >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateCreated <= '%s 23:59:59')",db_esc($filter["dateTwo"]));

    // Date Assigned
    } else if ($filter["taskDate"] == "d_assigned") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateAssigned >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateAssigned <= '%s 23:59:59')",db_esc($filter["dateTwo"]));

    // Date Target Start
    } else if ($filter["taskDate"] == "d_targetStart") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateTargetStart >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateTargetStart <= '%s')",db_esc($filter["dateTwo"]));

    // Date Target Completion
    } else if ($filter["taskDate"] == "d_targetCompletion") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateTargetCompletion >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateTargetCompletion <= '%s')",db_esc($filter["dateTwo"]));

    // Date Actual Start
    } else if ($filter["taskDate"] == "d_actualStart") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateActualStart >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateActualStart <= '%s')",db_esc($filter["dateTwo"]));

    // Date Actual Completion
    } else if ($filter["taskDate"] == "d_actualCompletion") {
      $filter["dateOne"] and $sql[] = sprintf("(task.dateActualCompletion >= '%s')",db_esc($filter["dateOne"]));
      $filter["dateTwo"] and $sql[] = sprintf("(task.dateActualCompletion <= '%s')",db_esc($filter["dateTwo"]));
    }

    // Task status filtering
    $filter["taskStatus"] and $sql[] = task::get_taskStatus_sql($filter["taskStatus"]);

    // Unset if they've only selected the topmost empty task type
    if (is_array($filter["taskTypeID"]) && count($filter["taskTypeID"])>=1 && !$filter["taskTypeID"][0]) {
      unset($filter["taskTypeID"][0]);
    }

    // If many create an SQL taskTypeID in (set) 
    if (is_array($filter["taskTypeID"]) && count($filter["taskTypeID"])) {
      $sql[] = "(task.taskTypeID in ('".implode("','",$filter["taskTypeID"])."'))";
    
    // Else if only one taskTypeID
    } else if ($filter["taskTypeID"]) {
      $sql[] = sprintf("(task.taskTypeID = '%s')",$filter["taskTypeID"]);
    }

    // Filter on taskID
    if ($filter["taskID"]) {     
      $sql[] = sprintf("(task.taskID = %d)", db_esc($filter["taskID"]));
    }
    // Filter on %taskName%
    if ($filter["taskName"]) {     
      $sql[] = sprintf("(task.taskName LIKE '%%%s%%')", db_esc($filter["taskName"]));
    }
    // If personID filter
    if ($filter["personID"]) {
      $sql["personID"] = sprintf("(task.personID = %d)",$filter["personID"]);
    }
    // If creatorID filter
    if ($filter["creatorID"]) {
      $sql["creatorID"] = sprintf("(task.creatorID = %d)",$filter["creatorID"]);
    }
    // If managerID filter
    if ($filter["managerID"]) {
      $sql["managerID"] = sprintf("(task.managerID = %d)",$filter["managerID"]);
    }

    // These filters are for the time sheet dropdown list
    if ($filter["taskTimeSheetStatus"] == "open") {
      unset($sql["personID"]);
      $sql[] = sprintf("(task.taskStatus NOT IN (".$ts_closed."))");

    } else if ($filter["taskTimeSheetStatus"] == "not_assigned"){ 
      unset($sql["personID"]);
      $sql[] = sprintf("((task.taskStatus NOT IN (".$ts_closed.")) AND personID != %d)",$filter["personID"]);

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
    foreach ($t as $r) {
      $row = $r["row"];
      $done[$row["taskID"]] = true; // To track orphans
      $tasks += array($row["taskID"]=>$row);

      if ($r["children"]) {
        list($t,$d) = task::build_recursive_task_list($r["children"],$_FORM);
        $t and $tasks += $t;
        $d and $done += $d;
      }
    }
    return array($tasks,$done);
  }

  function get_list($_FORM) {
    global $current_user;

    /*
     * This is the definitive method of getting a list of tasks that need a sophisticated level of filtering
     *
     */
 
    $filter = task::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    // Zero is a valid limit
    if ($_FORM["limit"] || $_FORM["limit"] === 0 || $_FORM["limit"] === "0") {
      $limit = sprintf("limit %d",$_FORM["limit"]); 
    }
    $_FORM["return"] or $_FORM["return"] = "html";

    $_FORM["people_cache"] = get_cached_table("person");
    $_FORM["timeUnit_cache"] = get_cached_table("timeUnit");

    // Get a hierarchical list of tasks
    if ($_FORM["taskView"] == "byProject") {
      if (is_array($filter) && count($filter)) {
        $f = " WHERE ".implode(" AND ",$filter);
      }
      $q = sprintf("SELECT task.*, projectName, projectPriority, project.currencyTypeID as currency, rate, rateUnitID
                      FROM task
                 LEFT JOIN project ON project.projectID = task.projectID
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID AND projectPerson.personID = '%d'
                           %s
                  GROUP BY task.taskID
                  ORDER BY projectName,taskName
                   ",$current_user->get_id(),$f);

    } else if ($_FORM["taskView"] == "prioritised") {
      unset($filter["parentTaskID"]);
      if (is_array($filter) && count($filter)) {
        $filter = " WHERE ".implode(" AND ",$filter);
      }

      $q = "SELECT task.*, projectName, projectShortName, clientID, projectPriority, project.currencyTypeID as currency, rate, rateUnitID,
                  priority * POWER(projectPriority, 2) * 
                      IF(task.dateTargetCompletion IS NULL, 
                        8,
                        ATAN(
                             (TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) / 20
                            ) / 3.14 * 8 + 4
                        ) / 10 as priorityFactor
              FROM task 
         LEFT JOIN project ON task.projectID = project.projectID 
         LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID AND projectPerson.personID = '".$current_user->get_id()."'
                   ".$filter." 
          ORDER BY priorityFactor ".$limit;
    }
      
    $debug and print "\n<br>QUERY: ".$q;
    $_FORM["debug"] and print "\n<br>QUERY: ".$q;
    $db = new db_alloc;
    $db->query($q);
    while ($row = $db->next_record()) {
      $task = new task;
      $task->read_db_record($db);
      $row["taskURL"] = $task->get_url();
      $row["taskName"] = $task->get_name($_FORM);
      $row["taskLink"] = $task->get_task_link($_FORM);
      $row["project_name"] = $row["projectShortName"]  or  $row["project_name"] = $row["projectName"];
      $row["projectPriority"] = $db->f("projectPriority");
      $row["taskTypeImage"] = $task->get_task_image();
      $row["taskStatusLabel"] = $task->get_task_status("label");
      $row["taskStatusColour"] = $task->get_task_status("colour");
      $row["creator_name"] = $_FORM["people_cache"][$row["creatorID"]]["name"];
      $row["manager_name"] = $_FORM["people_cache"][$row["managerID"]]["name"];
      $row["assignee_name"] = $_FORM["people_cache"][$row["personID"]]["name"];
      $row["newSubTask"] = $task->get_new_subtask_link();
      $_FORM["showDateStatus"] and $row["taskDateStatus"] = $task->get_dateStatus();
      $_FORM["showPercent"] and $row["percentComplete"] = $task->get_percentComplete();
      $_FORM["showTimes"] and $row["timeActual"] = $task->get_time_billed()/60/60;
      $row["rate"] = page::money($row["currency"],$row["rate"],"%mo");
      $row["rateUnit"] = $_FORM["timeUnit_cache"][$row["rateUnitID"]]["timeUnitName"];
      $_FORM["showPriority"] and $row["priorityFactor"] = task::get_overall_priority($row["projectPriority"], $row["priority"] ,$row["dateTargetCompletion"]);
      $row["priorityFactor"] = sprintf("%0.2f",$row["priorityFactor"]);
      $row["priorityLabel"] = $task->get_priority_label();
      if (!$_FORM["skipObject"]) {
        $_FORM["return"] == "array" and $row["object"] = $task;
      }
      $row["padding"] = $_FORM["padding"];
      $row["taskID"] = $task->get_id();
      $row["parentTaskID"] = $task->get_value("parentTaskID");
      $row["timeLimit"] !== NULL    and $row["timeLimitLabel"]    = seconds_to_display_format($row["timeLimit"]*60*60);
      $row["timeBest"] !== NULL     and $row["timeBestLabel"]     = seconds_to_display_format($row["timeBest"]*60*60);
      $row["timeWorst"] !== NULL    and $row["timeWorstLabel"]    = seconds_to_display_format($row["timeWorst"]*60*60);
      $row["timeExpected"] !== NULL and $row["timeExpectedLabel"] = seconds_to_display_format($row["timeExpected"]*60*60);
      $row["timeActual"] !== NULL   and $row["timeActualLabel"]   = seconds_to_display_format($row["timeActual"]*60*60);
      if ($_FORM["showComments"] && $comments = comment::util_get_comments("task",$row["taskID"])) {
        $row["comments"] = $comments;
      }
      if ($_FORM["taskView"] == "byProject") {
        $rows[$task->get_id()] = array("parentTaskID"=>$row["parentTaskID"],"row"=>$row);
      } else if ($_FORM["taskView"] == "prioritised") {
        $rows[$row["taskID"]] = $row;
      }
    }
 
    if ($_FORM["taskView"] == "byProject") {
      $parentTaskID = $_FORM["parentTaskID"] or $parentTaskID = 0;
      $t = task::get_recursive_child_tasks($parentTaskID,(array)$rows);
      list($tasks,$done) = task::build_recursive_task_list($t,$_FORM);

      // This bit appends the orphan tasks onto the end..
      foreach ((array)$rows as $taskID => $r) {
        $row = $r["row"];
        $row["padding"] = 0;
        if (!$done[$taskID]) {
          $tasks += array($taskID=>$row);
        }
      }
    } else if ($_FORM["taskView"] == "prioritised") {
      if (is_array($rows) && count($rows)) {
        uasort($rows, array("task", "priority_compare"));
      }
      $tasks = $rows;
    }

    return (array)$tasks;
  }

  function get_list_html($tasks=array(),$ops=array()) {
    global $TPL;
    $TPL["taskListRows"] = $tasks;
    $TPL["_FORM"] = $ops;
    $TPL["taskPriorities"] = config::get_config_item("taskPriorities");
    $TPL["projectPriorities"] =  config::get_config_item("projectPriorities");
    include_template(dirname(__FILE__)."/../templates/taskListS.tpl");
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

  function get_task_priority_dropdown($priority=false) {
    $taskPriorities = config::get_config_item("taskPriorities") or $taskPriorities = array();
    foreach ($taskPriorities as $k => $v) {
      $tp[$k] = $v["label"];     
    }
    return page::select_options($tp,$priority);
  }

  function get_new_subtask_link() {
    global $TPL;
    if (is_object($this) && $this->get_value("taskTypeID") == "Parent") {
      return "<a class=\"noprint\" href=\"".$TPL["url_alloc_task"]."projectID=".$this->get_value("projectID")."&parentTaskID=".$this->get_id()."\">New Subtask</a>";
    }
  }

  function get_time_billed($taskID="") {
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
      return "";
    }
  }

  function get_percentComplete($get_num=false) {

    $timeActual = sprintf("%0.2f",$this->get_time_billed());
    $timeExpected = sprintf("%0.2f",$this->get_value("timeExpected")*60*60);

    if ($timeExpected>0 && is_object($this)) {

      $percent = $timeActual / $timeExpected * 100;
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
    static $taskPriorities;
    $taskPriorities or $taskPriorities = config::get_config_item("taskPriorities");
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

    $date_actual_start = format_date("U",$date_actual_start);
    $time_spent = mktime() - $date_actual_start;
    $time_per_percent = $time_spent / $percent_complete;
    $percent_left = 100 - $percent_complete;
    $time_left = $percent_left * $time_per_percent;
    $date_forecast_completion = mktime() + $time_left;
    return $date_forecast_completion;
  }

  function get_dateStatus($format = "html", $type = "standard") {
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

  function get_list_vars() {
    $taskStatii = task::get_task_statii_array(true);
    foreach($taskStatii as $k => $v) {
      $taskStatiiStr.= $pipe.$k;
      $pipe = " | ";
    }

    return array("taskView"             => "[MANDATORY] eg: byProject | prioritised"
                ,"return"               => "[MANDATORY] eg: html | array"
                ,"limit"                => "Appends an SQL limit (only for prioritised and objects views)"
                ,"projectIDs"           => "An array of projectIDs"
                ,"projectID"            => "A single projectID"
                ,"taskStatus"           => $taskStatiiStr
                ,"taskDate"             => "new | due_today | overdue | d_created | d_assigned | d_targetStart | d_targetCompletion | d_actualStart | d_actualCompletion (all the d_* options require a dateOne (From Date) or a dateTwo (To Date) to be filled)"
                ,"dateOne"              => "From Date (must be used with a d_* taskDate option)"
                ,"dateTwo"              => "To Date (must be used with a d_* taskDate option)"
                ,"taskTimeSheetStatus"  => "my_open | not_assigned | my_closed | my_recently_closed | all"
                ,"taskTypeID"           => "Task | Parent | Message | Fault | Milestone"
                ,"current_user"         => "Lets us fake a current_user id for when generating task emails and there is no \$current_user object"
                ,"taskID"               => "Task ID"
                ,"taskName"             => "Task Name (eg: *install*)"
                ,"creatorID"            => "Task creator"
                ,"managerID"            => "The person managing task"
                ,"personID"             => "The person assigned to the task"
                ,"parentTaskID"         => "ID of parent task, all top level tasks have parentTaskID of 0, so this defaults to 0"
                ,"projectType"          => "mine | pm | tsm | pmORtsm | Current | Potential | Archived | all"
                ,"applyFilter"          => "Saves this filter as the persons preference"
                ,"padding"              => "Initial indentation level (useful for byProject lists)"
                ,"url_form_action"      => "The submit action for the filter form"
                ,"form_name"            => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"             => "Specify that the filter preferences should not be saved this time"
                ,"skipObject"           => "Services coming over SOAP should set this true to minimize the amount of bandwidth"
                ,"showDates"            => "Show dates 1-4"
                ,"showDate1"            => "Date Target Start"
                ,"showDate2"            => "Date Target Completion"
                ,"showDate3"            => "Date Actual Start"
                ,"showDate4"            => "Date Actual Completion"
                ,"showDate5"            => "Date Created"
                ,"showProject"          => "The tasks Project (has different layout when prioritised vs byProject)"
                ,"showPriority"         => "The calculated overall priority, then the tasks, then the projects priority"
                ,"showStatus"           => "A colour coded textual description of the status of the task"
                ,"showDateStatus"       => "A colour coded textual description of the *dates* status of the task"
                ,"showCreator"          => "The tasks creator"
                ,"showAssigned"         => "The person assigned to the task"
                ,"showTimes"            => "The original estimate and the time billed and percentage"
                ,"showHeader"           => "A descriptive html header row"
                ,"showDescription"      => "The tasks description"
                ,"showComments"         => "The tasks comments"
                ,"showTaskID"           => "The task ID"
                ,"showManager"          => "Show the tasks manager"
                ,"showPercent"          => "The percent complete"
                ,"showEdit"             => "Display the html edit controls to allow en masse task editing"
                );
  }

  function load_form_data($defaults=array()) {
    global $current_user;
  
    $page_vars = array_keys(task::get_list_vars());

    $_FORM = get_all_form_data($page_vars,$defaults);

    if ($_FORM["projectID"] && !is_array($_FORM["projectID"])) {
      $p = $_FORM["projectID"];
      unset($_FORM["projectID"]);
      $_FORM["projectID"][] = $p;

    } else if (!$_FORM["projectType"]){
      $_FORM["projectType"] = "mine";
    }

    if ($_FORM["showDates"]) {
      $_FORM["showDate1"] = true;
      $_FORM["showDate2"] = true;
      $_FORM["showDate3"] = true;
      $_FORM["showDate4"] = true;
      $_FORM["showDate5"] = true;
    }

    if ($_FORM["applyFilter"] && is_object($current_user)) {
      // we have a new filter configuration from the user, and must save it
      if(!$_FORM["dontSave"]) {
        $url = $_FORM["url_form_action"];
        unset($_FORM["url_form_action"]);
        $current_user->prefs[$_FORM["form_name"]] = $_FORM;
        $_FORM["url_form_action"] = $url;
      }
    } else {
      // we haven't been given a filter configuration, so load it from user preferences
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["projectType"] = "mine";
        $_FORM["taskStatus"] = "open";
        $_FORM["personID"] = $current_user->get_id();
      }
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
    global $current_user;

    $db = new db_alloc;

    // Load up the forms action url
    $rtn["url_form_action"] = $_FORM["url_form_action"];

    // Load up the filter bits
    $rtn["projectOptions"] = project::get_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);

    $_FORM["projectType"] and $rtn["projectType_checked"][$_FORM["projectType"]] = " checked"; 

    $rtn["personOptions"] = "\n<option value=\"\"> ";
    $rtn["personOptions"].= page::select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

    $rtn["creatorPersonOptions"] = "\n<option value=\"\"> ";
    $rtn["creatorPersonOptions"].= page::select_options(person::get_username_list($_FORM["creatorID"]), $_FORM["creatorID"]);

    $rtn["managerPersonOptions"] = "\n<option value=\"\"> ";
    $rtn["managerPersonOptions"].= page::select_options(person::get_username_list($_FORM["managerID"]), $_FORM["managerID"]);

    $taskType = new meta("taskType");
    $taskType_array = $taskType->get_assoc_array("taskTypeID","taskTypeID");
    $rtn["taskTypeOptions"] = page::select_options($taskType_array,$_FORM["taskTypeID"]);

    $_FORM["taskView"] and $rtn["taskView_checked_".$_FORM["taskView"]] = " checked";

    $taskStatii = task::get_task_statii_array();
    $rtn["taskStatusOptions"] = page::select_options($taskStatii, $_FORM["taskStatus"]);

    $_FORM["showDescription"] and $rtn["showDescription_checked"] = " checked";
    $_FORM["showDates"]       and $rtn["showDates_checked"]       = " checked";
    $_FORM["showCreator"]     and $rtn["showCreator_checked"]     = " checked";
    $_FORM["showAssigned"]    and $rtn["showAssigned_checked"]    = " checked";
    $_FORM["showTimes"]       and $rtn["showTimes_checked"]       = " checked";
    $_FORM["showPercent"]     and $rtn["showPercent_checked"]     = " checked";
    $_FORM["showPriority"]    and $rtn["showPriority_checked"]    = " checked";
    $_FORM["showDateStatus"]  and $rtn["showDateStatus_checked"]  = " checked";
    $_FORM["showTaskID"]      and $rtn["showTaskID_checked"]      = " checked";
    $_FORM["showManager"]     and $rtn["showManager_checked"]     = " checked";
    
    $arrow = " --&gt;";
    $taskDateOps = array(""                   => ""
                        ,"new"                => "New Tasks"
                        ,"due_today"          => "Due Today"
                        ,"overdue"            => "Overdue"
                        ,"d_created"          => "Date Created".$arrow
                        ,"d_assigned"         => "Date Assigned".$arrow
                        ,"d_targetStart"      => "Estimated Start".$arrow
                        ,"d_targetCompletion" => "Estimated Completion".$arrow
                        ,"d_actualStart"      => "Date Started".$arrow
                        ,"d_actualCompletion" => "Date Completed".$arrow
                        );
    $rtn["taskDateOptions"] = page::select_options($taskDateOps, $_FORM["taskDate"], 45, false);

    if (!in_array($_FORM["taskDate"],array("new","due_today","overdue"))) {
      $rtn["dateOne"] = $_FORM["dateOne"];
      $rtn["dateTwo"] = $_FORM["dateTwo"];
    }


    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_changes_list() {
    // This function returns HTML rows for the changes that have been made to this task
    $rows = array();

    $people_cache = get_cached_table("person");

    $options = array("return"       => "array"
                    ,"entityType"   => "task"
                    ,"entityID"     => $this->get_id());
    $changes = auditItem::get_list($options);

    // Insert the creation event into the table to make the history complete.
    $rows []= '<tr><td class="nobr">' . $this->get_value("dateCreated") . '</td><td>The task was created.</td><td>' . page::htmlentities($people_cache[$this->get_value("creatorID")]["name"]) . "</td></tr>";

    // we record changes to taskName, taskDescription, priority, timeLimit, projectID, dateActualCompletion, dateActualStart, dateTargetStart, dateTargetCompletion, personID, managerID, parentTaskID, taskTypeID, duplicateTaskID
    foreach($changes as $auditItem) {
      $changeDescription = "";
      $oldValue = $auditItem->get_value('oldValue',DST_HTML_DISPLAY);
      if($auditItem->get_value('changeType') == 'FieldChange') {
        $newValue = page::htmlentities($auditItem->get_new_value());
        switch($auditItem->get_value('fieldName')) {
          case 'taskName':
            $changeDescription = "Task name changed from '$oldValue' to '$newValue'.";
            break;
          case 'taskDescription':
            $changeDescription = "Task description changed. <a class=\"magic\" href=\"#x\" onclick=\"$('#auditItem" . $auditItem->get_id() . "').slideToggle('fast');\">Show</a> <div class=\"hidden\" id=\"auditItem" . $auditItem->get_id() . "\"><div><b>Old Description</b><br>" .$oldValue. "</div><div><b>New Description</b><br>" .$newValue. "</div></div>";
            break;
          case 'priority':
            $priorities = config::get_config_item("taskPriorities");
            $changeDescription = "Task priority changed from " . $priorities[$oldValue]["label"] . " to " . $priorities[$newValue]["label"] . ".";
            $changeDescription = sprintf('Task priority changed from <span style="color: %s;">%s</span> to <span style="color: %s;">%s</span>.', $priorities[$oldValue]["colour"], $priorities[$oldValue]["label"], $priorities[$newValue]["colour"], $priorities[$newValue]["label"]);
          break;
          case 'projectID':
            task::load_entity("project", $oldValue, $oldProject);
            task::load_entity("project", $newValue, $newProject);
            is_object($oldProject) and $oldProjectLink = $oldProject->get_project_link();
            is_object($newProject) and $newProjectLink = $newProject->get_project_link();
            $oldProjectLink or $oldProjectLink = "&lt;empty&gt;";
            $newProjectLink or $newProjectLink = "&lt;empty&gt;";
            $changeDescription = "Project changed from ".$oldProjectLink." to ".$newProjectLink.".";
          break;
          case 'parentTaskID':
            task::load_entity("task", $oldValue, $oldTask);
            task::load_entity("task", $newValue, $newTask);
            if(!$oldValue && is_object($newTask)) {
              $changeDescription = sprintf("Task was set to a child of %d %s.", $newTask->get_id(), $newTask->get_task_link());
            } else if(!$newValue && is_object($oldTask)) {
              $changeDescription = sprintf("Task ceased to be a child of %d %s", $oldTask->get_id(), $oldTask->get_task_link());
            } else if (is_object($oldTask) && is_object($newTask)) {
              $changeDescription = sprintf("Task ceased to be a child of %d %s and became a child of %d %s.", $oldTask->get_id(), $oldTask->get_task_link(), $newTask->get_id(), $newTask->get_task_link());
            }
          break;
          case 'duplicateTaskID':
            task::load_entity("task", $oldValue, $oldTask);
            task::load_entity("task", $newValue, $newTask);
            if(!$oldValue) {
              $changeDescription = "The task was marked a duplicate of " . $newTask->get_task_link() . ".";
            } elseif(!$newValue) {
              $changeDescription = "Task is no longer a duplicate of " . $oldTask->get_task_link() . ".";
            } else {
              $changeDescription = "Task is no longer a duplicate of " . $oldTask->get_task_link() . " and is now a duplicate of " . $newTask->get_task_link() . ".";
            }
          break;
          case 'personID':
            $changeDescription = "Task was reassigned from " . $people_cache[$oldValue]["name"] . " to " . $people_cache[$newValue]["name"] . ".";
          break;
          case 'managerID':
            $changeDescription = "Task manager changed from " . $people_cache[$oldValue]["name"] . " to " . $people_cache[$newValue]["name"] . ".";
          break;
          case 'estimatorID':
            $changeDescription = "Task estimator changed from " . $people_cache[$oldValue]["name"] . " to " . $people_cache[$newValue]["name"] . ".";
          break;
          case 'taskTypeID':
            $changeDescription = "Task type was changed from " . $oldValue . " to " . $newValue . ".";
          break;
          case 'taskStatus':
            $changeDescription = sprintf('Task status changed from <span style="background-color:%s">%s</span> to <span style="background-color:%s">%s</span>.'
                                        ,task::get_task_status_thing("colour",$oldValue)
                                        ,task::get_task_status_thing("label",$oldValue)
                                        ,task::get_task_status_thing("colour",$newValue)
                                        ,task::get_task_status_thing("label",$newValue)
                                        );
          break;
          case 'dateActualCompletion':
          case 'dateActualStart':
          case 'dateTargetStart':
          case 'dateTargetCompletion':
          case 'timeLimit':
          case 'timeBest':
          case 'timeWorst':
          case 'timeExpected':
            // these cases are more or less identical
            switch($auditItem->get_value('fieldName')) {
              case 'dateActualCompletion': $fieldDesc = "actual completion date"; break;
              case 'dateActualStart': $fieldDesc = "actual start date"; break;
              case 'dateTargetStart': $fieldDesc = "estimate/target start date"; break;
              case 'dateTargetCompletion': $fieldDesc = "estimate/target completion date"; break;
              case 'timeLimit': $fieldDesc = "hours worked limit"; break;
              case 'timeBest': $fieldDesc = "best estimate"; break;
              case 'timeWorst': $fieldDesc = "worst estimate"; break;
              case 'timeExpected': $fieldDesc = "expected estimate";
            }
            if(!$oldValue) {
              $changeDescription = "The $fieldDesc was set to $newValue.";
            } elseif(!$newValue) {
              $changeDescription = "The $fieldDesc, previously $oldValue, was removed.";
            } else {
              $changeDescription = "The $fieldDesc changed from $oldValue to $newValue.";
            }
          break;
        }

      // these are the cases in which other tasks are un/marked duplicates of this task
      } elseif($auditItem->get_value('changeType') == 'TaskMarkedDuplicate') {
        task::load_entity("task", $oldValue, $otherTask);
        $changeDescription = "The task " . $otherTask->get_id() . " " . $otherTask->get_task_link() . " was marked a duplicate of this task.";
      } elseif($auditItem->get_value('changeType') == 'TaskUnmarkedDuplicate') {
        task::load_entity("task", $oldValue, $otherTask);
        $changeDescription = "The task " . $otherTask->get_id() . " " . $otherTask->get_task_link() . " was no longer marked a duplicate of this task.";
      } elseif($auditItem->get_value('changeType') == 'TaskClosed') {
        $changeDescription = "The task was closed.";
      } elseif($auditItem->get_value('changeType') == 'TaskReopened') {
        $changeDescription = "The task was opened.";
      } elseif($auditItem->get_value('changeType') == 'TaskPending') {
        $changeDescription = "The task was pending.";
      }
      $rows[] = "<tr><td class=\"nobr\">" . $auditItem->get_value("dateChanged") . "</td><td>$changeDescription</td><td>" . page::htmlentities($people_cache[$auditItem->get_value("personID")]["name"]) . "</td></tr>";

    }

    return implode("\n", $rows);
  }

  function load_entity($type, $id, &$entity) {
    // helper function to cut down on code duplication in the above function
    if($id) {
      $entity = new $type;
      $entity->set_id($id);
      $entity->select();
    }
  }

  function mark_closed() {
    // write a message into the log, closing this task
    $ai = new auditItem();
    $ai->audit_special_change($this, "TaskClosed");
    $ai->insert();
  }

  function mark_dupe($id=false) {
    $id or $id = $this->get_value("duplicateTaskID"); 

    if ($id) {
      $othertask = new task;
      $othertask->set_id($id);
      $othertask->select();

      // Note in the other task's history that this task was marked a duplicate of it
      $ai = new auditItem;
      $ai->audit_special_change($othertask, "TaskMarkedDuplicate", $this->get_id());
      $ai->insert();
      // If we have a previous duplicate, notify the previous dupe task that it's no longer a duplicate
      if ($this->all_row_fields["duplicateTaskID"]) {
        $othertask = new task;
        $othertask->set_id($this->all_row_fields["duplicateTaskID"]);
        $othertask->select();
        $ai = new auditItem;
        $ai->audit_special_change($othertask, "TaskUnmarkedDuplicate", $this->get_id());
        $ai->insert();
      } 
    }
  }

  function mark_reopened() {
    $ai = new auditItem();
    $ai->audit_special_change($this, "TaskReopened");
    $ai->insert();
  }

  function mark_pending() {
    $ai = new auditItem();
    $ai->audit_special_change($this, "TaskPending");
    $ai->insert();
  }

  // Called when a comment is added to this task via email
  function add_comment_hook($comment) {
    // If status is pending or closed and this comment came from an external 
    // party, re-open the task.
    $status = $this->get_value('taskStatus');
    if (substr($status, 0, 4) == "open") {
      return;
    }

    // if ClientContactID is set it's a client email - reopen the task.
    // I tried to use commentCreatedUserID, but it's always set for some reason
    if (!$comment->get_value('commentCreatedUserClientContactID')) {
      return;
    }

    $this->open();
    $this->save();
  }

  function update_search_index_doc(&$index) {
    $p = get_cached_table("person");
    $creatorID = $this->get_value("creatorID");
    $creator_field = $creatorID." ".$p[$creatorID]["username"]." ".$p[$creatorID]["name"];
    $closerID = $this->get_value("closerID");
    $closer_field = $closerID." ".$p[$closerID]["username"]." ".$p[$closerID]["name"];
    $personID = $this->get_value("personID");
    $person_field = $personID." ".$p[$personID]["username"]." ".$p[$personID]["name"];
    $managerID = $this->get_value("managerID");
    $manager_field = $managerID." ".$p[$managerID]["username"]." ".$p[$managerID]["name"];
    $taskModifiedUser = $this->get_value("taskModifiedUser");
    $taskModifiedUser_field = $taskModifiedUser." ".$p[$taskModifiedUser]["username"]." ".$p[$taskModifiedUser]["name"];
    $status = $this->get_value("taskStatus");

    if ($this->get_value("projectID")) {
      $project = new project();
      $project->set_id($this->get_value("projectID"));
      $project->select();
      $projectName = $project->get_name();
      $projectShortName = $project->get_name(array("showShortProjectLink"=>true));
      $projectShortName && $projectShortName != $projectName and $projectName.= " ".$projectShortName;
    }

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$this->get_value("taskName")));
    $doc->addField(Zend_Search_Lucene_Field::Text('project' ,$projectName));
    $doc->addField(Zend_Search_Lucene_Field::Text('pid'     ,$this->get_value("projectID")));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$creator_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('closer'  ,$closer_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('assignee',$person_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('manager' ,$manager_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('modifier',$taskModifiedUser_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$this->get_value("taskDescription")));
    $doc->addField(Zend_Search_Lucene_Field::Text('priority',$this->get_value("priority")));
    $doc->addField(Zend_Search_Lucene_Field::Text('limit'   ,$this->get_value("timeLimit")));
    $doc->addField(Zend_Search_Lucene_Field::Text('best'    ,$this->get_value("timeBest")));
    $doc->addField(Zend_Search_Lucene_Field::Text('worst'   ,$this->get_value("timeWorst")));
    $doc->addField(Zend_Search_Lucene_Field::Text('expected',$this->get_value("timeExpected")));
    $doc->addField(Zend_Search_Lucene_Field::Text('type',$this->get_value("taskTypeID")));
    $doc->addField(Zend_Search_Lucene_Field::Text('status',$status));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCreated',str_replace("-","",$this->get_value("dateCreated"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateAssigned',str_replace("-","",$this->get_value("dateAssigned"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateClosed',str_replace("-","",$this->get_value("dateClosed"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetStart',str_replace("-","",$this->get_value("dateTargetStart"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetCompletion',str_replace("-","",$this->get_value("dateTargetCompletion"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateStart',str_replace("-","",$this->get_value("dateActualStart"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCompletion',str_replace("-","",$this->get_value("dateActualCompletion"))));
    $index->addDocument($doc);
  }

}


?>
