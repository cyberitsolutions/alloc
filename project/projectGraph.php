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
include("lib/task_graph.inc.php");

global $current_user, $show_weeks, $for_home_item, $projectID;

$project = new project;
$project->set_id($projectID);
$project->check_perm();
$task_filter = $project->get_task_filter($show_weeks);

if ($for_home_item) {
  $task_filter->set_element("person", $current_user);
  $task_filter->set_element("in_progress", true);
  $task_filter->set_element("weeks_to_show", false);
  $task_filter->set_element("completed", false);
}


$task_graph = new task_graph;
$task_graph->init($task_filter);

$task_graph->draw_grid();

$top_tasks = $project->get_top_tasks($task_graph->task_filter);

reset($top_tasks);
while (list(, $task) = each($top_tasks)) {
  $task_graph->draw_task($task);
}

$task_graph->draw_milestones();
$task_graph->draw_today();

        // Legend
$task_graph->draw_legend();

$task_graph->output();

page_close();



?>
