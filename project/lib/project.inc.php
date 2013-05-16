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

define("PERM_PROJECT_VIEW_TASK_ALLOCS", 256);
define("PERM_PROJECT_ADD_TASKS", 512);

class project extends db_entity {
  public $classname = "project";
  public $data_table = "project";
  public $display_field_name = "projectName";
  public $key_field = "projectID";
  public $data_fields = array("projectName"
                             ,"projectShortName"
                             ,"projectComments"
                             ,"clientID"
                             ,"projectType"
                             ,"projectClientName"
                             ,"projectClientPhone"
                             ,"projectClientMobile"
                             ,"projectClientEMail"
                             ,"projectClientAddress"
                             ,"dateTargetStart"
                             ,"dateTargetCompletion"
                             ,"dateActualStart"
                             ,"dateActualCompletion"
                             ,"projectBudget" => array("type"=>"money")
                             ,"currencyTypeID"
                             ,"projectPriority"
                             ,"projectStatus"
                             ,"cost_centre_tfID"
                             ,"customerBilledDollars" => array("type"=>"money")
                             ,"clientContactID"
                             ,"projectCreatedTime"
                             ,"projectCreatedUser"
                             ,"projectModifiedTime"
                             ,"projectModifiedUser"
                             ,"defaultTaskLimit"
                             ,"defaultTimeSheetRate" => array("type"=>"money")
                             ,"defaultTimeSheetRateUnitID"
                             );

  public $permissions = array(PERM_PROJECT_VIEW_TASK_ALLOCS => "view task allocations"
                             ,PERM_PROJECT_ADD_TASKS => "add tasks");

