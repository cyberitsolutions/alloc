<?php
include("alloc.inc");
include("lib/task_graph.inc.php");

if ($projectID) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();
}

$person = new person;
$person->set_id($personID);
$task_filter = new task_filter();
$task_filter->set_element("person", $person);
if ($projectID) {
  $task_filter->set_element("project", $project);
}


$task_graph = new task_graph;
$task_graph->bottom_margin = 20;
$task_graph->init($task_filter);

$task_graph->draw_grid();

$top_tasks = $person->get_tasks($task_filter);

reset($top_tasks);
while (list(, $task) = each($top_tasks)) {
  $task_graph->draw_task($task, false);
}

$task_graph->draw_milestones();
$task_graph->draw_today();

$task_graph->output();

page_close();



?>
