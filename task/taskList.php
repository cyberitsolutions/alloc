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
define("PAGE_IS_PRINTABLE",1);


$defaults = array("showHeader"=>true
                 ,"showProject"=>true
                 ,"showTaskID"=>true
                 ,"showEdit"=>true
                 ,"taskView" => "byProject"
                 ,"padding"=>1
                 ,"url_form_action"=>$TPL["url_alloc_taskList"]
                 ,"form_name"=>"taskList_filter"
                 );

function show_filter() {
  global $TPL,$defaults;

  $_FORM = task::load_form_data($defaults);
  $arr = task::load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/taskFilterS.tpl");
}

function show_task_list() {
  global $defaults;

  $_FORM = task::load_form_data($defaults);
  #echo "<pre>".print_r($_FORM,1)."</pre>";
  echo task::get_task_list($_FORM);
}

$TPL["main_alloc_title"] = "Task List - ".APPLICATION_NAME;

// Check for updates
if ($_POST["run_mass_update"]) {

  if ($_POST["select"]) {
    foreach($_POST["select"] as $taskID => $selected) { 
      $task = new task;
      $task->set_id($taskID);
      $task->select();

      if ($_POST["update_action"] == "dateTargetStart") {
        $task->set_value('dateTargetStart', $_POST["dateTargetStart"]);
        $task->save();

      } else if ($_POST["update_action"] == "dateTargetCompletion") {
        $task->set_value('dateTargetCompletion', $_POST["dateTargetCompletion"]);
        $task->save();

      } else if ($_POST["update_action"] == "dateActualStart") {
        $task->set_value('dateActualStart', $_POST["dateActualStart"]);
        $task->save();

      } else if ($_POST["update_action"] == "dateActualCompletion") {
        $task->set_value('dateActualCompletion', $_POST["dateActualCompletion"]);
        $task->set_value('closerID', $current_user->get_id());
        $task->save();

      } else if ($_POST["update_action"] == "assignee") {
        $task->set_value("personID", $_POST["assignee"]);
        $task->save();

      } else if ($_POST["update_action"] == "manager") {
        $task->set_value("managerID", $_POST["manager"]);
        $task->save();

      } else if ($_POST["update_action"] == "timeEstimate") {
        $task->set_value("timeEstimate", $_POST["timeEstimate"]);
        $task->save();

      } else if ($_POST["update_action"] == "priority") {
        $task->set_value("priority", $_POST["priority"]);
        $task->save();
      }
    }
    $TPL["message_good"][] = "Tasks updated.";
  }
}

include_template("templates/taskListM.tpl");
page_close();

?>
