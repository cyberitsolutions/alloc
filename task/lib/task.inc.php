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

define("PERM_PROJECT_READ_TASK_DETAIL", 256);

class task extends db_entity {
  public $classname = "task";
  public $data_table = "task";
  public $display_field_name = "taskName";
  public $key_field = "taskID";
  public $data_fields = array("taskName"
                             ,"taskDescription"
                             ,"creatorID"
                             ,"closerID"
                             ,"priority"
                             ,"timeLimit"
                             ,"timeBest"
                             ,"timeWorst"
                             ,"timeExpected"
                             ,"dateCreated"
                             ,"dateAssigned"
                             ,"dateClosed"
                             ,"dateTargetStart"
                             ,"dateTargetCompletion"
                             ,"dateActualStart"
                             ,"dateActualCompletion"
                             ,"taskStatus"
                             ,"taskModifiedUser"
                             ,"projectID"
                             ,"parentTaskID"
                             ,"taskTypeID"
                             ,"personID"
                             ,"managerID"
                             ,"estimatorID"
                             ,"duplicateTaskID"
                             );
  public $permissions = array(PERM_PROJECT_READ_TASK_DETAIL => "read details");

  function save() {
    $current_user = &singleton("current_user");
    global $TPL;

    $errors = $this->validate();
    if ($errors) {
      alloc_error($errors);

    } else {
      $existing = $this->all_row_fields;
      if ($existing["taskStatus"] != $this->get_value("taskStatus")) {
        $db = new db_alloc();
        $db->query("call change_task_status(%d,'%s')",$this->get_id(),$this->get_value("taskStatus"));
        $row = $db->qr("SELECT taskStatus
                              ,dateActualCompletion
                              ,dateActualStart
                              ,dateClosed
                              ,closerID
                          FROM task
                         WHERE taskID = %d",$this->get_id());
        // Changing a task's status changes these fields.
        // Unfortunately the call to save() below erroneously nukes these fields.
        // So we manually set them to whatever change_task_status() has dictated.
        $this->set_value("taskStatus",$row["taskStatus"]);
        $this->set_value("dateActualCompletion",$row["dateActualCompletion"]);
        $this->set_value("dateActualStart",$row["dateActualStart"]);
        $this->set_value("dateClosed",$row["dateClosed"]);
        $this->set_value("closerID",$row["closerID"]);
      }

      return parent::save();
    }
  }

  function delete() {
    if ($this->can_be_deleted()) {
      return parent::delete();
    }
  }

  function validate() {
    // Validate/coerce the fields
    $coerce = array("inprogress"=>"open_inprogress"
                   ,"notstarted"=>"open_notstarted"
                   ,"info"      =>"pending_info"
                   ,"client"    =>"pending_client"
                   ,"manager"   =>"pending_manager"
                   ,"tasks"     =>"pending_tasks"
                   ,"invalid"   =>"closed_invalid"
                   ,"duplicate" =>"closed_duplicate"
                   ,"incomplete"=>"closed_incomplete"
                   ,"complete"  =>"closed_complete"
                   ,"archived"  =>"closed_archived"
                   ,"open"      =>"open_inprogress"
                   ,"pending"   =>"pending_info"
                   ,"close"     =>"closed_complete"
                   ,"closed"    =>"closed_complete"
                   );
    if ($this->get_value("taskStatus") && !in_array($this->get_value("taskStatus"),$coerce)) {
      $orig = $this->get_value("taskStatus");
      $cleaned = str_replace("-","_",strtolower($orig));
      if (in_array($cleaned,$coerce)) {
        $this->set_value("taskStatus",$cleaned);
      } else if ($coerce[$cleaned]) {
        $this->set_value("taskStatus",$coerce[$cleaned]);
      }

      if (!in_array($this->get_value("taskStatus"),$coerce)) {
        $err[] = "Unrecognised task status: ".$orig;
      }
    }

    in_array($this->get_value("priority"),array(1,2,3,4,5)) or $err[] = "Invalid priority.";
    in_array(ucwords($this->get_value("taskTypeID")),array("Task","Fault","Message","Milestone","Parent")) or $err[] = "Invalid Task Type.";
    $this->get_value("taskName") or $err[] = "Please enter a name for the Task.";
    $this->get_value("taskDescription") and $this->set_value("taskDescription",rtrim($this->get_value("taskDescription")));
    return parent::validate($err);
  }

  function add_pending_tasks($str) {
    $db = new db_alloc();
    $db->query("SELECT * FROM pendingTask WHERE taskID = %d",$this->get_id());
    $rows = array();
    while ($row = $db->row()) {
      $rows[] = $row["pendingTaskID"]; 
    }
    asort($rows);

    $bits = preg_split("/\b/",$str);
    $bits or $bits = array();
    asort($bits);

    $str1 = implode(",",(array)$rows);
    $str2 = implode(",",(array)$bits);

    if ($str1 != $str2) {
      $db->qr("DELETE FROM pendingTask WHERE taskID = %d",$this->get_id());
      foreach ((array)$bits as $id) {
        if (is_numeric($id)) {
          $db->query("INSERT INTO pendingTask (taskID,pendingTaskID) VALUES (%d,%d)",$this->get_id(),$id);
        }
      }
    }
  }

  function get_pending_tasks($invert=false) {
    $db = new db_alloc();
    $q = prepare("SELECT * FROM pendingTask WHERE %s = %d",($invert ? "pendingTaskID" : "taskID"),$this->get_id());
    $db->query($q);
    while ($row = $db->row()) {
      $rows[] = $row[($invert ? "taskID" : "pendingTaskID")];
    }
    return (array)$rows;
  }

  function get_reopen_reminders() {
    $q = prepare("SELECT reminder.*,token.*,tokenAction.*, reminder.reminderID as rID
                    FROM reminder
               LEFT JOIN token ON reminder.reminderHash = token.tokenHash
               LEFT JOIN tokenAction ON token.tokenActionID = tokenAction.tokenActionID
                   WHERE token.tokenEntity = 'task'
                     AND token.tokenEntityID = %d
                     AND reminder.reminderActive = 1
                     AND token.tokenActionID = 4
                GROUP BY reminder.reminderID
                 ",$this->get_id());

    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $rows[] = $row;
    }
    return (array)$rows;
  }

  function add_reopen_reminder($date) {
    if ($date) {
      $rows = $this->get_reopen_reminders();
      foreach ($rows as $r) {
        $reminder = new reminder();
        $reminder->set_id($r['rID']);
        $reminder->select();
        $reminder->deactivate();
      }

      if ($date != 'null') { // alloc-cli can pass 'null' to kill future reopening
        $tokenActionID = 4;
        //$maxUsed = 1; nope, so people can have recurring reminders
        $name = "Reopen pending task";
        $desc = "This reminder will automatically reopen this task, if it is pending.";
        $recipients = array();
        if (strlen($date) <= "10") {
          $date.= " 08:30:00";
        }
        $this->add_notification($tokenActionID,$maxUsed,$name,$desc,$recipients,$date);
      }
    }
  }

  function create_task_reminder() {
    // Create a reminder for this task based on the priority.
    $current_user = &singleton("current_user");

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

    $label = $this->get_priority_label();

    $reminder = new reminder();
    $reminder->set_value('reminderType', "task");
    $reminder->set_value('reminderLinkID', $this->get_id());
    $reminder->set_value('reminderRecuringInterval', $reminderInterval);
    $reminder->set_value('reminderRecuringValue', $intervalValue);
    $reminder->set_value('reminderSubject', $subject);
    $reminder->set_value('reminderContent', $message);
    $reminder->set_value('reminderAdvNoticeSent', "0");
    if ($this->get_value("dateTargetStart") && $this->get_value("dateTargetStart") != date("Y-m-d")) {
      $date = $this->get_value("dateTargetStart")." 09:00:00";
      $reminder->set_value('reminderAdvNoticeInterval', "Hour");
      $reminder->set_value('reminderAdvNoticeValue', "24");
    } else {
      $date = date("Y-m-d")." 09:00:00";
      $reminder->set_value('reminderAdvNoticeInterval', "No");
      $reminder->set_value('reminderAdvNoticeValue', "0");
    }
    $reminder->set_value('reminderTime', $date);
    $reminder->save();
    // the negative is due to ugly reminder internals
    $reminder->update_recipients(array(-REMINDER_METAPERSON_TASK_ASSIGNEE));
  }

  function is_owner($person = "") {

    if (!is_object($person)) {
      return false;
    }

    // A user owns a task if they 'own' the project
    if ($this->get_id()) {
      // Check for existing task
      has("project") and $p = $this->get_foreign_object("project");
    } else if (has("project") && $_POST["projectID"]) {
      // Or maybe they are creating a new task
      $p = new project();
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
    || (is_object($p) && ($p->has_project_permission($person, array("isManager", "canEditTasks", "timeSheetRecipient"))) 
    || $this->get_value("creatorID") == $person->get_id()
    || $this->get_value("personID") == $person->get_id()
    || $this->get_value("managerID") == $person->get_id()
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
    $q = prepare("SELECT * FROM task WHERE parentTaskID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($db->row()) {
      $t = new task();
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

    $db = new db_alloc();
    if ($projectID) {
      list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();
      // Status may be closed_<something>
      $query = prepare("SELECT taskID AS value, taskName AS label
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
    if (is_object($this)) {
      $interestedPartyOptions = $this->get_all_parties($projectID);
    } else {
      $interestedPartyOptions = task::get_all_parties($projectID);
    }
    foreach ((array)$interestedPartyOptions as $email => $info) {
      if ($info["role"] == "interested" && $info["selected"]) {
        $selected[] = $info["identifier"];
      }
      if ($email) {
        $options[$info["identifier"]] = trim(page::htmlentities(trim($info["name"])." <".$email.">"));
      }
    }
    return "<select name=\"interestedParty[]\" multiple=\"true\">".page::select_options($options,$selected,100,false)."</select>";
  }

  function get_all_parties($projectID="") {
    $db = new db_alloc();
    $interestedPartyOptions = array();
  
    if ($_GET["projectID"]) {
      $projectID = $_GET["projectID"];
    } else if (!$projectID && is_object($this)) {
      $projectID = $this->get_value("projectID");
    }

    if ($projectID) {
      $interestedPartyOptions = project::get_all_parties($projectID,is_object($this) && $this->get_id());
    }

    $extra_interested_parties = config::get_config_item("defaultInterestedParties") or $extra_interested_parties=array();
    foreach ($extra_interested_parties as $name => $email) {
      $interestedPartyOptions[$email]["name"] = $name;
    }
    if (is_object($this)) {
      if ($this->get_value("creatorID")) {
        $p = new person();
        $p->set_id($this->get_value("creatorID"));
        $p->select();
        if ($p->get_value("emailAddress")) {
          $interestedPartyOptions[$p->get_value("emailAddress")]["name"] = $p->get_name();
          $interestedPartyOptions[$p->get_value("emailAddress")]["role"] = "creator";
          $interestedPartyOptions[$p->get_value("emailAddress")]["personID"] = $this->get_value("creatorID");
        }
      }
      if ($this->get_value("personID")) {
        $p = new person();
        $p->set_id($this->get_value("personID"));
        $p->select();
        if ($p->get_value("emailAddress")) {
          $interestedPartyOptions[$p->get_value("emailAddress")]["name"] = $p->get_name();
          $interestedPartyOptions[$p->get_value("emailAddress")]["role"] = "assignee";
          $interestedPartyOptions[$p->get_value("emailAddress")]["personID"] = $this->get_value("personID");
          $interestedPartyOptions[$p->get_value("emailAddress")]["selected"] = 1;
        }
      }
      if ($this->get_value("managerID")) {
        $p = new person();
        $p->set_id($this->get_value("managerID"));
        $p->select();
        if ($p->get_value("emailAddress")) {
          $interestedPartyOptions[$p->get_value("emailAddress")]["name"] = $p->get_name();
          $interestedPartyOptions[$p->get_value("emailAddress")]["role"] = "manager";
          $interestedPartyOptions[$p->get_value("emailAddress")]["personID"] = $this->get_value("managerID");
          $interestedPartyOptions[$p->get_value("emailAddress")]["selected"] = 1;
        }
      }
      $this_id = $this->get_id();
    }
    // return an aggregation of the current task/proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("task",$this_id,$interestedPartyOptions);
    return $interestedPartyOptions;
  }

  function get_personList_dropdown($projectID,$field,$selected=null) {
    $current_user = &singleton("current_user");
 
    $db = new db_alloc();

    if ($this->get_id()) {
      $origval = $this->get_value($field);
    } else {
      $origval = $current_user->get_id();
    }

    $peoplenames = person::get_username_list($origval);

    if ($projectID) {
      if ($field == "managerID") {
        $manager_sql = " AND role.roleHandle in ('isManager','timeSheetRecipient')";
        $managers_only = true;
      }

      $q = prepare("SELECT * 
                      FROM projectPerson 
                 LEFT JOIN person ON person.personID = projectPerson.personID
                 LEFT JOIN role ON role.roleID = projectPerson.roleID
                     WHERE person.personActive = 1 ".$manager_sql."
                       AND projectID = %d
                  ORDER BY firstName, username
                   ",$projectID);
      $db->query($q);
      while ($row = $db->row()) {
        if ($managers_only && $current_user->get_id() == $row["personID"]) {
          $current_user_is_manager = true;
        }
        $ops[$row["personID"]] = $peoplenames[$row["personID"]];
      }

    // Everyone
    } else {
      $ops = $peoplenames;
    }

    $origval and $ops[$origval] = $peoplenames[$origval];

    if ($managers_only && !$current_user_is_manager) {
      unset($ops[$current_user->get_id()]);
    }

    if ($selected === null) {
      $selected = $origval;
    }

    return page::select_options($ops, $selected);
  }

  function get_project_options($projectID="") {
    $projectID or $projectID = $_GET["projectID"];
    // Project Options - Select all projects 
    $db = new db_alloc();
    $query = prepare("SELECT projectID AS value, projectName AS label 
                        FROM project 
                       WHERE projectStatus IN ('Current', 'Potential') OR projectID = %d
                    ORDER BY projectName",$projectID);
    $str = page::select_options($query, $projectID, 60);
    return $str;
  }

  function set_option_tpl_values() {
    // Set template values to provide options for edit selects
    global $TPL;
    $current_user = &singleton("current_user");
    global $isMessage;
    $db = new db_alloc();
    $projectID = $_GET["projectID"] or $projectID = $this->get_value("projectID");
    $TPL["personOptions"] = "<select name=\"personID\"><option value=\"\">".$this->get_personList_dropdown($projectID, "personID")."</select>";
    $TPL["managerPersonOptions"] = "<select name=\"managerID\"><option value=\"\">".$this->get_personList_dropdown($projectID, "managerID")."</select>";
    $TPL["estimatorPersonOptions"] = "<select name=\"estimatorID\"><option value=\"\">".$this->get_personList_dropdown($projectID, "estimatorID")."</select>";

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

    $db->query(prepare("SELECT fullName, emailAddress, clientContactPhone, clientContactMobile
                          FROM interestedParty
                     LEFT JOIN clientContact ON interestedParty.clientContactID = clientContact.clientContactID
                         WHERE entity='task' 
                           AND entityID = %d
                           AND interestedPartyActive = 1
                      ORDER BY fullName",$this->get_id()));
    while ($db->next_record()) {
      $value = interestedParty::get_encoded_interested_party_identifier($db->f("fullName"), $db->f("emailAddress"));
      $phone = array("p"=>$db->f('clientContactPhone'),"m"=>$db->f('clientContactMobile'));
      $TPL["interestedParties"][] = array('key'=>$value, 'name'=>$db->f("fullName"), 'email'=>$db->f("emailAddress"), 'phone'=>$phone);
    }

    $TPL["task_taskStatusLabel"] = $this->get_task_status("label");
    $TPL["task_taskStatusColour"] = $this->get_task_status("colour");
    $TPL["task_taskStatusValue"] = $this->get_value("taskStatus");
    $TPL["task_taskStatusOptions"] = page::select_options($this->get_task_statii_array(true),$this->get_value("taskStatus"));

    // If we're viewing the printer friendly view
    if ($_GET["media"] == "print") {
      // Parent Task label
      $t = new task();
      $t->set_id($this->get_value("parentTaskID"));
      $t->select();
      $TPL["parentTask"] = $t->get_display_value();

      // Task Type label
      $TPL["taskType"] = $this->get_value("taskTypeID"); 

      // Priority
      $TPL["priority"] = $this->get_value("priority");

      // Assignee label
      $p = new person();
      $p->set_id($this->get_value("personID"));
      $p->select();
      $TPL["person"] = $p->get_display_value();
  
      // Project label
      if (has("project")) {
        $p = new project();
        $p->set_id($this->get_value("projectID"));
        $p->select();
        $TPL["projectName"] = $p->get_display_value();
      }
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
    return "<img class=\"taskType\" alt=\"".$this->get_value("taskTypeID")."\" title=\"".$this->get_value("taskTypeID")."\" src=\"".$TPL["url_alloc_images"]."taskType_".strtolower($this->get_value("taskTypeID")).".gif\">";
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
    $sess or $sess = new session();

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

  function get_taskStatus_sql($status) {
    if (!is_array($status)) {
      $status = array($status);
    }
    foreach((array)$status as $s) {
      $lengths[] = strlen($s);
    }
    return sprintf_implode("SUBSTRING(task.taskStatus,1,%d) = '%s'",$lengths,$status);
  }

  function get_list_filter($filter=array()) {
    $current_user = &singleton("current_user");

    // If they want starred, load up the taskID filter element
    if ($filter["starred"]) {
      foreach ((array)$current_user->prefs["stars"]["task"] as $k=>$v) {
        $filter["taskID"][] = $k;
      }
      is_array($filter["taskID"]) or $filter["taskID"][] = -1;
    }

    // Filter on taskID
    $filter["taskID"] and $sql[] = sprintf_implode("task.taskID = %d",$filter["taskID"]);

    // No point continuing if primary key specified, so return
    if ($filter["taskID"]) {
      return $sql;
    }

    // This takes care of projectID singular and plural
    has("project") and $projectIDs = project::get_projectID_sql($filter);
    $projectIDs and $sql["projectIDs"] = $projectIDs;

    // project name or project nick name or project id
    $filter["projectNameMatches"] and $sql[] = sprintf_implode("project.projectName LIKE '%%%s%%'
                                                               OR project.projectShortName LIKE '%%%s%%'
                                                               OR project.projectID = %d"
                                                              ,$filter["projectNameMatches"]
                                                              ,$filter["projectNameMatches"]
                                                              ,$filter["projectNameMatches"]);

    list($ts_open,$ts_pending,$ts_closed) = task::get_task_status_in_set_sql();

    // New Tasks
    if ($filter["taskDate"] == "new") {
      $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 2, date("Y")))." 00:00:00";
      date("D") == "Mon" and $past = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 4, date("Y")))." 00:00:00";
      $sql[] = prepare("(task.taskStatus NOT IN (".$ts_closed.") AND task.dateCreated >= '".$past."')");

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
      $filter["dateOne"] and $sql[] = prepare("(task.dateCreated >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateCreated <= '%s 23:59:59')",$filter["dateTwo"]);

    // Date Assigned
    } else if ($filter["taskDate"] == "d_assigned") {
      $filter["dateOne"] and $sql[] = prepare("(task.dateAssigned >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateAssigned <= '%s 23:59:59')",$filter["dateTwo"]);

    // Date Target Start
    } else if ($filter["taskDate"] == "d_targetStart") {
      $filter["dateOne"] and $sql[] = prepare("(task.dateTargetStart >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateTargetStart <= '%s')",$filter["dateTwo"]);

    // Date Target Completion
    } else if ($filter["taskDate"] == "d_targetCompletion") {
      $filter["dateOne"] and $sql[] = prepare("(task.dateTargetCompletion >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateTargetCompletion <= '%s')",$filter["dateTwo"]);

    // Date Actual Start
    } else if ($filter["taskDate"] == "d_actualStart") {
      $filter["dateOne"] and $sql[] = prepare("(task.dateActualStart >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateActualStart <= '%s')",$filter["dateTwo"]);

    // Date Actual Completion
    } else if ($filter["taskDate"] == "d_actualCompletion") {
      $filter["dateOne"] and $sql[] = prepare("(task.dateActualCompletion >= '%s')",$filter["dateOne"]);
      $filter["dateTwo"] and $sql[] = prepare("(task.dateActualCompletion <= '%s')",$filter["dateTwo"]);
    }

    // Task status filtering
    $filter["taskStatus"] and $sql[] = task::get_taskStatus_sql($filter["taskStatus"]);
    $filter["taskTypeID"] and $sql[] = sprintf_implode("task.taskTypeID = '%s'",$filter["taskTypeID"]);

    // Filter on %taskName%
    $filter["taskName"] and $sql[] = sprintf_implode("task.taskName LIKE '%%%s%%'", $filter["taskName"]);

    // If personID filter
    $filter["personID"]  and $sql["personID"]  = sprintf_implode("task.personID = %d", $filter["personID"]);
    $filter["creatorID"] and $sql["creatorID"] = sprintf_implode("task.creatorID = %d",$filter["creatorID"]);
    $filter["managerID"] and $sql["managerID"] = sprintf_implode("task.managerID = %d",$filter["managerID"]);

    // These filters are for the time sheet dropdown list
    if ($filter["taskTimeSheetStatus"] == "open") {
      unset($sql["personID"]);
      $sql[] = prepare("(task.taskStatus NOT IN (".$ts_closed."))");

    } else if ($filter["taskTimeSheetStatus"] == "mine"){ 
      $current_user = &singleton("current_user");
      unset($sql["personID"]);
      $sql[] = prepare("((task.taskStatus NOT IN (".$ts_closed.")) AND task.personID = %d)",$current_user->get_id());

    } else if ($filter["taskTimeSheetStatus"] == "not_assigned"){ 
      unset($sql["personID"]);
      $sql[] = prepare("((task.taskStatus NOT IN (".$ts_closed.")) AND task.personID != %d)",$filter["personID"]);

    } else if ($filter["taskTimeSheetStatus"] == "recent_closed"){
      unset($sql["personID"]);
      $sql[] = prepare("(task.dateActualCompletion >= DATE_SUB(CURDATE(),INTERVAL 14 DAY))");

    } else if ($filter["taskTimeSheetStatus"] == "all") {
    }

    $filter["parentTaskID"] and $sql["parentTaskID"] = sprintf_implode("IFNULL(task.parentTaskID,0) = %d",$filter["parentTaskID"]);
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
    $current_user = &singleton("current_user");

    /*
     * This is the definitive method of getting a list of tasks that need a sophisticated level of filtering
     *
     */
 
    $filter = task::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    $_FORM["taskView"] or $_FORM["taskView"] = 'prioritised';

    // Zero is a valid limit
    if ($_FORM["limit"] || $_FORM["limit"] === 0 || $_FORM["limit"] === "0") {
      $limit = prepare("limit %d",$_FORM["limit"]); 
    }
    $_FORM["return"] or $_FORM["return"] = "html";

    $_FORM["people_cache"] =& get_cached_table("person");
    $_FORM["timeUnit_cache"] =& get_cached_table("timeUnit");

    if ($_FORM["taskView"] == "prioritised") {
      unset($filter["parentTaskID"]);
      $order_limit = " ORDER BY priorityFactor ".$limit;
    } else {
      $order_limit = " ORDER BY projectName,taskName ".$limit;
    }

    // Get a hierarchical list of tasks
    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }

    $uid = sprintf("%d",$current_user->get_id());
    $spread = sprintf("%d",config::get_config_item("taskPrioritySpread"));
    $scale = sprintf("%d",config::get_config_item("taskPriorityScale"));
    $scale_halved = sprintf("%d",config::get_config_item("taskPriorityScale")/2);

    $q = "SELECT task.*
                ,projectName
                ,projectShortName
                ,clientID
                ,projectPriority
                ,project.currencyTypeID as currency
                ,rate
                ,rateUnitID
                ,GROUP_CONCAT(pendingTask.pendingTaskID) as pendingTaskIDs
                ,priority * POWER(projectPriority, 2) * 
                 IF(task.dateTargetCompletion IS NULL, 
                   8,
                   ATAN(
                        (TO_DAYS(task.dateTargetCompletion) - TO_DAYS(NOW())) / ".$spread."
                       ) / 3.14 * ".$scale." + ".$scale_halved."
                   ) / 10 as priorityFactor
            FROM task
       LEFT JOIN project ON project.projectID = task.projectID
       LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID AND projectPerson.personID = '".$uid."'
       LEFT JOIN pendingTask ON pendingTask.taskID = task.taskID
                 ".$f."
        GROUP BY task.taskID
                 ".$order_limit;
      
    $debug and print "\n<br>QUERY: ".$q;
    $_FORM["debug"] and print "\n<br>QUERY: ".$q;
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->next_record()) {
      $task = new task();
      $task->read_db_record($db);
      $row["taskURL"] = $task->get_url();
      $row["taskName"] = $task->get_name($_FORM);
      $row["taskLink"] = $task->get_task_link($_FORM);
      $row["project_name"] = $row["projectShortName"]  or  $row["project_name"] = $row["projectName"];
      $row["projectPriority"] = $db->f("projectPriority");
      has("project") and $row["projectPriorityLabel"] = project::get_priority_label($db->f("projectPriority"));
      $row["taskTypeImage"] = $task->get_task_image();
      $row["taskStatusLabel"] = $task->get_task_status("label");
      $row["taskStatusColour"] = $task->get_task_status("colour");
      $row["creator_name"] = $_FORM["people_cache"][$row["creatorID"]]["name"];
      $row["manager_name"] = $_FORM["people_cache"][$row["managerID"]]["name"];
      $row["assignee_name"] = $_FORM["people_cache"][$row["personID"]]["name"];
      $row["closer_name"] = $_FORM["people_cache"][$row["closerID"]]["name"];
      $row["estimator_name"] = $_FORM["people_cache"][$row["estimatorID"]]["name"];
      $row["newSubTask"] = $task->get_new_subtask_link();
      $_FORM["showPercent"] and $row["percentComplete"] = $task->get_percentComplete();
      $_FORM["showTimes"] and $row["timeActual"] = $task->get_time_billed()/60/60;
      $row["rate"] = page::money($row["currency"],$row["rate"],"%mo");
      $row["rateUnit"] = $_FORM["timeUnit_cache"][$row["rateUnitID"]]["timeUnitName"];
      $row["priorityFactor"] = sprintf("%0.2f",$row["priorityFactor"]);
      $row["priorityLabel"] = $task->get_priority_label();
      if (!$_FORM["skipObject"]) {
        $_FORM["return"] == "array" and $row["object"] = $task;
      }
      $row["padding"] = $_FORM["padding"];
      $row["taskID"] = $task->get_id();
      $row["parentTaskID"] = $task->get_value("parentTaskID");
      $row["timeLimitLabel"] = $row["timeBestLabel"] = $row["timeWorstLabel"] = $row["timeExpectedLabel"] = $row["timeActualLabel"] = "";
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
      $db = new db_alloc();
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
    $timeExpected = sprintf("%0.2f",$this->get_value("timeLimit")*60*60);

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
                ,"starred"              => "Tasks that you have starred"
                ,"taskName"             => "Task Name (eg: *install*)"
                ,"creatorID"            => "Task creator"
                ,"managerID"            => "The person managing task"
                ,"personID"             => "The person assigned to the task"
                ,"parentTaskID"         => "ID of parent task, all top level tasks have parentTaskID of 0, so this defaults to 0"
                ,"projectType"          => "mine | pm | tsm | pmORtsm | Current | Potential | Archived | all"
                ,"applyFilter"          => "Saves this filter as the persons preference"
                ,"padding"              => "Initial indentation level (useful for byProject lists)"
                ,"url_form_action"      => "The submit action for the filter form"
                ,"hide_field_options"   => "Hide the filter's field's panel."
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
    $current_user = &singleton("current_user");
  
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
    $current_user = &singleton("current_user");

    $db = new db_alloc();

    // Load up the forms action url
    $rtn["url_form_action"] = $_FORM["url_form_action"];
    $rtn["hide_field_options"] = $_FORM["hide_field_options"];

    //time Load up the filter bits
    has("project") and $rtn["projectOptions"] = project::get_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);

    $_FORM["projectType"] and $rtn["projectType_checked"][$_FORM["projectType"]] = " checked"; 
    $ops = array(""=>"Nobody");
    $rtn["personOptions"] = page::select_options($ops+person::get_username_list($_FORM["personID"]), $_FORM["personID"]);
    $rtn["managerPersonOptions"] = page::select_options($ops+person::get_username_list($_FORM["managerID"]), $_FORM["managerID"]);
    $rtn["creatorPersonOptions"] = page::select_options(person::get_username_list($_FORM["creatorID"]), $_FORM["creatorID"]);

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
    $_FORM["showTaskID"]      and $rtn["showTaskID_checked"]      = " checked";
    $_FORM["showManager"]     and $rtn["showManager_checked"]     = " checked";
    $_FORM["showProject"]     and $rtn["showProject_checked"]     = " checked";
    
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

    $task_num_ops = array("" => "All results"
                         ,1     => "1 result"
                         ,2     => "2 results"
                         ,3     => "3 results"
                         ,4     => "4 results"
                         ,5     => "5 results"
                         ,10    => "10 results"
                         ,15    => "15 results"
                         ,20    => "20 results"
                         ,30    => "30 results"
                         ,40    => "40 results"
                         ,50    => "50 results"
                         ,100   => "100 results"
                         ,150   => "150 results"
                         ,200   => "200 results"
                         ,300   => "300 results"
                         ,400   => "400 results"
                         ,500   => "500 results"
                         ,1000  => "1000 results"
                         ,2000  => "2000 results"
                         ,3000  => "3000 results"
                         ,4000  => "4000 results"
                         ,5000  => "5000 results"
                         ,10000 => "10000 results"
                         );
    $rtn["limitOptions"] = page::select_options($task_num_ops, $_FORM["limit"]);


    // unset vars that aren't necessary
    foreach ((array)$_FORM as $k => $v) {
      if (!$v) {
        unset($_FORM[$k]);
      }
    }

    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_changes_list() {
    // This function returns HTML rows for the changes that have been made to this task
    $rows = array();

    $people_cache =& get_cached_table("person");

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

  function update_search_index_doc(&$index) {
    $p =& get_cached_table("person");
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
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$this->get_value("taskName"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('project' ,$projectName,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('pid'     ,$this->get_value("projectID"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$creator_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('closer'  ,$closer_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('assignee',$person_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('manager' ,$manager_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('modifier',$taskModifiedUser_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$this->get_value("taskDescription"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('priority',$this->get_value("priority"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('limit'   ,$this->get_value("timeLimit"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('best'    ,$this->get_value("timeBest"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('worst'   ,$this->get_value("timeWorst"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('expected',$this->get_value("timeExpected"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('type',$this->get_value("taskTypeID"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('status',$status,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCreated',str_replace("-","",$this->get_value("dateCreated")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateAssigned',str_replace("-","",$this->get_value("dateAssigned")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateClosed',str_replace("-","",$this->get_value("dateClosed")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetStart',str_replace("-","",$this->get_value("dateTargetStart")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetCompletion',str_replace("-","",$this->get_value("dateTargetCompletion")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateStart',str_replace("-","",$this->get_value("dateActualStart")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCompletion',str_replace("-","",$this->get_value("dateActualCompletion")),"utf-8"));
    $index->addDocument($doc);
  }

  function add_comment_from_email($email_receive,$ignorethis) {
    return comment::add_comment_from_email($email_receive,$this);
  }

  function get_project_id() {
    return $this->get_value("projectID");
  }

  function can_be_deleted() {
    if (is_object($this) && $this->get_id()) {
      $db = new db_alloc();
      $q = prepare("SELECT can_delete_task(%d) as rtn",$this->get_id());
      $db->query($q);
      $row = $db->row();
      return $row['rtn'];
    }
  }

  function moved_from_pending_to_open() {
    if (is_object($this) && $this->get_id()) {
      $this->select();
      if (substr($this->get_value("taskStatus"),0,4) == 'open') {
        $db = new db_alloc();
        $q = prepare("SELECT *
                        FROM auditItem
                       WHERE entityName = 'task'
                         AND entityID = %d
                         AND changeType = 'FieldChange'
                         AND fieldName = 'taskStatus'
                    ORDER BY dateChanged DESC
                       LIMIT 1",$this->get_id());
        $row = $db->qr($q);
        return substr($row["oldValue"],0,7) == "pending";
      }
    }
  }

  function reopen_pending_task() {
    if (is_object($this) && $this->get_id()) {
      $this->select();
      if (substr($this->get_value("taskStatus"),0,4) == 'pend') {
        $db = new db_alloc();
        $db->query("call change_task_status(%d,'%s')",$this->get_id(),"open_inprogress");
        return true;
      }
    }
  }

  function add_notification($tokenActionID,$maxUsed,$name,$desc,$recipients,$datetime=false) {
    $current_user = &singleton("current_user");
    $token = new token();
    $token->set_value("tokenEntity","task");
    $token->set_value("tokenEntityID",$this->get_id());
    $token->set_value("tokenActionID",$tokenActionID);
    $token->set_value("tokenActive",1);
    $token->set_value("tokenMaxUsed",$maxUsed);
    $token->set_value("tokenCreatedBy",$current_user->get_id());
    $token->set_value("tokenCreatedDate",date("Y-m-d H:i:s"));
    $hash = $token->generate_hash();
    $token->set_value("tokenHash",$hash);
    $token->save();
    if ($token->get_id()) {
      $reminder = new reminder();
      $reminder->set_value("reminderType","task");
      $reminder->set_value("reminderLinkID",$this->get_id());
      $reminder->set_value("reminderHash",$hash);
      $reminder->set_value("reminderSubject",$name);
      $reminder->set_value("reminderContent",$desc);
      if ($datetime) {
        $reminder->set_value("reminderTime",$datetime);
      } 
      $reminder->save();
      if ($reminder->get_id()) {
        foreach ($recipients as $row) {
          $reminderRecipient = new reminderRecipient();
          $reminderRecipient->set_value("reminderID",$reminder->get_id());
          $reminderRecipient->set_value($row["field"],$row["who"]);
          $reminderRecipient->save();
        }
      }
    }
  }


}


?>
