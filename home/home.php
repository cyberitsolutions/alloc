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

function sort_home_items($a, $b) {
  return $a->seq > $b->seq;
}

function show_home_items($width,$home_items) {
  global $TPL;
  $items = array();

  foreach ($home_items as $item) {
    $i = new $item();
    $items[] = $i;
  }

  uasort($items,"sort_home_items");

  foreach ((array)$items as $item) {
    if ($item->width == $width && $item->visible()) {
      $TPL["item"] = $item;
      if ($item->render()) {
        include_template("templates/homeItemS.tpl");
      }
    }
  }
}

global $modules;
$current_user = &singleton("current_user");
foreach ($modules as $module_name => $module) {
  if ($module->home_items) {
    $home_items = array_merge((array)$home_items,$module->home_items);
  }
}
$TPL["home_items"] = $home_items;

if (isset($_POST["tsiHint_item"])) {
  $t = tsiHint::parse_tsiHint_string($_POST["tsiHint_item"]);
  if (is_numeric($t["duration"]) && $current_user->get_id()) {
    $tsiHint = new tsiHint();
    $tsi_row = $tsiHint->add_tsiHint($t);
    alloc_redirect($TPL["url_alloc_home"]);
  } else {
    alloc_error("Time hint not added. No duration set.");
    alloc_error(print_r($t,1));
  }
}

$TPL["main_alloc_title"]="Home Page - ".APPLICATION_NAME;
if ($_GET["media"] == "print") {
	include_template("templates/homePrintableM.tpl");
} else {
	include_template("templates/homeM.tpl");
}

?>
