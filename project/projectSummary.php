<?php
include("alloc.inc");

function show_task_summary() {
  global $project, $task_filter;
  echo $project->get_task_summary($task_filter);
}

if ($graph_type == "phases") {
  $TPL["alt_graph_link"] = "<a href=\"".$TPL["url_alloc_projectSummary"]."&projectID=$projectID&graph_type=all\">Show All Tasks</a>";
} else {
  $TPL["alt_graph_link"] = "<a href=\"".$TPL["url_alloc_projectSummary"]."&projectID=$projectID&graph_type=phases\">Only Show Phases</a>";
}

$TPL["graph_type"] = $graph_type;

$project = new project;
$project->set_id($projectID);
$project->check_perm();
$project->select();
$TPL["navigation_links"] = $project->get_navigation_links();

$task_filter = new task_filter();
if ($graph_type == "phases") {
  $task_filter->set_element("phase", true);
}

$TPL["projectID"] = $projectID;
include_template("templates/projectSummaryM.tpl");

page_close();



?>
