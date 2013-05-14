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

class task_list_home_item extends home_item {
  var $date;

  function __construct() {
    $this->has_config = true;
    parent::__construct("top_ten_tasks", "Tasks", "task", "taskListH.tpl","standard",20);
  }

  function visible() {
    $current_user = &singleton("current_user");

    if (!isset($current_user->prefs["showTaskListHome"])) {
      $current_user->prefs["showTaskListHome"] = 1;
    }

    if ($current_user->prefs["showTaskListHome"]) {
      return true;
    }
  }

  function render() {
    global $TPL;

    $defaults = array("showHeader"=>true
                     ,"showTaskID"=>true
                     ,"taskView" => "prioritised"
                     ,"showStatus" => "true"
                     ,"url_form_action"=>$TPL["url_alloc_home"]
                     ,"form_name"=>"taskListHome_filter"
                     );

    $current_user = &singleton("current_user");
    if (!$current_user->prefs["taskListHome_filter"]) {
      $defaults["taskStatus"] = "open"; 
      $defaults["personID"] = $current_user->get_id(); 
      $defaults["showStatus"] = true;
      $defaults["showProject"] = true;
      $defaults["limit"] = 10;
      $defaults["applyFilter"] = true;
    }

    $_FORM = task::load_form_data($defaults);
    $TPL["taskListRows"] = task::get_list($_FORM);
    $TPL["_FORM"] = $_FORM;

    return true;
  }
}



?>
