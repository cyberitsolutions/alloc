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


class pendingApprovalTimeSheetListHomeItem extends home_item {

  function __construct() {
    parent::__construct("pending_time_list", "Time Sheets Pending Manager", "time", "pendingApprovalTimeSheetHomeM.tpl", "narrow", 23);
  }

  function visible() {
    $current_user = &singleton("current_user");
    return (isset($current_user) && $current_user->is_employee() && has_pending_timesheet());
  }
  
  function render() {
    return true;
  }

  function show_pending_time_sheets($template_name,$doAdmin=false) {
    show_time_sheets_list_for_classes($template_name,$doAdmin);
  }
}

function show_time_sheets_list_for_classes($template_name,$doAdmin=false) {
  $current_user = &singleton("current_user");
  global $TPL;

  if ($doAdmin) {
    $db = get_pending_admin_timesheet_db();
  } else {
    $db = get_pending_timesheet_db();
  }

  $people =& get_cached_table("person");

  while ($db->next_record()) {
    $timeSheet = new timeSheet();
    $timeSheet->read_db_record($db);
    $timeSheet->set_values();

    unset($date); 
    if ($timeSheet->get_value("status") == "manager") {
      $date = $timeSheet->get_value("dateSubmittedToManager");
    } else if ($timeSheet->get_value("status") == "admin") {
      $date = $timeSheet->get_value("dateSubmittedToAdmin");
    }
    unset($TPL["class"]);

    // older than 10 days 
    if ($date && strtotime($date) < (mktime()-864000)) {
      $TPL["class"] = " class=\"really_overdue\"";

    // older than 5 days
    } else if ($date && strtotime($date) < (mktime()-432000)) {
      $TPL["class"] = " class=\"overdue\"";
    } 
    
    $TPL["date"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\">".$date."</a>";
    $TPL["user"] = $people[$timeSheet->get_value("personID")]["name"];
    $TPL["projectName"] = $db->f("projectName");

    include_template("../time/templates/".$template_name);
  }
}

function get_pending_timesheet_db() {
  /*
   -----------     -----------------     --------
   | project |  <  | projectPerson |  <  | role |
   -----------     -----------------     --------
      /\
   -------------
   | timeSheet |
   -------------
      /\
   -----------------
   | timeSheetItem |
   -----------------
  */

  $current_user = &singleton("current_user");
  $db = new db_alloc();

  // Get all the time sheets that are in status manager, and are the responsibility of only the default manager
  if (in_array($current_user->get_id(), config::get_config_item("defaultTimeSheetManagerList"))) {

    // First get the blacklist of projects that we don't want to include below
    $query = prepare("SELECT timeSheet.*, sum(timeSheetItem.timeSheetItemDuration * timeSheetItem.rate) as total_dollars
                           , COALESCE(projectShortName, projectName) as projectName
                        FROM timeSheet
                             LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
                             LEFT JOIN project on project.projectID = timeSheet.projectID
                       WHERE timeSheet.status='manager'
                         AND timeSheet.projectID NOT IN
                               (SELECT projectID FROM projectPerson WHERE personID != %d AND roleID = 3)
                    GROUP BY timeSheet.timeSheetID 
                    ORDER BY timeSheet.dateSubmittedToManager
                     ",$current_user->get_id());

  // Get all the time sheets that are in status manager, where the currently logged in user is the manager
  } else {
 
    $query = prepare("SELECT timeSheet.*, sum(timeSheetItem.timeSheetItemDuration * timeSheetItem.rate) as total_dollars
                           , COALESCE(projectShortName, projectName) as projectName
                        FROM timeSheet
                             LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
                             LEFT JOIN project on project.projectID = timeSheet.projectID
                             LEFT JOIN projectPerson on project.projectID = projectPerson.projectID 
                             LEFT JOIN role on projectPerson.roleID = role.roleID
                       WHERE projectPerson.personID = %d AND role.roleHandle = 'timeSheetRecipient' AND timeSheet.status='manager'
                    GROUP BY timeSheet.timeSheetID 
                    ORDER BY timeSheet.dateSubmittedToManager"
                     , $current_user->get_id()
                    );

  }

  $db->query($query);
  return $db;
}

function get_pending_admin_timesheet_db() {
  $current_user = &singleton("current_user");
  $db = new db_alloc();
 
  $timeSheetAdminPersonIDs = config::get_config_item("defaultTimeSheetAdminList");

  if (in_array($current_user->get_id(), $timeSheetAdminPersonIDs)) {
    $query = "SELECT timeSheet.*, sum(timeSheetItem.timeSheetItemDuration * timeSheetItem.rate) as total_dollars, COALESCE(projectShortName, projectName) as projectName
                FROM timeSheet
           LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
           LEFT JOIN project on project.projectID = timeSheet.projectID
               WHERE timeSheet.status='admin'
            GROUP BY timeSheet.timeSheetID 
            ORDER BY timeSheet.dateSubmittedToAdmin";

  } 
  $db->query($query);
  return $db;

}

function has_pending_admin_timesheet() {
  $db = get_pending_admin_timesheet_db();
  if ($db->next_record()) {
    return true;
  }
  return false;
}

function has_pending_timesheet() {
  $db = get_pending_timesheet_db();
  if ($db->next_record()) {
    return true;
  }
  return false;
}




?>
