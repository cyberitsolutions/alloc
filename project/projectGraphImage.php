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
include("lib/task_graph.inc.php");

$current_user = &singleton("current_user");
global $show_weeks;
global $for_home_item;



$options = unserialize(stripslashes($_GET["FORM"]));
$options["return"] = "array";
$options["padding"] = 0;
$options["debug"] = 0;

$tasks = task::get_list($options) or $tasks = array();

foreach ($tasks as $task) {
  $objects[$task["taskID"]] = $task["object"];
}

$task_graph = new task_graph();
$task_graph->set_title($_GET["graphTitle"]);
$task_graph->set_width($_GET["graphWidth"]);
$task_graph->init($objects);
$task_graph->draw_grid();

foreach ($tasks as $task) {
  $task_graph->draw_task($task);
}

$task_graph->draw_milestones();
$task_graph->draw_today();
$task_graph->draw_legend();
$task_graph->output();


?>
