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

class task_calendar_home_item extends home_item {
  var $date;

  function __construct() {
    $this->has_config = true;
    parent::__construct("task_calendar_home_item", "Calendar", "calendar", "taskCalendarS.tpl","standard",30);
  }

  function visible() {
    $current_user = &singleton("current_user");
  
    if (!isset($current_user->prefs["showCalendarHome"])) {
      $current_user->prefs["showCalendarHome"] = 1;
      $current_user->prefs["tasksGraphPlotHome"] = 4;
      $current_user->prefs["tasksGraphPlotHomeStart"] = 1;
    }

    if ($current_user->prefs["showCalendarHome"]) {
      return true;
    }
  }

  function render() {
    return true;
  }

  function show_task_calendar_recursive() {
    $current_user = &singleton("current_user");
    $tasksGraphPlotHomeStart = $current_user->prefs["tasksGraphPlotHomeStart"];
    $tasksGraphPlotHome = $current_user->prefs["tasksGraphPlotHome"];
    $calendar = new calendar($tasksGraphPlotHomeStart,$tasksGraphPlotHome);
    $calendar->set_cal_person($current_user->get_id());
    $calendar->set_return_mode("home");
    $calendar->draw($template);
  }
}



?>
