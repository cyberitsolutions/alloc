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

require_once("../alloc.php");

$absenceID = $_POST["absenceID"] or $absenceID = $_GET["absenceID"];

$returnToParent = $_GET["returnToParent"] or $returnToParent = $_POST["returnToParent"];
$TPL["returnToParent"] = $returnToParent;


$urls["home"] = $TPL["url_alloc_home"];
$urls["calendar"] = $TPL["url_alloc_taskCalendar"]."personID=".$personID;

$absence = new absence;
if ($absenceID) {
  $absence->set_id($absenceID);
  $absence->select();
  $absence->set_tpl_values();
  $personID = $absence->get_value("personID");
}

$person = new person;
$personID = $personID or $personID = $_POST["personID"] or $personID = $_GET["personID"];
if ($personID) {
  $person->set_id($personID);
  $person->select();
}

$db = new db_alloc;

if ($_POST["save"]) {
  // Saving a record
  $absence->read_globals();
  $absence->read_globals("absence_");
  $success = $absence->save();
  if ($success) {
    $url = $TPL["url_alloc_person"]."personID=".$personID;
    $urls[$returnToParent] and $url = $urls[$returnToParent];
    header("Location: $url");
  }
  page_close();
  exit();
} else if ($_POST["delete"]) {
  // Deleting a record
  $absence->read_globals();
  $absence->delete();
  $url = $TPL["url_alloc_person"]."personID=".$personID;
  $urls[$returnToParent] and $url = $urls[$returnToParent];
  header("location: ".$url);
} else if ($absenceID) {
  // Displaying a record
  $absence->set_id($absenceID);
  $absence->select();
} else {
  // create a new record
  $absence->read_globals();
  $absence->read_globals("absence_");
  $absence->set_value("personID", $person->get_id());
}


$absence->set_tpl_values(DST_HTML_ATTRIBUTE, "absence_");
$_GET["date"] and $TPL["absence_dateFrom"] = $_GET["date"];

$TPL["personName"] = $person->get_username(1);

// Set up the options for a list of user.
$query = sprintf("SELECT * FROM person ORDER by username");
$db->query($query);
$person_array = get_array_from_db($db, "personID", "username");
$TPL["person_options"] = get_select_options($person_array, $personID);

// Set up the options for the absence type.
$absenceType_array = array('Annual Leave'=>'Annual Leave'
                          ,'Holiday'     =>'Holiday'
                          ,'Illness'     =>'Illness'
                          ,'Other'       =>'Other');


$TPL["absenceType_options"] = get_select_options($absenceType_array, $absence->get_value("absenceType"));

include_template("templates/absenceFormM.tpl");

page_close();



?>
