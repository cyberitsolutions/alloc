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


if ($graph_type == "phases") {
  $TPL["alt_graph_link"] = "<a href=\"".$TPL["url_alloc_projectSummary"]."projectID=$projectID&graph_type=all\">Show All Tasks</a>";
} else {
  $TPL["alt_graph_link"] = "<a href=\"".$TPL["url_alloc_projectSummary"]."projectID=$projectID&graph_type=phases\">Only Show Phases</a>";
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
