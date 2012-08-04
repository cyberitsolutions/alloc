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

function show_people($template_name) {
  global $person_query;
  global $project;
  global $TPL;

  $db = new db_alloc();
  $db->query($person_query);
  while ($db->next_record()) {
    $person = new person();
    $person->read_db_record($db);
    $person->set_values("person_");
    $TPL["graphTitle"] = urlencode($person->get_name());
    include_template($template_name);
  }
}

$projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];

if ($projectID) {
  $project = new project();
  $project->set_id($projectID);
  $project->select();
  $TPL["navigation_links"] = $project->get_navigation_links();
  $person_query = prepare("SELECT person.*
                             FROM person, projectPerson
                            WHERE person.personID = projectPerson.personID
                              AND projectPerson.projectID=%d", $project->get_id());

} else if ($_GET["personID"]) {
  $person_query = prepare("SELECT * FROM person WHERE personID = %d ORDER BY username",$_GET["personID"]);
} else {
  $person_query = prepare("SELECT * FROM person ORDER BY username");
}

$TPL["projectID"] = $projectID;
$TPL["main_alloc_title"] = "Allocation Graph - ".APPLICATION_NAME;
include_template("templates/personGraphM.tpl");

?>
