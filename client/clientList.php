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


$defaults = array("url_form_action"=>$TPL["url_alloc_clientList"]
                 ,"form_name"=>"clientList_filter"
                 );


function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = client::load_form_data($defaults);
  $arr = client::load_client_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/clientListFilterS.tpl");
}


$_FORM = client::load_form_data($defaults);
$TPL["clientListRows"] = client::get_list($_FORM);

if (!$current_user->prefs["clientList_filter"]) {
  $TPL["message_help"][] = "

allocPSA allows you to store pertinent information about your Clients and
the organisations that you interact with. This page allows you to see a list of Clients.

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Clients. 
If you would prefer to create a new Client, click the <b>New Client</b> link
in the top-right hand corner of the box below.";

}


$TPL["main_alloc_title"] = "Client List - ".APPLICATION_NAME;
include_template("templates/clientListM.tpl");



?>
