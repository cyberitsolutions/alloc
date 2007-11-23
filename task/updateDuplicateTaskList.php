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


require_once("../alloc.php");


if ($_GET["projectID"]) {
  $opt["return"] = "dropdown_options";
  $opt["projectID"] = $_GET["projectID"];
  $opt["taskStatus"] = $_GET["task_status"];
  $opt["taskView"] = "byProject";
  $tasklist = task::get_task_list($opt);
  
  $dropdown_options = get_option("", 0);

  $task = new task;
  $task->set_id($_GET["taskID"]);
  $task->select();
  $duplicateID = $task->get_value("duplicateTaskID");
  
  if ($duplicateID && !$tasklist[$duplicateID]) {
    $othertask = new task;
    $othertask->set_id($duplicateID);
    $othertask->select();
    $dropdown_options.= get_option($duplicateID." ".$othertask->get_task_name(), $duplicateID, true);
  }
  $dropdown_options.= get_select_options($tasklist, $duplicateID, 40);
  echo stripslashes("<select>".$dropdown_options."/select");
}



?>
