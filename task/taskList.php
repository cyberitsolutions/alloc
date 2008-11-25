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
  global $TPL,$defaults,$_FORM;

  $arr = task::load_task_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/taskFilterS.tpl");
}

function show_task_list() {
  global $defaults,$_FORM;

  #echo "<pre>".print_r($_FORM,1)."</pre>";
  echo task::get_list($_FORM);
}

$TPL["main_alloc_title"] = "Task List - ".APPLICATION_NAME;

//Load form
$_FORM = task::load_form_data($defaults);

// Check for updates
if ($_POST["run_mass_update"]) {

  if ($_POST["select"]) {

    $allowed_auto_fields = array("dateTargetStart","dateTargetCompletion","dateActualStart","managerID","timeEstimate","priority","taskTypeID","blocker");  

    foreach($_POST["select"] as $taskID => $selected) { 
      $task = new task;
      $task->set_id($taskID);
      $task->select();

      // Special case: Close task
      if ($_POST["update_action"] == "dateActualCompletion") {
        $task->set_value('dateActualCompletion', $_POST["dateActualCompletion"]);
        $d = $u = ""; # can't use unset(). The variables need to be empty strings for the set_value.
        $_POST["dateActualCompletion"] and $d = date("Y-m-d");
        $_POST["dateActualCompletion"] and $u = $current_user->get_id();
        $task->set_value('closerID', $u);
        $task->set_value("dateClosed", $d);
        $task->save();

      // Special case: Re-assign task
      } else if ($_POST["update_action"] == "personID") {
        $task->set_value("personID", $_POST["personID"]);
        $d = ""; # can't use unset(). The variables need to be empty strings for the set_value.
        $_POST["personID"] and $d = date("Y-m-d");
        $task->set_value("dateAssigned", $d);
        $task->save();

      // Special case: projectID and parentTaskID have to be done together
      } else if ($_POST["update_action"] == "projectIDAndParentTaskID") {
        
        // Can't set self to be parent
        if ($_POST["parentTaskID"] != $task->get_id()) {
          $task->set_value("parentTaskID", $_POST["parentTaskID"]);
        }
        // If task is a parent, change the project of that tasks children
        if ($_POST["projectID"] != $task->get_value("projectID") && $task->get_value("taskTypeID") == 2) { 
          $task->update_children("projectID",$_POST["projectID"]);   
        }
        $task->set_value("projectID", $_POST["projectID"]);
        $task->save();

      // All other cases are generic and can be handled by a single clause
      } else if ($_POST["update_action"] && in_array($_POST["update_action"],$allowed_auto_fields)) {
        $task->set_value($_POST["update_action"], $_POST[$_POST["update_action"]]);
        $task->save();
      }
    }
    $TPL["message_good"][] = "Tasks updated.";
  }
}

$_FORM = savedView::process_form($_FORM);


if (!$current_user->prefs["taskList_filter"]) {
  $TPL["message_help"][] = "

allocPSA allows you to assign, schedule and plan out Tasks. This page
allows you to view a list of Tasks. 

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Tasks. 
If you would prefer to create a new Task, click the <b>New Task</b> link
in the top-right hand corner of the box below.";
}



include_template("templates/taskListM.tpl");

?>
