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

function show_people($template_name) {
  global $person_query, $project, $TPL;

  $db = new db_alloc;
  $db->query($person_query);
  while ($db->next_record()) {
    $person = new person();
    $person->read_db_record($db);
    $person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");

    $options = array();
    $options["taskView"] = "prioritised";
    $options["personID"] = $person->get_id();
    $options["taskStatus"] = "not_completed";
    $options["showHeader"] = true;
    $options["showProject"] = true;

    if (isset($project)) {
      $options["projectIDs"] = array($project->get_id());
    }
    $TPL["person_task_summary"] = task::get_task_list($options);

    include_template($template_name);
  }
}

if ($projectID) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();
  $TPL["navigation_links"] = $project->get_navigation_links();

  $project->check_perm(PERM_PROJECT_VIEW_TASK_ALLOCS);

  $person_query = sprintf("SELECT person.* ")
    .sprintf("FROM person, projectPerson ")
    .sprintf("WHERE person.personID = projectPerson.personID ")
    .sprintf(" AND projectPerson.projectID='%d'", addslashes($project->get_id()));

} else if ($personID) {
  $person_query = sprintf("SELECT * FROM person where personID = ".$personID." ORDER BY username");
} else {
  $person_query = sprintf("SELECT * FROM person ORDER BY username");
}

$TPL["projectID"] = $projectID;
include_template("templates/personGraphsM.tpl");

page_close();



?>
