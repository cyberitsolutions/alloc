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

$defaults = array("showHeader"=>true
                 ,"showProject"=>true
                 ,"padding"=>1
                 ,"url_form_action"=>$TPL["url_alloc_projectGraph"]
                 ,"form_name"=>"projectSummary_filter"
                 );

function show_filter() {
  global $TPL,$defaults;

  $_FORM = load_form_data($defaults);
  $arr = load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("../task/templates/taskFilterS.tpl");
}

function show_projects($template_name) {
  global $TPL, $default;
  $_FORM = load_form_data($defaults);
  $arr = load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
 
  if (is_array($_FORM["projectID"])) {
    $projectIDs = $_FORM["projectID"];
    foreach($projectIDs as $projectID) {
      $project = new project;
      $project->set_id($projectID);
      $project->select();
      $_FORM["projectID"] = array($projectID);
      $TPL["graphTitle"] = urlencode($project->get_value("projectName"));
      $arr = load_task_filter($_FORM);
      is_array($arr) and $TPL = array_merge($TPL,$arr);
      include_template($template_name);
    }
  }
}



include_template("templates/projectGraphM.tpl");
page_close();
?>
