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

class top_ten_tasks_home_item extends home_item {
  var $date;

  function __construct() {
    parent::__construct("top_ten_tasks", "Tasks", "task", "topTenTasksH.tpl","standard",20);
  }

  function visible() {
    $current_user = &singleton("person");
    return ((isset($current_user->prefs["topTasksNum"]) && (sprintf("%d",$current_user->prefs["topTasksNum"]) > 0 || $current_user->prefs["topTasksNum"] == "all")) && have_entity_perm("task", PERM_READ_WRITE, $current_user, true));
  }

  function render() {
    $current_user = &singleton("person");
    global $tasks_date;
    global $TPL;
    $options["taskStatus"] = $current_user->prefs["topTasksStatus"];
    $current_user->prefs["topTasksNum"] != "all" and $options["limit"] = $current_user->prefs["topTasksNum"];
    $options["taskView"] = "prioritised";
    #$options["projectType"] = "mine";
    $options["personID"] = $current_user->get_id();
    #$options["showTimes"] = true;
    $options["showDate1"] = true;
    $options["showDate3"] = true;
    $options["showHeader"] = true;
    $options["showProject"] = true;
    $options["showTaskID"] = true;
    $options["showStatus"] = true;
    $TPL["taskListRows"] = task::get_list($options);
    $TPL["taskListOptions"] = $options;
    if (count($TPL["taskListRows"])) {
      return true;
    }
  }
}



?>
