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

$TPL["main_alloc_title"] = "Task List - ".APPLICATION_NAME;

$defaults = array("showHeader"=>true
                 ,"showTaskID"=>true
                 ,"showEdit"=>true
                 ,"taskView" => "byProject"
                 ,"showStatus" => "true"
                 ,"padding"=>1
                 ,"url_form_action"=>$TPL["url_alloc_taskList"]
                 ,"form_name"=>"taskList_filter"
                 );

// Load task list
$_FORM = task::load_form_data($defaults);
$TPL["taskListRows"] = task::get_list($_FORM);
$TPL["_FORM"] = $_FORM;

// Load filter
$arr = task::load_task_filter($_FORM);
is_array($arr) and $TPL = array_merge($TPL,$arr);


// Check for updates
if ($_POST["mass_update"]) {

  if ($_POST["select"]) {

    $allowed_auto_fields = array("dateTargetStart","dateTargetCompletion","dateActualStart","dateActualCompletion","managerID"
                                ,"timeLimit","timeBest","timeWorst","timeExpected","priority","taskTypeID","taskStatus","personID");  

    foreach($_POST["select"] as $taskID => $selected) { 
      $task = new task();
      $task->set_id($taskID);
      $task->select();

      // Special case: projectID and parentTaskID have to be done together
      if ($_POST["update_action"] == "projectIDAndParentTaskID") {
        
        // Can't set self to be parent
        if ($_POST["parentTaskID"] != $task->get_id()) {
          $task->set_value("parentTaskID", $_POST["parentTaskID"]);
        }
        // If task is a parent, change the project of that tasks children
        if ($_POST["projectID"] != $task->get_value("projectID") && $task->get_value("taskTypeID") == "Parent") { 
          $task->update_children("projectID",$_POST["projectID"]);   
        }
        $task->set_value("projectID", $_POST["projectID"]);
        $task->updateSearchIndexLater = true;
        $task->save();

      // All other cases are generic and can be handled by a single clause
      } else if ($_POST["update_action"] && in_array($_POST["update_action"],$allowed_auto_fields)) {
        $task->set_value($_POST["update_action"], $_POST[$_POST["update_action"]]);
        $task->updateSearchIndexLater = true;
        $task->save();
      }
    }
    $TPL["message_good"][] = "Tasks updated.";
    alloc_redirect($TPL["url_alloc_taskList"]);
  }
}

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
