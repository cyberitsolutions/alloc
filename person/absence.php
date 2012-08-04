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

require_once("../alloc.php");

$absenceID = $_POST["absenceID"] or $absenceID = $_GET["absenceID"];

$returnToParent = $_GET["returnToParent"] or $returnToParent = $_POST["returnToParent"];
$TPL["returnToParent"] = $returnToParent;


$urls["home"] = $TPL["url_alloc_home"];
$urls["calendar"] = $TPL["url_alloc_taskCalendar"]."personID=".$personID;

$absence = new absence();
if ($absenceID) {
  $absence->set_id($absenceID);
  $absence->select();
  $absence->set_values();
  $personID = $absence->get_value("personID");
}

$person = new person();
$personID = $personID or $personID = $_POST["personID"] or $personID = $_GET["personID"];
if ($personID) {
  $person->set_id($personID);
  $person->select();
}

$db = new db_alloc();

if ($_POST["save"]) {
  // Saving a record
  $absence->read_globals();
  $absence->read_globals("absence_");
  $absence->set_value("contactDetails",rtrim($absence->get_value("contactDetails")));
  $success = $absence->save();
  if ($success) {
    $url = $TPL["url_alloc_person"]."personID=".$personID;
    $urls[$returnToParent] and $url = $urls[$returnToParent];
    alloc_redirect($url);
  }
} else if ($_POST["delete"]) {
  // Deleting a record
  $absence->read_globals();
  $absence->delete();
  $url = $TPL["url_alloc_person"]."personID=".$personID;
  $urls[$returnToParent] and $url = $urls[$returnToParent];
  alloc_redirect($url);
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


$absence->set_values("absence_");
$_GET["date"] and $TPL["absence_dateFrom"] = $_GET["date"];

$TPL["personName"] = $person->get_name();

// Set up the options for the absence type.
$absenceType_array = array('Annual Leave'=>'Annual Leave'
                          ,'Holiday'     =>'Holiday'
                          ,'Illness'     =>'Illness'
                          ,'Other'       =>'Other');


$TPL["absenceType_options"] = page::select_options($absenceType_array, $absence->get_value("absenceType"));

$TPL["main_alloc_title"] = "Absence Form - ".APPLICATION_NAME;

include_template("templates/absenceFormM.tpl");


?>
