<?php
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
