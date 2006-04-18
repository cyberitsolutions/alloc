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

// initialise the request
require_once("alloc.inc");

  // create an object to hold an announcement
$announcement = new announcement;

  // load the announcement from the database
if (isset($announcementID)) {
  $announcement->set_id($announcementID);
  $announcement->select();
}
  // read announcement variables set by the request
$announcement->read_globals();

  // process submission of the form using the save button
if (isset($save)) {
  $announcement->set_value("personID", $current_user->get_id());
  $announcement->save();

  // process submission of the form using the delete button
} else if (isset($delete)) {
  $announcement->delete();
  page_close();
  header("Location: ".$TPL["url_alloc_announcementList"]);
  exit();
}
  // load data for display in the template
$announcement->set_tpl_values();

  // invoke the page's main template
include_template("templates/announcementM.tpl");


  // Close the request
page_close();



?>
