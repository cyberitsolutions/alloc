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


include_template("templates/projectListM.tpl");
page_close();



?>
