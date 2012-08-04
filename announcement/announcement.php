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

// initialise the request
require_once("../alloc.php");

// create an object to hold an announcement
$announcement = new announcement();

// load the announcement from the database
$announcementID = $_POST["announcementID"] or $announcementID = $_GET["announcementID"];
if ($announcementID) {
  $announcement->set_id($announcementID);
  $announcement->select();
}

// read announcement variables set by the request
$announcement->read_globals();

// process submission of the form using the save button
if ($_POST["save"]) {
  $announcement->set_value("personID", $current_user->get_id());
  $announcement->save();
  alloc_redirect($TPL["url_alloc_announcementList"]);

// process submission of the form using the delete button
} else if ($_POST["delete"]) {
  $announcement->delete();
  alloc_redirect($TPL["url_alloc_announcementList"]);
  exit();
}

// load data for display in the template
$announcement->set_values();

$TPL["main_alloc_title"] = "Edit Announcement - ".APPLICATION_NAME;

// invoke the page's main template
include_template("templates/announcementM.tpl");



?>
