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

require_once("alloc.inc");

if (!$current_user->is_employee()) {
  die("You do not have permission to access time sheets");
}

function show_timeSheets($template_name) {
  global $db, $current_user, $TPL, $timeSheet;

  $query = sprintf("SELECT timeSheet.*, username, projectName ")
    .sprintf("FROM timeSheet ")
    .sprintf("  LEFT JOIN timeSheetItem on timeSheet.timeSheetID = timeSheetItem.timeSheetID ")
    .sprintf("  LEFT JOIN person on timeSheet.personID = person.personID ")
    .sprintf("  LEFT JOIN project on timeSheet.projectID = project.projectID ")
    .sprintf("WHERE 1 ");

  if ($_POST["projectID"]) {
    $query.= sprintf(" AND timeSheet.projectID = '%d'", $_POST["projectID"]);
  }
  if ($_POST["personID"]) {
    $query.= sprintf(" AND timeSheet.personID = '%d'", $_POST["personID"]);
  }
  if ($_POST["status"]) {
    $query.= sprintf(" AND timeSheet.status = '%s'", $_POST["status"]);
  }
  if ($_POST["dateFrom"]) {
    $query.= sprintf(" AND timeSheet.dateFrom >= '%s'", $_POST["dateFrom"]);
  }
  $query.= "GROUP BY timeSheet.timeSheetID";
  $query.= sprintf(" ORDER BY dateFrom,projectName, username, dateFrom ");

  $db = new db_alloc;
  $db->query($query);

  while ($db->next_record()) {
    $timeSheet = new timeSheet;
    $timeSheet->read_db_record($db);
    $timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");
    $timeSheet->load_pay_info();

    $TPL["timeSheet_total_dollars"] = sprintf("%0.2f",$timeSheet->pay_info["total_dollars"]);
    $TPL["summary_unit_totals"] = $timeSheet->pay_info["summary_unit_totals"];
    $TPL["timeSheet_username"] = $db->f("username");
    $TPL["timeSheet_projectName"] = $db->f("projectName");
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    $TPL["grand_total"] += $TPL["timeSheet_total_dollars"];
    include_template($template_name);
  }
  $TPL["grand_total"] = sprintf("%0.2f",$TPL["grand_total"]);
}



if (!$_POST["status"]) {
  $_POST["status"] = "edit";
}

$db = new db_alloc;

// display the list of project name.
$query = sprintf("SELECT * FROM project ORDER by projectName");
$db->query($query);
$project_array = get_array_from_db($db, "projectID", "projectName");
$TPL["show_project_options"] = get_options_from_array($project_array, $_POST["projectID"], true);

// display the list of user name.
if (have_entity_perm("timeSheet", PERM_READ, $current_user, false)) {
  $query = sprintf("SELECT * FROM person ORDER by username");
  $db->query($query);
  $person_array = get_array_from_db($db, "personID", "username");
  $TPL["show_empty_option"] = "<option value=\"\"> -- ALL -- </option>";
} else {
  $person = new person;
  $person->set_id($current_user->get_id());
  $person->select();
  $person_array = array($current_user->get_id()=>$person->get_value("username"));
}

$TPL["show_userID_options"] = get_options_from_array($person_array, $_POST["personID"], true);

// display a list of status
$status_array = array("edit"=>"edit", "manager"=>"manager", "admin"=>"admin", "invoiced"=>"invoiced");
$TPL["show_status_options"] = get_options_from_array($status_array, $_POST["status"], false);

// display the date from filter value
$TPL["dateFrom"] = $_POST["dateFrom"];
$TPL["userID"] = $current_user->get_id();

include_template("templates/timeSheetListM.tpl");
page_close();



?>
