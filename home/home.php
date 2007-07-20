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

function show_home_items($width) {
  global $home_items, $current_home_item, $TPL;

  $home_items or $home_items = array();
  reset($home_items);


  $arr = $home_items[$width] or $arr = array();
  ksort($arr);

  foreach ($arr as $current_home_item) {
    if ($_GET["media"] != "print" || $current_home_item->print) {
      $TPL["item_title"] = $current_home_item->get_title();
      include_template("templates/homeItemS.tpl");
    }
  }
}

function show_item() {
  global $current_home_item;
  $current_home_item->show();
}

register_home_items();
define("PAGE_IS_PRINTABLE",1);

if ($_GET["media"] == "print") {
	include_template("templates/homePrintableM.tpl");
} else {
	include_template("templates/homeM.tpl");
}

page_close();



?>
