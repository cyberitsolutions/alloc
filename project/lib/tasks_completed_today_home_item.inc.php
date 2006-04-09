<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

class tasks_completed_today_home_item extends home_item {
  var $date;

  function tasks_completed_today_home_item() {
    home_item::home_item("tasks_completed_today", "Tasks Completed Today", "project", "tasksCompletedTodayH.tpl");

    global $task_date, $TPL;

    if (isset($task_date)) {
      $this->date = $task_date;
    } else {
      $this->date = mktime();
    }

    $TPL["prev_task_date"] = mktime(0, 0, 0, date("m", $this->date), date("d", $this->date) - 1, date("Y", $this->date));
    $TPL["next_task_date"] = mktime(0, 0, 0, date("m", $this->date), date("d", $this->date) + 1, date("Y", $this->date));
  }

  function show_tasks() {
    global $current_user, $tasks_date;

    $task_filter = new task_filter;
    $task_filter->set_element("person", $current_user);
    $task_filter->set_element("dateActualCompletion", date("Y-m-d", $this->date));
    $task_list = new task_list($task_filter);
    echo $task_list->get_task_summary("", false);
  }

  function get_title() {
    return "Tasks Completed ".date("Y-m-d (D)", $this->date);
  }
}



?>
