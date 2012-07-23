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

$defaults = array("return"       => "html"
                 ,"showHeader"   => true
                 ,"showName"     => true
                 ,"showActive"   => true
                 ,"showNos"      => true
                 ,"showLinks"    => true
                 ,"form_name"    => "personList_filter"
                 );

function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = person::load_form_data($defaults);
  $arr = person::load_person_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/personListFilterS.tpl");
}

function show_people() {
  global $defaults;
  $_FORM = person::load_form_data($defaults);
  #echo "<pre>".print_r($_FORM,1)."</pre>";
  echo person::get_list($_FORM);
}

$TPL["main_alloc_title"] = "People - ".APPLICATION_NAME;

$max_alloc_users = get_max_alloc_users();
$num_alloc_users = get_num_alloc_users();
if ($max_alloc_users && $num_alloc_users > $max_alloc_users) {
  alloc_error("Maximum number of active user accounts: ".$max_alloc_users);
  alloc_error("Current number of active user accounts: ".$num_alloc_users."<br>");
  alloc_error(get_max_alloc_users_message());
} else if ($max_alloc_users) {
  $TPL["message_help"][] = "Maximum number of active user accounts: ".$max_alloc_users;
  $TPL["message_help"][] = "Current number of active user accounts: ".$num_alloc_users;
}


include_template("templates/personListM.tpl");

?>
