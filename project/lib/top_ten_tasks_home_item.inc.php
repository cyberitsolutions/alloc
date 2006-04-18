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

class top_ten_tasks_home_item extends home_item {
  var $date;

  function top_ten_tasks_home_item() {
    home_item::home_item("top_ten_tasks", "Top Five Tasks", "project", "topTenTasksH.tpl");
  }

  function show_tasks() {
    global $current_user, $tasks_date;

    $options["taskView"] = "prioritised";
    $options["projectType"] = "mine";
    $options["personIDonly"] = $current_user->get_id();
    $options["taskStatus"] = "not_completed";
    $options["limit"] = 5;
    $options["showDate1"] = true;
    $options["showDate2"] = true;
    $options["showDate3"] = true;
    $options["showHeader"] = true;
    $options["showProject"] = true;

    echo task::get_task_list($options);
  }
}



?>
