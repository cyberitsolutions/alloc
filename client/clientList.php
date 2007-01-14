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
                 ,"showClientLink"=>true
                 ,"showClientStatus"=>true
                 ,"showPrimaryContactName"=>true
                 ,"showPrimaryContactPhone"=>true
                 ,"showPrimaryContactEmail"=>true
                 ,"url_form_action"=>$TPL["url_alloc_clientList"]
                 ,"form_name"=>"clientList_filter"
                 );


function show_filter() {
  global $TPL,$defaults;
  $_FORM = client::load_form_data($defaults);
  $arr = client::load_client_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/clientListFilterS.tpl");
}


function show_client_list() {
  global $defaults;
  $_FORM = client::load_form_data($defaults);
  echo client::get_client_list($_FORM);
}


include_template("templates/clientListM.tpl");
page_close();



?>