  function save() {
    global $TPL;
    // The data prior to the save
    $old = $this->all_row_fields;

    // If we're archiving the project, then archive the tasks.
    if ($old["projectStatus"] != "Archived" && $this->get_value("projectStatus") == "Archived") {
      $db = new db_alloc();
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND SUBSTRING(taskStatus,1,6) != 'closed'",$this->get_id());
      $db->query($q);
      while ($row = $db->row()) {
        $task = new task();
        $task->read_row_record($row);
        $task->set_value("taskStatus","closed_archived");
        $task->updateSearchIndexLater = true;
        $task->save();
        $ids.= $commar.$task->get_id();
        $commar = ", ";
      }
      $ids and $TPL["message_good"][] = "All open and pending Tasks (".$ids.") have had their status changed to Closed: Archived.";
    }

    // If we're un-archiving the project, then un-archive the tasks.
    if ($old["projectStatus"] == "Archived" && $this->get_value("projectStatus") != "Archived") {
      $db = new db_alloc();
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskStatus = 'closed_archived'",$this->get_id());
      $id = $db->query($q);
      while ($row = $db->row($id)) {
        $q = prepare("SELECT * FROM auditItem
                       WHERE entityName = 'task'
                         AND entityID = %d
                         AND changeType = 'FieldChange'
                         AND fieldName = 'taskStatus'
                    ORDER BY auditItemID DESC
                       LIMIT 1
                    ",$row["taskID"]);
        $id2 = $db->query($q);
        $r = $db->row($id2);
        $task = new task();
        $task->read_row_record($row);
        $task->set_value("taskStatus",$r["oldValue"]);
        $task->updateSearchIndexLater = true;
        $task->save();
        $ids.= $commar.$task->get_id();
        $commar = ", ";
      }
      $ids and $TPL["message_good"][] = "All archived Tasks (".$ids.") have been set back to their former task status.";
    }

    $TPL["message"] or $TPL["message_good"][] = "Project saved.";
    return parent::save();
  }

  function delete() { 
    $q = prepare("DELETE from projectPerson WHERE projectID = %d",$this->get_id()); 
    $db = new db_alloc();
    $db->query($q);
    return parent::delete();
  }

  function get_url() {
    global $sess;
    $sess or $sess = new session();

    $url = "project/project.php?projectID=".$this->get_id();

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

  function get_name($_FORM=array()) {
    if ($_FORM["showShortProjectLink"] && $this->get_value("projectShortName")) {
      $field = "projectShortName";
    } else {
      $field = "projectName";
    }

    if ($_FORM["return"] == "html") {
      return $this->get_value($field,DST_HTML_DISPLAY);
    } else {
      return $this->get_value($field);
    }
  }

  function get_project_link($_FORM=array()) {
    global $TPL;
    $_FORM["return"] or $_FORM["return"] = "html";
    return "<a href=\"".$TPL["url_alloc_project"]."projectID=".$this->get_id()."\">".$this->get_name($_FORM)."</a>";
  }

  function is_owner($person = "") {
    $current_user = &singleton("current_user");
    $person or $person = $current_user;

    // If brand new record then let it be created.
    if (!$this->get_id())
      return true;

    // Else check that user has isManager or timeSheetRecipient permission for this project
    return is_object($person) && ($person->have_role("manage") || $this->has_project_permission($person, array("isManager","timeSheetRecipient")));
  }

  function has_project_permission($person = "", $permissions = array()) {
    // Check that user has permission for this project
    $current_user = &singleton("current_user");
    $person or $person = $current_user;
    if (is_object($person)) {
      $permissions and $p = " AND ".sprintf_implode("ppr.roleHandle = '%s'",$permissions);

      $query = prepare("SELECT personID, projectID, pp.roleID, ppr.roleName, ppr.roleHandle 
                          FROM projectPerson pp 
                     LEFT JOIN role ppr ON ppr.roleID = pp.roleID 
                         WHERE projectID = '%d' and personID = '%d' ".$p
                      ,$this->get_id(), $person->get_id());
      #echo "<br><br>".$query;

      $db = new db_alloc();
      $db->query($query);
      return $db->next_record();
    }
  }

  function get_timeSheetRecipients() {
    $rows = $this->get_project_people_by_role("timeSheetRecipient");

    // Fallback time sheet manager person
    if (!$rows) {
      $people = config::get_config_item("defaultTimeSheetManagerList");
      $people and $rows = $people;
    }
    return $rows;
  }

  function get_project_people_by_role($role="") {
    $rows = array();
    $q = prepare("SELECT projectPerson.personID as personID
                    FROM projectPerson
               LEFT JOIN role ON projectPerson.roleID = role.roleID 
                   WHERE projectPerson.projectID = %d AND role.roleHandle = '%s'",$this->get_id(),$role);
    $db = new db_alloc();
    $db->query($q);
    while ($db->next_record()) {
      $rows[] = $db->f("personID");
    }
    return $rows;
  }

  function get_project_manager() {
    // Finds either the time sheet recipient or the project manager
    $projectManager = $this->get_project_people_by_role("timeSheetRecipient");
    if(!count($projectManager)) {
      $projectManager = $this->get_project_people_by_role("isManager");
    }
    if(!count($projectManager)) {
      return false;
    } else {
      return $projectManager[0];
    }
  }

  function get_navigation_links($ops=array()) {
    global $taskID;
    global $TPL;
    $current_user = &singleton("current_user");

    // Client 
    if ($this->get_value("clientID")) {  
      $url = $TPL["url_alloc_client"]."clientID=".$this->get_value("clientID");
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Client</a>";
    }

    // Project
    if ($ops["showProject"]) {
      $url = $TPL["url_alloc_project"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Project</a>";
    }

    // Tasks
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_taskList"]."applyFilter=1&amp;taskStatus=open&amp;taskView=byProject&amp;projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Tasks</a>";
    } 

    // Graph
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_projectGraph"]."applyFilter=1&projectID=".$this->get_id()."&taskStatus=open&showTaskID=true";
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Graph</a>";
    }

    // Allocation
    if ($this->have_perm(PERM_PROJECT_VIEW_TASK_ALLOCS)) {
      $url = $TPL["url_alloc_personGraph"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Allocation</a>";
    } 

    // To Time Sheet
    if ($this->have_perm(PERM_PROJECT_ADD_TASKS)) {
      if ($ops["taskID"]) {
        $extra = "&taskID=".$ops["taskID"];
      }
      $url = $TPL["url_alloc_timeSheet"]."newTimeSheet_projectID=".$this->get_id().$extra;
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Time Sheet</a>";
    }

    // New Task
    if ($this->have_perm(PERM_PROJECT_ADD_TASKS)) {
      $url = $TPL["url_alloc_task"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">New Task</a>";
    }

    // Join links up with space
    if (is_array($links)) {
      return implode(" ",$links);
    }
  }

  function get_project_type_query($type="mine",$personID=false,$projectStatus=false) {
    $current_user = &singleton("current_user");
    $type or $type = "mine";
    $personID or $personID = $current_user->get_id();
    $projectStatus and $projectStatus_sql = prepare(" AND project.projectStatus = '%s' ",$projectStatus);

    if ($type == "mine") {
      $q = prepare("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' ".$projectStatus_sql."
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

    } else if ($type == "pm") {
      $q = prepare("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' ".$projectStatus_sql."
                       AND role.roleHandle = 'isManager' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

    } else if ($type == "tsm") {
      $q = prepare("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' ".$projectStatus_sql."
                       AND role.roleHandle = 'timeSheetRecipient' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

   } else if ($type == "pmORtsm") {
      $q = prepare("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' ".$projectStatus_sql."
                       AND (role.roleHandle = 'isManager' or role.roleHandle = 'timeSheetRecipient')
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

    } else if ($type == "all") {
      $q = prepare("SELECT projectID,projectName FROM project ORDER BY projectName");

    } else if ($type) {
      $q = prepare("SELECT projectID,projectName FROM project WHERE project.projectStatus = '%s' ORDER BY projectName",$type);
    }
    return $q;
  }

  function get_list_by_client($clientID=false,$onlymine=false) {
    $current_user = &singleton("current_user");
    $clientID and $options["clientID"] = $clientID;
    $options["projectStatus"] = "Current";
    $options["showProjectType"] = true;
    if ($onlymine) {
      $options["personID"] = $current_user->get_id();
    }
    $ops = project::get_list($options);
    return array_kv($ops,"projectID","label");
  }

  function get_list_dropdown($type="mine",$projectIDs=array()) {
    $options = project::get_list_dropdown_options($type,$projectIDs);
    return "<select name=\"projectID[]\" size=\"9\" style=\"width:275px;\" multiple=\"true\">".$options."</select>";
  }

  function get_list_dropdown_options($type="mine",$projectIDs=array(), $maxlength=35) {
    $db = new db_alloc();
    $q = project::get_project_type_query($type);
    // Project dropdown
    $db->query($q);
    while ($db->next_record()) {
      $ops[$db->f("projectID")] = $db->f("projectName");
    }
    return page::select_options($ops, $projectIDs, $maxlength);
  }

  function get_dropdown_by_client($clientID=false,$onlymine=false) {
    if ($clientID) {
      $ops = "<select id=\"projectID\" name=\"projectID\"><option></option>";
      $o = project::get_list_by_client($clientID,$onlymine);
      is_object($this) && $this->get_id() and $o[$this->get_id()] = $this->get_value("projectName");
      $ops.= page::select_options($o,$this->get_id())."</select>";
    } else {
      $ops = "<select id=\"projectID\" name=\"projectID\"><option></option>";
      $o = project::get_list_by_client(null,$onlymine);
      is_object($this) and $this->get_id() and $o[$this->get_id()] = $this->get_value("projectName");
      $ops.= page::select_options($o,$this->get_id())."</select>";
      #$ops.= project::get_list_dropdown_options("curr",$this->get_id(),100)."</select>";
    }
    return $ops;
  }

  function has_attachment_permission($person) {
    return $this->has_project_permission($person);
  }

  function has_attachment_permission_delete($person) {
    return $this->has_project_permission($person,array("isManager"));
  }

  function get_list_filter($filter=array()) {
    $current_user = &singleton("current_user");

    // If they want starred, load up the projectID filter element
    if ($filter["starred"]) {
      foreach ((array)$current_user->prefs["stars"]["project"] as $k=>$v) {
        $filter["projectID"][] = $k;
      }
      is_array($filter["projectID"]) or $filter["projectID"][] = -1;
    }

    // Filter on projectID
    $filter["projectID"] and $sql[] = sprintf_implode("IFNULL(project.projectID,0) = %d",$filter["projectID"]);

    // No point continuing if primary key specified, so return
    if ($filter["projectID"] || $filter["starred"]) {
      return $sql;
    }

    $filter["clientID"]         and $sql[] = sprintf_implode("IFNULL(project.clientID,0) = %d",$filter["clientID"]);
    $filter["personID"]         and $sql[] = sprintf_implode("IFNULL(projectPerson.personID,0) = %d",$filter["personID"]);
    $filter["projectStatus"]    and $sql[] = sprintf_implode("IFNULL(project.projectStatus,'') = '%s'",$filter["projectStatus"]);
    $filter["projectType"]      and $sql[] = sprintf_implode("IFNULL(project.projectType,0) = %d",$filter["projectType"]);
    $filter["projectName"]      and $sql[] = sprintf_implode("IFNULL(project.projectName,'') LIKE '%%%s%%'",$filter["projectName"]);
    $filter["projectShortName"] and $sql[] = sprintf_implode("IFNULL(project.projectShortName,'') LIKE '%%%s%%'",$filter["projectShortName"]);

    // project name or project nick name or project id
    $filter["projectNameMatches"] and $sql[] = sprintf_implode("project.projectName LIKE '%%%s%%'
                                                               OR project.projectShortName LIKE '%%%s%%'
                                                               OR project.projectID = %d"
                                                              ,$filter["projectNameMatches"]
                                                              ,$filter["projectNameMatches"]
                                                              ,$filter["projectNameMatches"]);
    return $sql;
  }

  function get_list($_FORM) {
    /*
     * This is the definitive method of getting a list of projects that need a sophisticated level of filtering
     *
     */

    global $TPL;
    $filter = project::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";
  
    $_FORM["return"] or $_FORM["return"] = "html";

    if ($_FORM["personID"]) { 
      $from.= " LEFT JOIN projectPerson on projectPerson.projectID = project.projectID ";
    }

    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT project.*, client.* 
            FROM project".$from."
       LEFT JOIN client ON project.clientID = client.clientID 
                 ".$filter." 
        GROUP BY project.projectID 
        ORDER BY projectName";

    // Zero is a valid limit
    if ($_FORM["limit"] || $_FORM["limit"] === 0 || $_FORM["limit"] === "0") {
      $q.= prepare(" LIMIT %d",$_FORM["limit"]);
    }

    $debug and print "Query: ".$q;
    $db = new db_alloc();
    $db->query($q);
    
    while ($row = $db->next_record()) {
      $print = true;
      $p = new project();
      $p->read_db_record($db);
      $row["projectName"] = $p->get_name($_FORM);
      $row["projectLink"] = $p->get_project_link($_FORM);
      $row["navLinks"] = $p->get_navigation_links();
      $label = $p->get_name($_FORM);
      $_FORM["showProjectType"] and $label.= " [".$p->get_project_type()."]";
      $row["label"] = $label;
      $rows[$row["projectID"]] = $row;
    }

    return (array)$rows;
  }

  function get_list_vars() {
   
    return array("projectID"          => "The Project ID"
                ,"projectStatus"      => "Status of the project eg: Current | Potential | Archived"
                ,"clientID"           => "Show projects that are owned by this Client"
                ,"projectType"        => "Type of project eg: Contract | Job | Project | Prepaid"
                ,"personID"           => "Projects that have this person on them."
                ,"projectName"        => "Project name like *something*"
                ,"limit"              => "Limit the number of records returned"
                ,"url_form_action"    => "The submit action for the filter form"
                ,"form_name"          => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"           => "A flag that allows the user to specify that the filter preferences should not be saved this time"
                ,"applyFilter"        => "Saves this filter as the persons preference"
                ,"showProjectType"    => "Show the project type"
                );
  }

  function load_form_data($defaults=array()) {
    $current_user = &singleton("current_user");

    $page_vars = array_keys(project::get_list_vars());
  
    $_FORM = get_all_form_data($page_vars,$defaults);

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["projectStatus"] = "Current";
        $_FORM["personID"] = $current_user->get_id();
      }

    } else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
      $url = $_FORM["url_form_action"];
      unset($_FORM["url_form_action"]);
      $current_user->prefs[$_FORM["form_name"]] = $_FORM;
      $_FORM["url_form_action"] = $url;
    }

    return $_FORM;
  }

  function load_project_filter($_FORM) {

    global $TPL;
    $current_user = &singleton("current_user");

    $personSelect= "<select name=\"personID[]\" multiple=\"true\">";
    $personSelect.= page::select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);
    $personSelect.= "</select>";

    $rtn["personSelect"] = $personSelect;
    $m = new meta("projectStatus");
    $projectStatus_array = $m->get_assoc_array("projectStatusID","projectStatusID");
    $rtn["projectStatusOptions"] = page::select_options($projectStatus_array, $_FORM["projectStatus"]);
    $rtn["projectTypeOptions"] = page::select_options(project::get_project_type_array(), $_FORM["projectType"]);
    $rtn["projectName"] = $_FORM["projectName"];


    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_project_type_array() {
    // optimization
    static $rows;
    if (!$rows) {
      $m = new meta("projectType");
      $rows = $m->get_assoc_array("projectTypeID","projectTypeID");
    }
    return $rows;
  }

  function get_project_type() {
    $ops = $this->get_project_type_array();
    return $ops[$this->get_value("projectType")];
  }

  function get_prepaid_invoice() {
    $db = new db_alloc();

    $q = prepare("SELECT *
                    FROM invoice 
                   WHERE projectID = %d
                     AND invoiceStatus != 'finished' 
                ORDER BY invoiceDateFrom ASC 
                   LIMIT 1"
                 ,$this->get_id());
    $db->query($q);

    if ($row = $db->row()) {
      $invoiceID = $row["invoiceID"];

    } else if ($this->get_value("clientID")) {
      $q = prepare("SELECT *
                      FROM invoice 
                     WHERE clientID = %d 
                       AND (projectID IS NULL OR projectID = 0 OR projectID = '')
                       AND invoiceStatus != 'finished' 
                  ORDER BY invoiceDateFrom ASC 
                     LIMIT 1"
                   ,$this->get_value("clientID"));
      $db->query($q);

      if ($row = $db->row()) {
        $invoiceID = $row["invoiceID"];
      }
    } 
    return $invoiceID;
  }

  function update_search_index_doc(&$index) {
    $p =& get_cached_table("person");
    $projectModifiedUser = $this->get_value("projectModifiedUser");
    $projectModifiedUser_field = $projectModifiedUser." ".$p[$projectModifiedUser]["username"]." ".$p[$projectModifiedUser]["name"];
    $projectName = $this->get_name();
    $projectShortName = $this->get_name(array("showShortProjectLink"=>true));
    $projectShortName && $projectShortName != $projectName and $projectName.= " ".$projectShortName;

    if ($this->get_value("clientID")) {
      $c = new client();
      $c->set_id($this->get_value("clientID"));
      $c->select();
      $clientName = $c->get_name();
    }

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$projectName,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$this->get_value("projectComments"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('cid'     ,$this->get_value("clientID"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('client'  ,$clientName,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('modifier',$projectModifiedUser_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('type'    ,$this->get_value("projectType"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetStart',str_replace("-","",$this->get_value("dateTargetStart")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTargetCompletion',str_replace("-","",$this->get_value("dateTargetCompletion")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateStart',str_replace("-","",$this->get_value("dateActualStart")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCompletion',str_replace("-","",$this->get_value("dateActualCompletion")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('status'   ,$this->get_value("projectStatus"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('priority' ,$this->get_value("projectPriority"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('tf'       ,$this->get_value("cost_centre_tfID"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('billed'   ,$this->get_value("customerBilledDollars"),"utf-8"));
    $index->addDocument($doc);
  }

  function format_client_old() {
    $this->get_value("projectClientName")    and $str.= $this->get_value("projectClientName",DST_HTML_DISPLAY)."<br>";
    $this->get_value("projectClientAddress") and $str.= $this->get_value("projectClientAddress",DST_HTML_DISPLAY)."<br>";
    $this->get_value("projectClientPhone")   and $str.= $this->get_value("projectClientPhone",DST_HTML_DISPLAY)."<br>";
    $this->get_value("projectClientMobile")  and $str.= $this->get_value("projectClientMobile",DST_HTML_DISPLAY)."<br>";
    $this->get_value("projectClientEMail")   and $str.= $this->get_value("projectClientEMail",DST_HTML_DISPLAY)."<br>";
    return $str;
  }

  function get_projectID_sql($filter, $table="project") {
    
    if (!$filter["projectID"] && $filter["projectType"] && $filter["projectType"] != "all") {
      $db = new db_alloc();
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
      return sprintf_implode("(".$table.".projectID = %d)",$filter["projectIDs"]);

    // If there are no projects in $filter["projectIDs"][] and we're attempting the first option..
    } else if ($firstOption) {
      return "(".$table.".projectID = 0)";
    }
  }

  function get_cc_list_select($projectID="") {
    $interestedParty = array();
    $interestedPartyOptions = array();
    
    if (is_object($this)) {
      $interestedPartyOptions = $this->get_all_parties($projectID);
    } else {
      $interestedPartyOptions = project::get_all_parties($projectID);
    }

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
    $str = "<select name=\"interestedParty[]\" multiple=\"true\">".page::select_options($options,$interestedParty,100,false)."</select>";
    return $str;
  }

  function get_all_parties($projectID=false, $task_exists=false) {
    $current_user = &singleton("current_user");
    if (!$projectID && is_object($this)) {
      $projectID = $this->get_id();
    }
    if ($projectID) {

      $extra_interested_parties = config::get_config_item("defaultInterestedParties");
      foreach ((array)$extra_interested_parties as $name => $email) {
        $interestedPartyOptions[$email]["name"] = $name;
      }

      // Get primary client contact from Project page
      $db = new db_alloc();
      $q = prepare("SELECT projectClientName,projectClientEMail FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $interestedPartyOptions[$db->f("projectClientEMail")]["name"] = $db->f("projectClientName");
      $interestedPartyOptions[$db->f("projectClientEMail")]["external"] = "1";
  
      // Get all other client contacts from the Client pages for this Project
      $q = prepare("SELECT clientID FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $clientID = $db->f("clientID");
      if ($clientID) {
        $interestedPartyOptions = array_merge((array)$interestedPartyOptions, (array)client::get_all_parties($clientID));
      }

      // Get all the project people for this tasks project
      $q = prepare("SELECT emailAddress, firstName, surname, person.personID, username
                     FROM projectPerson 
                LEFT JOIN person on projectPerson.personID = person.personID 
                    WHERE projectPerson.projectID = %d AND person.personActive = 1 ",$projectID);
      $db->query($q);
      while ($db->next_record()) {
        unset($name);
        $db->f("firstName") && $db->f("surname") and $name = $db->f("firstName")." ".$db->f("surname");
        $name or $name = $db->f("username");
        $interestedPartyOptions[$db->f("emailAddress")]["name"] = $name;
        $interestedPartyOptions[$db->f("emailAddress")]["personID"] = $db->f("personID");
        $interestedPartyOptions[$db->f("emailAddress")]["internal"] = true; 
      }
    }

    if (is_object($current_user) && $current_user->get_id()) {
      $interestedPartyOptions[$current_user->get_value("emailAddress")]["name"] = $current_user->get_name();
      $interestedPartyOptions[$current_user->get_value("emailAddress")]["personID"] = $current_user->get_id();
    }

    // return an aggregation of the current task/proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("project",$projectID,$interestedPartyOptions,$task_exists);
    return (array)$interestedPartyOptions;
  }

  function get_priority_label($p="") {
    $projectPriorities = config::get_config_item("projectPriorities") or $projectPriorities = array();
    $pp = array();
    foreach($projectPriorities as $key => $arr) {
      $pp[$key] = $arr["label"];
    }
    return $pp[$p];
  }

  function get_list_html($rows=array(),$ops=array()) {
    global $TPL;
    $TPL["projectListRows"] = $rows;
    $TPL["_FORM"] = $ops;
    include_template(dirname(__FILE__)."/../templates/projectListS.tpl");
  }

  function get_changes_list() {
    // This function returns HTML rows for the changes that have been made to this task
    $rows = array();

    $people_cache =& get_cached_table("person");

    $timeUnit = new timeUnit();
    $timeUnits = array_reverse($timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA"),true);

    $options = array("return"       => "array"
                    ,"entityType"   => "project"
                    ,"entityID"     => $this->get_id());
    $changes = auditItem::get_list($options);

    // Insert the creation event into the table to make the history complete.
    if ($this->get_value("projectCreatedTime") && $this->get_value("projectCreatedUser")) {
      $rows []= '<tr><td class="nobr">' . $this->get_value("projectCreatedTime") . '</td><td>The project was created.</td><td>' . page::htmlentities($people_cache[$this->get_value("projectCreatedUser")]["name"]) . "</td></tr>";
    } else {
      $rows []= '<tr><td class="nobr">' . $this->get_value("projectModifiedTime") . '</td><td>The project was last modified.</td><td>' . page::htmlentities($people_cache[$this->get_value("projectModifiedUser")]["name"]) . "</td></tr>";
    }

    // audit these fields:
    // projectName,projectShortName,projectComments,clientID,clientContactID,projectType,dateTargetStart,dateTargetCompletion
    // dateActualStart,dateActualCompletion,projectBudget,currencyTypeID,projectStatus,projectPriority,cost_centre_tfID
    // customerBilledDollars,defaultTaskLimit,defaultTimeSheetRate,defaultTimeSheetRateUnitID 
    foreach($changes as $auditItem) {
      $changeDescription = "";
      $oldValue = $auditItem->get_value('oldValue',DST_HTML_DISPLAY);
      if($auditItem->get_value('changeType') == 'FieldChange') {
        $newValue = page::htmlentities($auditItem->get_new_value());
        switch($auditItem->get_value('fieldName')) {
          case 'projectName':
            $changeDescription = "Project name changed from '$oldValue' to '$newValue'.";
            break;
          case 'projectShortName':
            $changeDescription = "Project nick name changed from '$oldValue' to '$newValue'.";
            break;
          case 'projectComments':
            $changeDescription = "Project description changed. <a class=\"magic\" href=\"#x\" onclick=\"$('#auditItem" . $auditItem->get_id() . "').slideToggle('fast');\">Show</a> <div class=\"hidden\" id=\"auditItem" . $auditItem->get_id() . "\"><div><b>Old Description</b><br>" .$oldValue. "</div><div><b>New Description</b><br>" .$newValue. "</div></div>";
            break;
          case 'clientID':
            $oldClient = new client($oldValue);
            $newClient = new client($newValue);
            is_object($oldClient) and $oldClientLink = $oldClient->get_link();
            is_object($newClient) and $newClientLink = $newClient->get_link();
            $oldClientLink or $oldClientLink = "&lt;empty&gt;";
            $newClientLink or $newClientLink = "&lt;empty&gt;";
            $changeDescription = "Client changed from ".$oldClientLink." to ".$newClientLink.".";
          break;
          case 'clientContactID':
            $oldClientContact = new clientContact($oldValue);
            $newClientContact = new clientContact($newValue);
            is_object($oldClientContact) and $oldClientContactLink = $oldClientContact->get_link();
            is_object($newClientContact) and $newClientContactLink = $newClientContact->get_link();
            $oldClientContactLink or $oldClientContactLink = "&lt;empty&gt;";
            $newClientContactLink or $newClientContactLink = "&lt;empty&gt;";
            $changeDescription = "Client Contact changed from ".$oldClientContactLink." to ".$newClientContactLink.".";
          break;
          case 'projectType':
            $changeDescription = "Project type was changed from " . $oldValue . " to " . $newValue . ".";
          break;
          case 'projectBudget':
            $changeDescription = "Project budget was changed from " . page::money($this->get_value("currencyTypeID"),$oldValue)
                               . " to " . page::money($this->get_value("currencyTypeID"),$newValue) . ".";
          break;
          case 'currencyTypeID':
            $changeDescription = "Project currency was changed from " . $oldValue . " to " . $newValue . ".";
          break;
          case 'projectStatus':
            $changeDescription = "Project status was changed from " . $oldValue . " to " . $newValue . ".";
          break;
          case 'cost_centre_tfID':
            $oldCostCentre = new tf($oldValue);
            $newCostCentre = new tf($newValue);
            is_object($oldCostCentre) and $oldCostCentreLink = $oldCostCentre->get_link();
            is_object($newCostCentre) and $newCostCentreLink = $newCostCentre->get_link();
            $oldCostCentreLink or $oldCostCentreLink = "&lt;empty&gt;";
            $newCostCentreLink or $newCostCentreLink = "&lt;empty&gt;";
            $changeDescription = "Cost centre TF was changed from " . $oldCostCentreLink . " to " . $newCostCentreLink . ".";
          break;
          case 'customerBilledDollars':
            $changeDescription = "Client billing was changed from " . page::money($this->get_value("currencyTypeID"),$oldValue)
                               . " to " . page::money($this->get_value("currencyTypeID"),$newValue) . ".";
          break;
          case 'defaultTaskLimit':
            $changeDescription = "Default task limit was changed from " . $oldValue . " to " . $newValue . ".";
          break;
          case 'defaultTimeSheetRate':
            $changeDescription = "Default time sheet rate was changed from " . page::money($this->get_value("currencyTypeID"),$oldValue)
                               . " to " . page::money($this->get_value("currencyTypeID"),$newValue) . ".";
          break;
          case 'defaultTimeSheetRateUnitID':
            $changeDescription = "Default time sheet rate unit was changed from '" . $timeUnits[$oldValue] ."'"
                               . " to '" . $timeUnits[$newValue] . "'.";
          break;
          case 'projectPriority':
            $priorities = config::get_config_item("projectPriorities");
            $changeDescription = sprintf('Project priority changed from <span style="color: %s;">%s</span>
                                          to <span style="color: %s;">%s</span>.'
                                          , $priorities[$oldValue]["colour"]
                                          , $priorities[$oldValue]["label"]
                                          , $priorities[$newValue]["colour"]
                                          , $priorities[$newValue]["label"]);
          break;
          case 'dateActualCompletion':
          case 'dateActualStart':
          case 'dateTargetStart':
          case 'dateTargetCompletion':
            // these cases are more or less identical
            switch($auditItem->get_value('fieldName')) {
              case 'dateActualCompletion': $fieldDesc = "actual completion date"; break;
              case 'dateActualStart': $fieldDesc = "actual start date"; break;
              case 'dateTargetStart': $fieldDesc = "estimate/target start date"; break;
              case 'dateTargetCompletion': $fieldDesc = "estimate/target completion date"; break;
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


}





?>
