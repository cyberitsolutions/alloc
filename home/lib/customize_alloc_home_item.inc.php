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

class customize_alloc_home_item extends home_item {
  function customize_alloc_home_item() {
    global $TPL, $current_user;

    home_item::home_item("", "Preferences", "home", "customizeH.tpl", "narrow",60, false);

    if (!is_object($current_user)) {
      return false;
    }

    if ($_POST["customize_save"]) {
      $current_user->prefs["customizedFont"] = sprintf("%d",$_POST["font"]);
      $current_user->prefs["customizedTheme2"] = $_POST["theme"];

      $current_user->prefs["tasksGraphPlotHome"] = $_POST["weeks"];
      $current_user->prefs["tasksGraphPlotHomeStart"] = $_POST["weeksBack"];

      $current_user->prefs["topTasksNum"] = $_POST["topTasksNum"];
      $current_user->prefs["topTasksStatus"] = $_POST["topTasksStatus"];

      $current_user->prefs["projectListNum"] = $_POST["projectListNum"];
    }

    $TPL["fontOptions"] = get_select_options(get_customizedFont_array(), $current_user->prefs["customizedFont"]);
    $TPL["themeOptions"] = get_select_options(get_customizedTheme_array(), $current_user->prefs["customizedTheme2"]);

    $week_ops = array("0"=>0, 1=>1, 2=>2, 3=>3, 4=>4, 8=>8, 12=>12, 30=>30, 52=>52);
    $TPL["weeksOptions"] = get_select_options($week_ops, $current_user->prefs["tasksGraphPlotHome"]);
    $TPL["weeksBackOptions"] = get_select_options($week_ops, $current_user->prefs["tasksGraphPlotHomeStart"]);

    $task_num_ops = array("0"=>0,1=>1,2=>2,3=>3,4=>4,5=>5,10=>10,15=>15,20=>20,30=>30,40=>40,50=>50,"all"=>"All");
    $TPL["topTasksNumOptions"] = get_select_options($task_num_ops, $current_user->prefs["topTasksNum"]);
    $TPL["topTasksStatusOptions"] = get_select_options(task::get_task_statii_array(), $current_user->prefs["topTasksStatus"]);

    $project_list_ops = array("0"=>0,5=>5,10=>10,15=>15,20=>20,30=>30,40=>40,50=>50,"all"=>"All");
    $TPL["projectListNumOptions"] = get_select_options($project_list_ops, $current_user->prefs["projectListNum"]);
  }


  function show_customization($template_name) {
    global $TPL, $current_user;
    include_template($template_name);
  }


}



?>
