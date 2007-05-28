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


class pendingAdminApprovalTimeSheetListHomeItem extends home_item {
  function pendingAdminApprovalTimeSheetListHomeItem() {
    home_item::home_item("pending_admin_time_list", "Time Sheets Pending Admin Approval", "time", "pendingAdminApprovalTimeSheetHomeM.tpl", "narrow", 22);
  }
  function show_pending_time_sheets($template_name,$doAdmin=false) {
    show_time_sheets_list_for_classes($template_name,$doAdmin);
  }
}

class pendingApprovalTimeSheetListHomeItem extends home_item {
  function pendingApprovalTimeSheetListHomeItem() {
    home_item::home_item("pending_time_list", "Time Sheets Pending Manager Approval", "time", "pendingApprovalTimeSheetHomeM.tpl", "narrow", 23);
  }
  function show_pending_time_sheets($template_name,$doAdmin=false) {
    show_time_sheets_list_for_classes($template_name,$doAdmin);
  }
}

function show_time_sheets_list_for_classes($template_name,$doAdmin=false) {
  global $current_user, $TPL;

  if ($doAdmin) {
    $db = get_pending_admin_timesheet_db();
  } else {
    $db = get_pending_timesheet_db();
  }

  $people = get_cached_table("person");

  while ($db->next_record()) {
    $timeSheet = new timeSheet;
    $timeSheet->read_db_record($db);
    $timeSheet->set_tpl_values();

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
   -----------     -----------------     ---------------------
   | project |  <  | projectPerson |  <  | projectPersonRole |
   -----------     -----------------     ---------------------
      /\
   -------------
   | timeSheet |
   -------------
      /\
   -----------------
   | timeSheetItem |
   -----------------
  */

  global $current_user;
  $db = new db_alloc;
 
  $query = sprintf("SELECT timeSheet.*, sum(timeSheetItem.timeSheetItemDuration * timeSheetItem.rate) as total_dollars, COALESCE(projectShortName, projectName) as projectName
                      FROM timeSheet
                           LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
                           LEFT JOIN project on project.projectID = timeSheet.projectID
                           LEFT JOIN projectPerson on project.projectID = projectPerson.projectID 
                           LEFT JOIN projectPersonRole on projectPerson.projectPersonRoleID = projectPersonRole.projectPersonRoleID
                     WHERE projectPerson.personID = %d AND projectPersonRole.projectPersonRoleHandle = 'timeSheetRecipient' AND timeSheet.status='manager'
                  GROUP BY timeSheet.timeSheetID 
                  ORDER BY timeSheet.dateSubmittedToManager"
                   , $current_user->get_id()
                   , $current_user->get_id()
                   , $timeSheetAdminEmailPersonID
                  );

  $db->query($query);
  return $db;
}


function get_pending_admin_timesheet_db() {
  global $current_user;
  $db = new db_alloc;
 
  $c = new config; 
  $timeSheetAdminEmailPersonID = $c->get_config_item("timeSheetAdminEmail");

  if ($timeSheetAdminEmailPersonID == $current_user->get_id()) {
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
