<?php
require_once("alloc.inc");


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

if ($graph_type == "phases") {
  $options["taskTypeID"][] = TT_PHASE;
}
$options["taskView"] = "byProject";
$options["projectIDs"][] = $project->get_id();
$TPL["task_summary"] = task::get_task_list($options);

$TPL["projectID"] = $projectID;
include_template("templates/projectSummaryM.tpl");

page_close();



?>
