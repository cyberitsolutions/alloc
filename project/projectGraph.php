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

$defaults = array("showHeader"=>true
                 ,"showProject"=>true
                 ,"padding"=>1
                 ,"url_form_action"=>$TPL["url_alloc_projectGraph"]
                 ,"form_name"=>"projectSummary_filter"
                 );

function show_filter() {
  global $TPL;
  global $defaults;

  $_FORM = task::load_form_data($defaults);
  $arr = task::load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("../task/templates/taskFilterS.tpl");
}

function show_projects($template_name) {
  global $TPL;
  global $default;
  $_FORM = task::load_form_data($defaults);
  $arr = task::load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
 
  if (is_array($_FORM["projectID"])) {
    $projectIDs = $_FORM["projectID"];
    foreach($projectIDs as $projectID) {
      $project = new project();
      $project->set_id($projectID);
      $project->select();
      $_FORM["projectID"] = array($projectID);
      $TPL["graphTitle"] = urlencode($project->get_value("projectName"));
      $arr = task::load_task_filter($_FORM);
      is_array($arr) and $TPL = array_merge($TPL,$arr);
      include_template($template_name);
    }
  }
}

$TPL["main_alloc_title"] = "Project Graph - ".APPLICATION_NAME;

include_template("templates/projectGraphM.tpl");
?>
