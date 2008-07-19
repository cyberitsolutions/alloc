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

function get_products() {
  global $TPL;
  $rtn = array();
  $rows = product::get_product_list(array());
  $rows or $rows = array();
  foreach ($rows as $row) {
    $rtn[] = array("url"=>$TPL["url_alloc_product"]."productID=".$row["productID"],
        "name"=>$row["productName"]);
  }
  return $rtn;
}

$TPL["main_alloc_title"] = "Product List - ".APPLICATION_NAME;
include_template("templates/productListM.tpl");
page_close();

