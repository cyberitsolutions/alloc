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

class tsiHint extends db_entity {
  public $classname = "tsiHint";
  public $data_table = "tsiHint";
  public $display_field_name = "projectID";
  public $key_field = "tsiHintID";
  public $data_fields = array("taskID"
                             ,"personID"
                             ,"duration"
                             ,"date"
                             ,"comment"
                             ,"tsiHintCreatedTime"
                             ,"tsiHintCreatedUser"
                             ,"tsiHintModifiedTime"
                             ,"tsiHintModifiedUser"
                             );

  function add_tsiHint($stuff) {
    $current_user = &singleton("current_user");
    $errstr = "Failed to record new time sheet item hint. ";
    $username = $stuff["username"];

    $people = person::get_people_by_username();
    $personID = $people[$username]["personID"];
    $personID or alloc_error("Person ".$username." not found.");

    $taskID = $stuff["taskID"];
    $projectID = $stuff["projectID"];
    $duration = $stuff["duration"];
    $comment = $stuff["comment"];
    $date = $stuff["date"];

    if ($taskID) {
      $task = new task();
      $task->set_id($taskID);
      $task->select();
      $projectID = $task->get_value("projectID");
      $extra = " for task ".$taskID;
    }

    $projectID or alloc_error(sprintf($errstr."No project found%s.",$extra));

    $row_projectPerson = projectPerson::get_projectPerson_row($projectID, $current_user->get_id());
    $row_projectPerson or alloc_error($errstr."The person(".$current_user->get_id().") has not been added to the project(".$projectID.").");

    if ($row_projectPerson && $projectID) {
      // Add new time sheet item
      $tsiHint = new tsiHint();
      $d = $date or $d = date("Y-m-d");
      $tsiHint->set_value("date",$d);
      $tsiHint->set_value("duration",$duration);
      if (is_object($task)) {
        $tsiHint->set_value("taskID",sprintf("%d",$taskID));
      }
      $tsiHint->set_value("personID",$personID);
      $tsiHint->set_value("comment",$comment);
      $tsiHint->save();
      $ID = $tsiHint->get_id();
    }

    if ($ID) {
      return array("status"=>"yay","message"=>$ID);
    } else {
      alloc_error($errstr."Time hint not added.");
    }
  }

  function parse_tsiHint_string($str) {
    preg_match("/^"
              ."([a-zA-Z0-9]+)"                      # username
              ."\s*"
              ."(\d\d\d\d\-\d\d?\-\d\d?\s+)?"   # date
              ."([\d\.]+)?"          # duration
              ."\s*"
              ."(\d+)?"             # task id
              ."\s*"
              ."(.*)"               # comment
              ."\s*"
              ."$/i",$str,$m);

    $rtn["username"] = $m[1];
    $rtn["date"] = trim($m[2]) or $rtn["date"] = date("Y-m-d");
    $rtn["duration"] = $m[3];
    $rtn["taskID"] = $m[4];
    $rtn["comment"] = $m[5];

    // change 2010/10/27 to 2010-10-27
    $rtn["date"] = str_replace("/","-",$rtn["date"]);

    return $rtn;
  }

}  



?>
