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

require_once("../alloc.php");
include("lib/task_graph.inc.php");

if ($_GET["projectID"]) {
  $options["projectIDs"][] = $_GET["projectID"];
}

$options["personID"] = $_GET["personID"];
$options["taskView"] = "prioritised";
$options["return"] = "objects";
$options["taskStatus"] = "not_completed";
$options["showTaskID"] = true;

if ($_GET["graph_type"] == "phases") {
  $options["taskTypeID"] = TT_PHASE;
}

$task_graph = new task_graph;
$task_graph->set_title($_GET["graphTitle"]);
$task_graph->set_width($_GET["graphWidth"]);
$task_graph->bottom_margin = 20;

$tasks = task::get_task_list($options) or $tasks = array();

foreach ($tasks as $task) {
  $objects[$task["taskID"]] = $task["object"];
}

$task_graph->init($objects);
$task_graph->draw_grid();

foreach ($tasks as $task) {
  $task_graph->draw_task($task);
}

$task_graph->draw_milestones();
$task_graph->draw_today();
$task_graph->output();



page_close();
?>
