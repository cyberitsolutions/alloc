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

$page_vars = array("taskTypeID"
                  ,"taskStatus"
                  ,"projectID"
                  );

$_FORM = get_all_form_data($page_vars);

$taskType = new taskType;
$TPL["taskTypeOptions"] = "\n<option value=\"\"> ";
$TPL["taskTypeOptions"].= $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);

$taskStatii = task::get_task_statii_array();
$TPL["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);


$TPL["graph_type"] = $_GET["graph_type"];
$TPL["taskTypeID_url"] = urlencode(serialize($_FORM["taskTypeID"]));
$TPL["taskStatus"] = $_FORM["taskStatus"];

$project = new project;
$project->set_id($_FORM["projectID"]);
$project->check_perm();
$project->select();
$TPL["navigation_links"] = $project->get_navigation_links();

$options["taskView"] = "prioritised";
$options["projectIDs"][] = $project->get_id();
$options = array_merge($_FORM,$options);
$TPL["task_summary"] = task::get_task_list($options);
$TPL["projectID"] = $_FORM["projectID"];
include_template("templates/projectSummaryM.tpl");

page_close();



?>
