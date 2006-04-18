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
  global $current_user, $TPL, $personID, $projectName, $projectStatus, $projectType;

  // Construct query based on any filter conditions
  $from = "project LEFT JOIN client ON project.clientID = client.clientID";
  $where = "1=1";
  if ($projectName) {
    $where.= sprintf(" AND projectName LIKE '%%%s%%'", addslashes($projectName));
  }
  if ($personID) {
    $from.= ", projectPerson";
    $where.= sprintf(" AND projectPerson.projectID = project.projectID AND personID=%d", $personID);
  }
  if ($projectStatus) {
    $where.= sprintf(" AND projectStatus = '%s'", $projectStatus);
  }
  if ($projectType) {
    $where.= sprintf(" AND projectType = '%s'", $projectType);
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
  global $current_user, $TPL, $current_user, $personID, $projectName, $projectStatus, $projectType;

  if (!$current_user->is_employee()) {
    return;
  }

  if (have_entity_perm("project", PERM_READ, $current_user, false)) {
    $personDb = new db_alloc;
    $query = "SELECT personID, username FROM person ORDER BY username";
    $personDb->query($query);

    $personSelect= "<select name=\"personID\">";
    $personSelect.= "<option value=\"\"> -- ALL -- ";
    $personSelect.= get_options_from_db($personDb, "username", "personID", $personID);
    $personSelect.= "</select>";
  } else {
    $personSelect = $current_user->get_value("username");
  }
  $TPL["personSelect"] = $personSelect;
  $TPL["projectStatusOptions"] = get_options_from_array(array("Current", "Potential", "Archived"), $projectStatus, false);
  $TPL["projectTypeOptions"] = get_options_from_array(array("Project", "Job", "Contract"), $projectType, false);
  $TPL["projectName"] = $projectName;

  include_template($template_name);
}

  // Set default filter parameters
if (!isset($personID)) {
  $personID = $current_user->get_id();
}
if (!isset($projectStatus)) {
  $projectStatus = "Current";
}

include_template("templates/projectListM.tpl");
page_close();



?>
