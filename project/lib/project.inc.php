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
                             ,"projectBudget"
                             ,"currencyType"
                             ,"projectPriority"
                             ,"projectStatus"
                             ,"is_agency"
                             ,"cost_centre_tfID"
                             ,"customerBilledDollars"
                             ,"clientContactID"
                             );

  public $permissions = array(PERM_PROJECT_VIEW_TASK_ALLOCS => "view task allocations"
                             ,PERM_PROJECT_ADD_TASKS => "add tasks");

  function delete() { 
    $q = sprintf("DELETE from projectPerson WHERE projectID = %d",$this->get_id()); 
    $db = new db_alloc();
    $db->query($q);
    return parent::delete();
  }

  function get_url() {
    global $sess;
    $sess or $sess = new Session;

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

  function get_project_name($shortest=false) {
    if ($shortest && $this->get_value("projectShortName")) {
      return $this->get_value("projectShortName");
    }
    return $this->get_value("projectName");
  }

  function get_project_link($shortest=false) {
    global $TPL;
    return "<a href=\"".$TPL["url_alloc_project"]."projectID=".$this->get_id()."\">".$this->get_project_name($shortest)."</a>";
  }

  function is_owner($person = "") {
    global $current_user;
    $person or $person = $current_user;

    // If brand new record then let it be created.
    if (!$this->get_id())
      return true;

    // Else check that user has isManager permission for this project
    return $person->have_role("manage") || $this->has_project_permission($person, array("isManager"));
  }

  function has_project_permission($person = "", $permissions = array()) {
    // Check that user has permission for this project
    global $current_user;
    $person or $person = $current_user;
    $permissions and $p = " AND ppr.roleHandle in ('".implode("','",$permissions)."')";

    $query = sprintf("SELECT personID, projectID, pp.roleID, ppr.roleName, ppr.roleHandle 
                        FROM projectPerson pp 
                   LEFT JOIN role ppr ON ppr.roleID = pp.roleID 
                       WHERE projectID = '%d' and personID = '%d' %s"
                    ,$this->get_id(), $person->get_id(), $p);
    #echo "<br><br>".$query;

    $db = new db_alloc;
    $db->query($query);
    return $db->next_record();
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
    $q = sprintf("SELECT projectPerson.personID as personID
                    FROM projectPerson
               LEFT JOIN role ON projectPerson.roleID = role.roleID 
                   WHERE projectPerson.projectID = %d AND role.roleHandle = '%s'",$this->get_id(),db_esc($role));
    $db = new db_alloc;
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
    global $taskID, $TPL, $current_user;

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
    global $current_user;
    $type or $type = "mine";
    $personID or $personID = $current_user->get_id();
    $projectStatus and $projectStatus_sql = sprintf(" AND project.projectStatus = '%s' ",$projectStatus);

    if ($type == "mine") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' %s
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID, $projectStatus_sql);

    } else if ($type == "pm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' %s
                       AND role.roleHandle = 'isManager' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID, $projectStatus_sql);

    } else if ($type == "tsm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' %s
                       AND role.roleHandle = 'timeSheetRecipient' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID, $projectStatus_sql);

   } else if ($type == "pmORtsm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN role ON projectPerson.roleID = role.roleID
                     WHERE projectPerson.personID = '%d' %s
                       AND (role.roleHandle = 'isManager' or role.roleHandle = 'timeSheetRecipient')
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID, $projectStatus_sql);

    } else if ($type == "curr") {
      $q = sprintf("SELECT projectID,projectName FROM project WHERE project.projectStatus = 'current' ORDER BY projectName");

    } else if ($type == "pote") {
      $q = sprintf("SELECT projectID,projectName FROM project WHERE project.projectStatus = 'potential' ORDER BY projectName");

    } else if ($type == "arch") {
      $q = sprintf("SELECT projectID,projectName FROM project WHERE project.projectStatus = 'archived' ORDER BY projectName");

    } else if ($type == "all") {
      $q = sprintf("SELECT projectID,projectName FROM project ORDER BY projectName");
    }
    return $q;
  }

  function get_list_by_client($clientID=false) {
    $clientID and $options["clientID"] = $clientID;
    $options["projectStatus"] = "current";
    $options["showProjectType"] = true;
    $options["return"] = "dropdown_options";
    #global $current_user;
    #$options["personID"] = $current_user->get_id();
    return project::get_list($options);
  }

  function get_list_dropdown($type="mine",$projectIDs=array()) {
    $options = project::get_list_dropdown_options($type,$projectIDs);
    return "<select name=\"projectID[]\" size=\"9\" style=\"width:275px;\" multiple=\"true\">".$options."</select>";
  }

  function get_list_dropdown_options($type="mine",$projectIDs=array(), $maxlength=35) {
    $db = new db_alloc;
    $q = project::get_project_type_query($type);
    // Project dropdown
    $db->query($q);
    while ($db->next_record()) {
      $ops[$db->f("projectID")] = $db->f("projectName");
    }
    return page::select_options($ops, $projectIDs, $maxlength);
  }

  function get_dropdown_by_client($clientID=false) {
    if ($clientID) {
      $ops = "<select size=\"1\" name=\"projectID\"><option></option>";
      $ops.= page::select_options(project::get_list_by_client($clientID),$this->get_id())."</select>";
    } else {
      $ops = "<select size=\"1\" name=\"projectID\"><option></option>";
      $ops.= page::select_options(project::get_list_by_client(),$this->get_id())."</select>";
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

    if ($filter["clientID"]) {
      $sql[] = sprintf("(project.clientID = %d)", $filter["clientID"]);
    }
    if ($filter["personID"]) {
      $sql[] = sprintf("(projectPerson.personID = %d)", $filter["personID"]);
    }
    if ($filter["projectID"]) {  
      $sql[] = sprintf("(project.projectID = %d)", db_esc($filter["projectID"]));
    }
    if ($filter["projectName"]) {
      $sql[] = sprintf("(project.projectName LIKE '%%%s%%')", db_esc($filter["projectName"]));
    }
    if ($filter["projectStatus"]) {
      $sql[] = sprintf("(project.projectStatus = '%s')", db_esc($filter["projectStatus"]));
    }
    if ($filter["projectType"]) {
      $sql[] = sprintf("(project.projectType = '%s')", db_esc($filter["projectType"]));
    }

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

    // A header row
    $summary.= project::get_list_tr_header($_FORM);

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
      $q.= sprintf(" LIMIT %d",$_FORM["limit"]);
    }

    $debug and print "Query: ".$q;
    $db = new db_alloc;
    $db->query($q);
    
    while ($row = $db->next_record()) {
      $print = true;
      $p = new project;
      $p->read_db_record($db);
      $row["projectName"] = $p->get_project_name();
      $row["projectLink"] = $p->get_project_link();
      $row["navLinks"] = $p->get_navigation_links();
      $summary.= project::get_list_tr($row,$_FORM);
      $label = $p->get_project_name();
      $_FORM["showProjectType"] and $label.= " [".$p->get_project_type()."]";
      $summary_ops[$row["projectID"]] = $label; 
      $rows[$row["projectID"]] = $row;
    }

    $rows or $rows = array();
    if ($print && $_FORM["return"] == "array") {
      return $rows;

    } else if ($print && $_FORM["return"] == "html") {
      return "<table class=\"list sortable\">".$summary."</table>";
    
    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Projects Found</b></td></tr></table>";
    }
  }

  function get_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $summary = "\n<tr>";
      $_FORM["showProjectName"]   and $summary.= "\n<th>Project</th>";
      $_FORM["showProjectLink"]   and $summary.= "\n<th>Project</th>";
      $_FORM["showClient"]        and $summary.= "\n<th>Client</th>";
      $_FORM["showProjectType"]   and $summary.= "\n<th>Type</th>";
      $_FORM["showProjectStatus"] and $summary.= "\n<th>Status</th>";
      $_FORM["showNavLinks"]      and $summary.= "\n<th class=\"noprint\">&nbsp;</th>";
      $summary.="\n</tr>";
      return $summary;
    }
  }

  function get_list_tr($row,$_FORM) {
    $summary[] = "<tr>";
    $_FORM["showProjectName"]     and $summary[] = "  <td>".$row["projectName"]."&nbsp;</td>";
    $_FORM["showProjectLink"]     and $summary[] = "  <td>".$row["projectLink"]."&nbsp;</td>";
    $_FORM["showClient"]          and $summary[] = "  <td>".$row["clientName"]."&nbsp;</td>";
    $_FORM["showProjectType"]     and $summary[] = "  <td>".ucwords($row["projectType"])."&nbsp;</td>";
    $_FORM["showProjectStatus"]   and $summary[] = "  <td>".ucwords($row["projectStatus"])."&nbsp;</td>";
    $_FORM["showNavLinks"]        and $summary[] = "  <td class=\"nobr noprint\" align=\"right\">".$row["navLinks"]."&nbsp;</td>";
    $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
  }

  function get_list_vars() {
   
    return array("return"             => "[MANDATORY] eg: array | html | dropdown_options"
                ,"projectID"          => "The Project ID"
                ,"projectStatus"      => "Status of the project eg: current | potential | archived"
                ,"clientID"           => "Show projects that are owned by this Client"
                ,"projectType"        => "Type of project eg: contract | job | project"
                ,"personID"           => "Projects that have this person on them."
                ,"projectName"        => "Project name like *something*"
                ,"limit"              => "Limit the number of records returned"
                ,"url_form_action"    => "The submit action for the filter form"
                ,"form_name"          => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"           => "A flag that allows the user to specify that the filter preferences should not be saved this time"
                ,"applyFilter"        => "Saves this filter as the persons preference"
                ,"showHeader"         => "A descriptive html header row"
                ,"showProjectName"    => "Show the projects name"
                ,"showProjectLink"    => "Show a link to the project"
                ,"showClient"         => "Show the projects client"
                ,"showProjectType"    => "Show the project type"
                ,"showProjectStatus"  => "Show the project status"
                ,"showNavLinks"       => "Show the projects navigation links"
                );
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array_keys(project::get_list_vars());
  
    $_FORM = get_all_form_data($page_vars,$defaults);

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["projectStatus"] = "current";
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

    global $TPL, $current_user;

    $personSelect= "<select name=\"personID\">";
    $personSelect.= "<option value=\"\"> ";
    $personSelect.= page::select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);
    $personSelect.= "</select>";

    $rtn["personSelect"] = $personSelect;
    $rtn["projectStatusOptions"] = page::select_options(array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived"), $_FORM["projectStatus"]);
    $rtn["projectTypeOptions"] = page::select_options(array("project"=>"Project", "job"=>"Job", "contract"=>"Contract"), $_FORM["projectType"]);
    $rtn["projectName"] = $_FORM["projectName"];


    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_project_budget_spent() {
    $db = new db_alloc();
    $q = sprintf("SELECT SUM(amount) AS total FROM transaction WHERE projectID = %d AND status='approved'",$this->get_id());
    $db->query($q);
    $row = $db->row();

    $total_transactions = $row["total"];
    
    $q = sprintf("SELECT SUM(amount) AS total 
                    FROM timeSheet 
               LEFT JOIN transaction on timeSheet.timeSheetID = transaction.timeSheetID 
                     AND transaction.status='approved' 
                   WHERE timeSheet.projectID = %d
                ",$this->get_id());

    $db->query($q);
    $row = $db->row();
    $total_timesheet_transactions = $row["total"];

    return array(sprintf("%0.2f",$total_timesheet_transactions), sprintf("%0.2f",$total_transactions));
  }

  function get_project_type_array() {
    return  array("project"=>"Project", "job"=>"Job", "contract"=>"Contract", "prepaid"=>"Pre-Paid");
  }

  function get_project_type() {
    $ops = $this->get_project_type_array();
    return $ops[$this->get_value("projectType")];
  }

  function get_prepaid_invoice() {
    $db = new db_alloc();

    $q = sprintf("SELECT *
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
      $q = sprintf("SELECT *
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

}





?>
