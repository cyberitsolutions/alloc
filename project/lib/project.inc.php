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

define("PERM_PROJECT_VIEW_TASK_ALLOCS", 256);
define("PERM_PROJECT_ADD_TASKS", 512);

class project extends db_entity {
  var $classname = "project";
  var $data_table = "project";
  var $display_field_name = "projectName";

  function project() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("projectID");
    $this->data_fields = array("projectName"=>new db_field("projectName")
                               , "projectShortName"=>new db_field("projectShortName")
                               , "projectComments"=>new db_field("projectComments")
                               , "clientID"=>new db_field("clientID")
                               , "projectType"=>new db_field("projectType")
                               , "projectClientName"=>new db_field("projectClientName")
                               , "projectClientPhone"=>new db_field("projectClientPhone")
                               , "projectClientMobile"=>new db_field("projectClientMobile")
                               , "projectClientEMail"=>new db_field("projectClientEMail")
                               , "projectClientAddress"=>new db_field("projectClientAddress")
                               , "dateTargetStart"=>new db_field("dateTargetStart")
                               , "dateTargetCompletion"=>new db_field("dateTargetCompletion")
                               , "dateActualStart"=>new db_field("dateActualStart")
                               , "dateActualCompletion"=>new db_field("dateActualCompletion")
                               , "projectBudget"=>new db_field("projectBudget")
                               , "currencyType"=>new db_field("currencyType")
                               , "projectPriority"=>new db_field("projectPriority")
                               , "projectStatus"=>new db_field("projectStatus")
                               , "is_agency"=>new db_field("is_agency")
                               , "cost_centre_tfID"=>new db_field("cost_centre_tfID")
                               , "customerBilledDollars"=>new db_field("customerBilledDollars")
                               , "clientContactID"=>new db_field("clientContactID")
      );
    $this->permissions[PERM_PROJECT_VIEW_TASK_ALLOCS] = "View task allocations";
    $this->permissions[PERM_PROJECT_ADD_TASKS] = "Add tasks";
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
    return $this->has_project_permission($person, array("isManager"));
  }

  function has_project_permission($person = "", $permissions = array()) {
    // Check that user has permission for this project
    global $current_user;
    $person or $person = $current_user;
    $permissions and $p = " AND ppr.projectPersonRoleHandle in ('".implode("','",$permissions)."')";

    $query = sprintf("SELECT personID, projectID, pp.projectPersonRoleID, ppr.projectPersonRoleName, ppr.projectPersonRoleHandle 
                        FROM projectPerson pp 
                   LEFT JOIN projectPersonRole ppr ON ppr.projectPersonRoleID = pp.projectPersonRoleID 
                       WHERE projectID = '%d' and personID = '%d' %s"
                    ,$this->get_id(), $person->get_id(), $p);
    #echo "<br><br>".$query;

    $db = new db_alloc;
    $db->query($query);
    return $db->next_record();
  }

  function get_timeSheetRecipients() {
    $rows = array();
    $q = sprintf("SELECT projectPerson.personID as personID
                    FROM projectPerson
               LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID 
                   WHERE projectPerson.projectID = %d AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient'",$this->get_id());
    $db = new db_alloc;
    $db->query($q);
    while ($db->next_record()) {
      $rows[] = $db->f("personID");
    }
    return $rows;
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
      $url = $TPL["url_alloc_taskList"]."applyFilter=1&amp;taskStatus=not_completed&amp;taskView=byProject&amp;projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr noprint\">Tasks</a>";
    } 

    // Graph
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_projectGraph"]."applyFilter=1&projectID=".$this->get_id()."&taskStatus=not_completed&showTaskID=true";
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
      return implode("&nbsp;&nbsp;",$links);
    }
  }

  function get_project_type_query($type="mine",$personID=false) {
    global $current_user;
    $type or $type = "mine";
    $personID or $personID = $current_user->get_id();

    


    if ($type == "mine") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

    } else if ($type == "pm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                       AND projectPersonRole.projectPersonRoleHandle = 'isManager' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

    } else if ($type == "tsm") {
      $q = sprintf("SELECT project.projectID, project.projectName
                      FROM project
                 LEFT JOIN projectPerson ON project.projectID = projectPerson.projectID
                 LEFT JOIN projectPersonRole ON projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = '%d' AND project.projectStatus = 'current'
                       AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient' 
                  GROUP BY projectID 
                  ORDER BY project.projectName"
                  ,$personID);

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

  function get_project_list_by_client($clientID) {
    $options["clientID"] = $clientID;
    $options["projectStatus"] = "current";
    $options["return"] = "dropdown_options";
    #global $current_user;
    #$options["personID"] = $current_user->get_id();
    return project::get_project_list($options);
  }

  function get_project_list_dropdown($type="mine",$projectIDs=array()) {
    $db = new db_alloc;
    $q = project::get_project_type_query($type);
    // Project dropdown
    $db->query($q);
    while ($db->next_record()) {
      $ops[$db->f("projectID")] = $db->f("projectName");
    }

    $options = get_select_options($ops, $projectIDs, 35);

    return "<select name=\"projectID[]\" size=\"10\" style=\"width:275px;\" multiple=\"true\">".$options."</select>";
  }

  function has_attachment_permission($person) {
    return $this->has_project_permission($person);
  }

  function has_attachment_permission_delete($person) {
    return $this->has_project_permission($person,array("isManager"));
  }

  function get_project_list_filter($filter=array()) {

    if ($filter["clientID"]) {
      $sql[] = sprintf("(project.clientID = %d)", $filter["clientID"]);
    }
    if ($filter["personID"]) {
      $sql[] = sprintf("(projectPerson.personID=%d)", $filter["personID"]);
    }
    if ($filter["projectName"]) {
      $sql[] = sprintf("(projectName LIKE '%%%s%%')", db_esc($filter["projectName"]));
    }
    if ($filter["projectStatus"]) {
      $sql[] = sprintf("(projectStatus = '%s')", db_esc($filter["projectStatus"]));
    }
    if ($filter["projectType"]) {
      $sql[] = sprintf("(projectType = '%s')", db_esc($filter["projectType"]));
    }

    return $sql;
  }

  function get_project_list($_FORM) {
    /*
     * This is the definitive method of getting a list of projects that need a sophisticated level of filtering
     * 
     * Display Options:
     *  showHeader
     *  showProjectName
     *  showProjectLink
     *  showClient
     *  showProjectType
     *  showProjectStatus
     *  showNavLinks
     *  
     * Filter Options:
     *   projectStatus
     *   clientID
     *   projectType
     *   personID
     *   projectName
     *   limit
     *
     */

    global $TPL;
    $filter = project::get_project_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";
  
    $_FORM["return"] or $_FORM["return"] = "html";

    // A header row
    $summary.= project::get_project_list_tr_header($_FORM);

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

    isset($_FORM["limit"]) and $q.= sprintf(" LIMIT %d",$_FORM["limit"]);

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
      $summary.= project::get_project_list_tr($row,$_FORM);
      $summary_ops[$row["projectID"]] = $p->get_project_name(); 
    }

    if ($print && $_FORM["return"] == "html") {
      return $TPL["table_list"].$summary."</table>";
    
    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Projects Found</b></td></tr></table>";
    }
  }

  function get_project_list_tr_header($_FORM) {
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

  function get_project_list_tr($row,$_FORM) {

    static $odd_even;
    $odd_even = $odd_even == "even" ? "odd" : "even";

    $summary[] = "<tr class=\"".$odd_even."\">";
    $_FORM["showProjectName"]     and $summary[] = "  <td>".$row["projectName"]."&nbsp;</td>";
    $_FORM["showProjectLink"]     and $summary[] = "  <td>".$row["projectLink"]."&nbsp;</td>";
    $_FORM["showClient"]          and $summary[] = "  <td>".$row["clientName"]."&nbsp;</td>";
    $_FORM["showProjectType"]     and $summary[] = "  <td>".ucwords($row["projectType"])."&nbsp;</td>";
    $_FORM["showProjectStatus"]   and $summary[] = "  <td>".ucwords($row["projectStatus"])."&nbsp;</td>";
    $_FORM["showNavLinks"]        and $summary[] = "  <td class=\"nobr noprint\" align=\"right\" width=\"1%\">".$row["navLinks"]."&nbsp;</td>";
    $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array("showHeader"
                      ,"showProjectName"
                      ,"showProjectLink"
                      ,"showClient"
                      ,"showProjectType"
                      ,"showProjectStatus"
                      ,"showNavLinks"

                      ,"projectStatus"
                      ,"clientID"
                      ,"projectType"
                      ,"personID"
                      ,"projectName"

                      ,"url_form_action"
                      ,"form_name"
                      ,"dontSave"
                      ,"applyFilter"
                      );

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
    $personSelect.= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);
    $personSelect.= "</select>";

    $rtn["personSelect"] = $personSelect;
    $rtn["projectStatusOptions"] = get_options_from_array(array("Current", "Potential", "Archived"), $_FORM["projectStatus"], false);
    $rtn["projectTypeOptions"] = get_options_from_array(array("Project", "Job", "Contract"), $_FORM["projectType"], false);
    $rtn["projectName"] = $_FORM["projectName"];


    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }


}





?>
