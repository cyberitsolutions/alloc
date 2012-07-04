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


if ($_POST["customize_save"]) {
  $current_user->prefs["customizedFont"] = sprintf("%d",$_POST["font"]);
  $current_user->prefs["customizedTheme2"] = $_POST["theme"];

  $current_user->prefs["tasksGraphPlotHome"] = $_POST["weeks"];
  $current_user->prefs["tasksGraphPlotHomeStart"] = $_POST["weeksBack"];

  $current_user->prefs["topTasksNum"] = $_POST["topTasksNum"];
  $current_user->prefs["topTasksStatus"] = $_POST["topTasksStatus"];

  $current_user->prefs["projectListNum"] = $_POST["projectListNum"];

  $current_user->prefs["dailyTaskEmail"] = $_POST["dailyTaskEmail"];
  $current_user->prefs["receiveOwnTaskComments"] = $_POST["receiveOwnTaskComments"];

  $current_user->prefs["showFilters"] = $_POST["showFilters"];
  $current_user->prefs["privateMode"] = $_POST["privateMode"];

  $current_user->prefs["timeSheetHoursWarn"] = $_POST["timeSheetHoursWarn"];
  $current_user->prefs["timeSheetDaysWarn"] = $_POST["timeSheetDaysWarn"];
  $current_user->store_prefs();
  alloc_redirect($TPL["url_alloc_home"]);
}

if (isset($_POST["time_item"])) {

  $t = timeSheetItem::parse_time_string($_POST["time_item"]);

  if (is_numeric($t["duration"]) && $current_user->get_id()) {
    $timeSheet = new timeSheet();
    $tsi_row = $timeSheet->add_timeSheetItem($t);
  } else {
    $TPL["message"][] = "Time not added. No duration set.";
    $TPL["message"][] = print_r($t,1);
  }
}


$TPL["main_alloc_title"]="Home Page - ".APPLICATION_NAME;
if ($_GET["media"] == "print") {
	include_template("templates/homePrintableM.tpl");
} else {
	include_template("templates/homeM.tpl");
}

?>
