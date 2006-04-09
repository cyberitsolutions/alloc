<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

// Create an object to hold a taskCommentTemplate
$taskCommentTemplate = new taskCommentTemplate();

// Load the taskCommentTemplate from the database
if (isset($taskCommentTemplate)){
 $taskCommentTemplate->set_id($taskCommentTemplateID);
 $taskCommentTemplate->select();
}

// Process submission of the form using the save button
if (isset($_POST["save"])) {
  $taskCommentTemplate->read_globals();
  $taskCommentTemplate->save();
  header("Location: ".$TPL["url_alloc_taskCommentTemplateList"]);

// Process submission of the form using the delete button
} else if (isset($_POST["delete"])) {
  header("Location: ".$TPL["url_alloc_taskCommentTemplateList"]);
  $taskCommentTemplate->delete();
  page_close();
  exit();
}
// Load data for display in the template
$taskCommentTemplate->set_tpl_values();

// Invoke the page's main template
include_template("templates/taskCommentTemplateM.tpl");

// Close the request
page_close();
				 
?>

