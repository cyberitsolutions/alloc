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

function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = productSale::load_form_data($defaults);
  $arr = productSale::load_productSale_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/productSaleListFilterS.tpl");
}


$defaults = array("url_form_action"=>$TPL["url_alloc_productSaleList"]
                 ,"form_name"=>"productSaleList_filter"
                 ,"return" => "array"
                 );

$_FORM = productSale::load_form_data($defaults);
$TPL["productSaleListRows"] = productSale::get_list($_FORM);

if (!$current_user->prefs["productSaleList_filter"]) {
  $TPL["message_help"][] = "

allocPSA allows you to create Sales and Products and allocate the funds from
Sales. This page allows you to view a list of Sales.

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Sales.
If you would prefer to create a new Sale, click the <b>New Sale</b> link
in the top-right hand corner of the box below.";
}




$TPL["main_alloc_title"] = "Sales List - ".APPLICATION_NAME;
include_template("templates/productSaleListM.tpl");

?>
