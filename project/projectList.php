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




$defaults = array("showProjectType"=>true
                 ,"url_form_action"=>$TPL["url_alloc_projectList"]
                 ,"form_name"=>"projectList_filter"
                 );

function show_filter() {
  global $TPL;
  global $defaults;

  $_FORM = project::load_form_data($defaults);
  $arr = project::load_project_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/projectListFilterS.tpl");
}


$_FORM = project::load_form_data($defaults);
$TPL["projectListRows"] = project::get_list($_FORM);
$TPL["_FORM"] = $_FORM;


if (!$current_user->prefs["projectList_filter"]) {
  $TPL["message_help"][] = "

allocPSA helps you manage Projects. This page allows you to see a list of
Projects.

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Projects. 
If you would prefer to create a new Project, click the <b>New Project</b> link
in the top-right hand corner of the box below.";

}





$TPL["main_alloc_title"] = "Project List - ".APPLICATION_NAME;
include_template("templates/projectListM.tpl");

?>
