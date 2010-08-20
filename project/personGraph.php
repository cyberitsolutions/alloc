<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

function show_people($template_name) {
  global $person_query, $project, $TPL;

  $db = new db_alloc;
  $db->query($person_query);
  while ($db->next_record()) {
    $person = new person();
    $person->read_db_record($db);
    $person->set_tpl_values(DST_VARIABLE, "person_");
    $TPL["graphTitle"] = urlencode($person->get_username(1));
    include_template($template_name);
  }
}

$projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];

if ($projectID) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();
  $TPL["navigation_links"] = $project->get_navigation_links();

  $project->check_perm(PERM_PROJECT_VIEW_TASK_ALLOCS);

  $person_query = sprintf("SELECT person.* ")
    .sprintf("FROM person, projectPerson ")
    .sprintf("WHERE person.personID = projectPerson.personID ")
    .sprintf(" AND projectPerson.projectID='%d'", db_esc($project->get_id()));

} else if ($_GET["personID"]) {
  $person_query = sprintf("SELECT * FROM person where personID = ".$_GET["personID"]." ORDER BY username");
} else {
  $person_query = sprintf("SELECT * FROM person ORDER BY username");
}

$TPL["projectID"] = $projectID;
$TPL["main_alloc_title"] = "Allocation Graph - ".APPLICATION_NAME;
include_template("templates/personGraphM.tpl");

?>
