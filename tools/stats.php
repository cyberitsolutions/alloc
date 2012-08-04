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

$db = new db_alloc();
$db_sub = new db_alloc();

$stats = new stats();
$projects = $stats->project_stats();
$tasks = $stats->task_stats();
$comments = $stats->comment_stats();

$TPL["global_projects_current"] = $projects["current"]["total"];
$TPL["global_projects_total"] = $projects["current"]["total"] + $projects["archived"]["total"];
$TPL["global_tasks_current"] = $tasks["current"]["total"];
$TPL["global_tasks_total"] = $tasks["total"]["total"];
$TPL["global_comments_total"] = $comments["total"]["total"];

$TPL["global_graph"] = "<a href=\"".$TPL["url_alloc_statsImage"]."id=total&width=640&multiplier=8&labels=true\"><img alt=\"Global graph\" src=\"".$TPL["url_alloc_statsImage"]."id=total&width=400&multiplier=2\"></a>";

include_template("templates/statsM.tpl");



/* order array of userids from most actions to least actions */
function compare($a, $b) {
  global $projects;
  global $tasks;
  global $comments;

  for ($i = 0, $a_value = 0, $b_value = 0; $a_value == $b_value && $i < 30; $i++) {
    $date = mktime(0, 0, 0, date("m"), date("d") - $i, date("Y"));
    $a_value = $comments["new"][$a][date("Y-m-d", $date)] + $projects["new"][$a][date("Y-m-d", $date)]
      + $tasks["new"][$a][date("Y-m-d", $date)];
    $b_value = $comments["new"][$b][date("Y-m-d", $date)] + $projects["new"][$b][date("Y-m-d", $date)]
      + $tasks["new"][$b][date("Y-m-d", $date)];
  }
  if ($a_value == $b_value)
    return 0;
  return ($a_value < $b_value) ? 1 : -1;
}

function show_users_stats($template) {
  global $TPL;
  global $db;
  $stats = new stats();
  $projects = $stats->project_stats();
  $tasks = $stats->task_stats();
  $comments = $stats->comment_stats();

  $persons = array();

  $query = "SELECT * FROM person ORDER BY username";
  $db->query($query);
  while ($db->next_record()) {
    $person = new person();
    $person->read_db_record($db);
    array_push($persons, $person->get_id());
  }

  usort($persons, "compare");

  for ($i = 0; $i < count($persons); $i++) {
    $person = new person();
    $person->set_id($persons[$i]);
    $person->select();

    $TPL["user_username"] = $person->get_value("username");

    $TPL["user_projects_current"] = $projects["current"][$person->get_id()] + 0;
    $TPL["user_projects_total"] = $projects["current"][$person->get_id()] + $projects["archived"][$person->get_id()];

    $TPL["user_tasks_current"] = $tasks["current"][$person->get_id()] + 0;
    $TPL["user_tasks_total"] = $tasks["current"][$person->get_id()] + $tasks["completed"][$person->get_id()];

    $TPL["user_comments_total"] = $comments["total"][$person->get_id()] + 0;

    $TPL["user_graph"] = "<a href=\"".$TPL["url_alloc_statsImage"]."id=".$person->get_id()."&width=640&multiplier=8&labels=true\">";
    $TPL["user_graph"].= "<img alt=\"User graph\" src=\"".$TPL["url_alloc_statsImage"]."id=".$person->get_id()."&width=400&multiplier=2\"></a>";

    if ($TPL["user_projects_total"] + $TPL["user_tasks_total"] + $TPL["user_comments_total"] > 0) {
      $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
      include_template($template);
    }
  }
}











?>
