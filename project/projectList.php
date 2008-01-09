<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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
define("PAGE_IS_PRINTABLE",1);




$defaults = array("showHeader"=>true
                 ,"showProjectLink"=>true
                 ,"showClient"=>true
                 ,"showProjectType"=>true
                 ,"showProjectStatus"=>true
                 ,"showNavLinks"=>true
                 ,"url_form_action"=>$TPL["url_alloc_projectList"]
                 ,"form_name"=>"projectList_filter"
                 );

function show_filter() {
  global $TPL,$defaults;

  $_FORM = project::load_form_data($defaults);
  $arr = project::load_project_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/projectListFilterS.tpl");
}

function show_project_list() {
  global $defaults;

  $_FORM = project::load_form_data($defaults);
  #echo "<pre>".print_r($_FORM,1)."</pre>";
  echo project::get_project_list($_FORM);
}

$TPL["main_alloc_title"] = "Project List - ".APPLICATION_NAME;
include_template("templates/projectListM.tpl");
page_close();



?>
