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


define("NO_REDIRECT",1);
require_once("../alloc.php");

//usleep(1000);

$t = tsiHint::parse_tsiHint_string($_REQUEST["tsiHint_item"]);

$people = person::get_people_by_username();

foreach ($t as $k=>$v) {
  if ($v) {
    if ($k == "taskID") {
      $task = new task();
      $task->set_id($v);
      if ($task->select()) {
        $v = $task->get_id()." ".$task->get_link();
      } else {
        $v = "Task ".$v." not found.";
      }
    } else if ($k == "username") {
      $name = $people[$v]["name"] or $name = $people[$v]["username"];
    }
    $rtn[$k] = $v;
  }
}

//2010-10-01  1 Days x Double Time  
//Task: 102 This is the task
//Comment: This is the comment


$str[] = "<table>";
$str[] = "<tr><td>".$name." ".$rtn["date"]." </td><td class='nobr bold'> ".$rtn["duration"]." Hours</td><td class='nobr'></td></tr>";
$rtn["taskID"]  and $str[] = "<tr><td colspan='3'>".$rtn["taskID"]."</td></tr>";
$rtn["comment"] and $str[] = "<tr><td colspan='3'>".$rtn["comment"]."</td></tr>";
$str[] = "</table>";

print implode("\n",$str);




?>
