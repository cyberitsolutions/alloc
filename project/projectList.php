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

function show_project($template_name) {
  global $current_user, $TPL;

  // Construct query based on any filter conditions
  $from = "project LEFT JOIN client ON project.clientID = client.clientID";
  $where = "1=1";
  if ($_POST["projectName"]) {
    $where.= sprintf(" AND projectName LIKE '%%%s%%'", addslashes($_POST["projectName"]));
  }
  if ($_POST["personID"]) {
    $from.= ", projectPerson";
    $where.= sprintf(" AND projectPerson.projectID = project.projectID AND personID=%d", $_POST["personID"]);
  }
  if ($_POST["projectStatus"]) {
    $where.= sprintf(" AND projectStatus = '%s'", $_POST["projectStatus"]);
  }
  if ($_POST["projectType"]) {
    $where.= sprintf(" AND projectType = '%s'", $_POST["projectType"]);
  }
  $query = "SELECT project.*, clientName
             FROM $from 
             WHERE $where
		   GROUP BY projectID
             ORDER BY projectName";

  // Run query and loop through the records
  $db = new db_alloc;
  $db->query($query);
  while ($db->next_record()) {
    $project = new project;
    $project->read_db_record($db);
    $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

    $TPL["clientName"] = $db->f("clientName");
    $TPL["navLinks"] = $project->get_navigation_links();
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";

    include_template($template_name);
  }
}

function show_filter($template_name) {
  global $TPL, $current_user;

  if (!$current_user->is_employee()) {
    return;
  }

  if (have_entity_perm("project", PERM_READ, $current_user, false)) {
    $personDb = new db_alloc;
    $query = "SELECT personID, username FROM person ORDER BY username";
    $personDb->query($query);

    $personSelect= "<select name=\"personID\">";
    $personSelect.= "<option value=\"\"> -- ALL -- ";
    $personSelect.= get_options_from_db($personDb, "username", "personID", $_POST["personID"]);
    $personSelect.= "</select>";
  } else {
    $personSelect = $current_user->get_value("username");
  }
  $TPL["personSelect"] = $personSelect;
  $TPL["projectStatusOptions"] = get_options_from_array(array("Current", "Potential", "Archived"), $_POST["projectStatus"], false);
  $TPL["projectTypeOptions"] = get_options_from_array(array("Project", "Job", "Contract"), $_POST["projectType"], false);
  $TPL["projectName"] = $_POST["projectName"];

  include_template($template_name);
}

// Set default filter parameters
if (!isset($_POST["projectStatus"])) {
  $_POST["projectStatus"] = "Current";
}

include_template("templates/projectListM.tpl");
page_close();



?>
