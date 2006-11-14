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

global $current_user, $show_weeks, $for_home_item;

$projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];
$project = new project;
$project->set_id($projectID);
$project->check_perm();

if ($for_home_item) {
  $options["personID"] = $current_user->get_id();
}

$options["projectIDs"][] = $projectID;
$options["taskView"] = "prioritised";
$options["return"] = "objects";
$options["taskTypeID"] = unserialize(urldecode(stripslashes($_GET["taskTypeID"])));
$options["taskStatus"] = $_GET["taskStatus"];

$top_tasks = task::get_task_list($options);
$task_graph = new task_graph;
$task_graph->init($options,$top_tasks);
$task_graph->draw_grid();

reset($top_tasks);
while (list(, $task) = each($top_tasks)) {
  $task_graph->draw_task($task);
}

$task_graph->draw_milestones();
$task_graph->draw_today();
$task_graph->draw_legend();

$task_graph->output();

page_close();



?>
