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

class project extends db_entity
{
  var $classname = "project";
  var $data_table = "project";
  var $display_field_name = "projectName";

  function project() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectID");
    $this->data_fields = array("projectName"=>new db_text_field("projectName")
                               , "projectShortName"=>new db_text_field("projectShortName")
                               , "projectComments"=>new db_text_field("projectComments")
                               , "clientID"=>new db_text_field("clientID")
                               , "projectType"=>new db_text_field("projectType")
                               , "projectClientName"=>new db_text_field("projectClientName")
                               , "projectClientPhone"=>new db_text_field("projectClientPhone")
                               , "projectClientMobile"=>new db_text_field("projectClientMobile")
                               , "projectClientEMail"=>new db_text_field("projectClientEMail")
                               , "projectClientAddress"=>new db_text_field("projectClientAddress")
                               , "dateTargetStart"=>new db_text_field("dateTargetStart")
                               , "dateTargetCompletion"=>new db_text_field("dateTargetCompletion")
                               , "dateActualStart"=>new db_text_field("dateActualStart")
                               , "dateActualCompletion"=>new db_text_field("dateActualCompletion")
                               , "projectBudget"=>new db_text_field("projectBudget")
                               , "currencyType"=>new db_text_field("currencyType")
                               , "projectPriority"=>new db_text_field("projectPriority")
                               , "projectStatus"=>new db_text_field("projectStatus")
                               , "is_agency"=>new db_text_field("is_agency")
                               , "cost_centre_tfID"=>new db_text_field("cost_centre_tfID")
                               , "customerBilledDollars"=>new db_text_field("customerBilledDollars")
                               , "clientContactID"=>new db_text_field("clientContactID")
      );
    $this->permissions[PERM_PROJECT_VIEW_TASK_ALLOCS] = "View task allocations";
    $this->permissions[PERM_PROJECT_ADD_TASKS] = "Add tasks";
  }

  function get_url() {
    $sess = new Session;
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

  function get_navigation_links() {
    global $taskID, $TPL, $current_user;
 
    // Client 
    if ($this->get_value("clientID")) {  
      $url = $TPL["url_alloc_client"]."clientID=".$this->get_value("clientID");
      $links[] = "<a href=\"$url\" class=\"nobr\">Client</a>";
    }

    // Tasks
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_taskSummary"]."applyFilter=1&amp;taskStatus=not_completed&amp;taskView=byProject&amp;projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr\">Tasks</a>";
    } 

    // Graph
    if ($this->have_perm()) {
      $url = $TPL["url_alloc_projectSummary"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr\">Graph</a>";
    }

    // Allocation
    if ($this->have_perm(PERM_PROJECT_VIEW_TASK_ALLOCS)) {
      $url = $TPL["url_alloc_personGraphs"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr\">Allocation</a>";
    } 

    // To Time Sheet
    if ($this->have_perm(PERM_PROJECT_ADD_TASKS)) {
      $url = $TPL["url_alloc_timeSheet"]."newTimeSheet_projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr\">Time Sheet</a>";
    }

    // New Task
    if ($this->have_perm(PERM_PROJECT_ADD_TASKS)) {
      $url = $TPL["url_alloc_task"]."projectID=".$this->get_id();
      $links[] = "<a href=\"$url\" class=\"nobr\">New Task</a>";
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

}





?>
