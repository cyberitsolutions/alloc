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


require_once("alloc.inc");

echo "<style>body {font-size:10px; font-family:Arial,fixed;}</style>";

function rebuild_tree($taskID, $left) { 
  #static $i;
  #$i++; // fifty iterations
  #if ($i>50) {
   #exit;
  #}

  static $x;
  $padding = "&nbsp;&nbsp;&nbsp;&nbsp;";

  $db = new db_alloc;

  $right = $left+1; 

  $q = sprintf('SELECT taskID  FROM task WHERE parentTaskID=%d',$taskID);
  $db->query($q); 
  $orig_x = $x;
  if ($db->num_rows()) {
    $buffer2 = str_repeat($padding,$x);
    $x+=1;
    $buffer = str_repeat($padding,$x);
  } else {
    $buffer = str_repeat($padding,$x);
    $buffer2 = str_repeat($padding,$x);
  }

  while ($db->next_record()) { $ids[] = $db->f("taskID"); }
  count($ids) and print " (".implode(",",$ids).")";

  $db->query($q); 
  while ($db->next_record()) { 
    echo "<br/>".$buffer."<b>".$db->f("taskID")."</b>";
    $right = rebuild_tree($db->f("taskID"), $right); 
  } 
  $x = $orig_x;

  $q = sprintf("UPDATE task SET leftID = %d, rightID = %d WHERE taskID = %d",$left,$right,$taskID);
  $db->query($q);
  echo "<br/>".$buffer2."<b>".$taskID."</b> L:".$left." R:".$right;

  return $right+1; 
}


$db = new db_alloc;

$q = "update task set leftID=0,rightID=0 where 1";
$db->query($q);
$q = "delete from task where taskID = 0";
$db->query($q);
$q = "insert into task (taskID,taskName,parentTaskID) values (100000,'root node elephant',150000)";
$db->query($q);
$q = "update task set taskID = 0 where taskName = 'root node elephant'";
$db->query($q);

// Update orphaned nodes
$db2 = new db_alloc;
$db3 = new db_alloc;

$q = "select taskID,parentTaskID from task where parentTaskID != 0 and parentTaskID != 150000";
$db->query($q);
while ($db->next_record()) {
  $q = "select taskID from task where taskID = ".$db->f("parentTaskID");
  $db2->query($q);
  $db2->next_record();
  if (!$db2->f("taskID")) {
    $q = "update task set parentTaskID = 0 where taskID = ".$db->f("taskID");
    $db3->query($q);
    echo "<br/>Resetting parentTaskID to 0 for taskID ".$db->f("taskID");

  }
}

rebuild_tree(0,1);

echo "<br/>Finished!<br/>";

$q = "select taskID, leftID, rightID from task where taskID = 0";
$db->query($q);
$db->next_record();
echo "<br/>taskID: 0, left: ".$db->f("leftID").", right: ".$db->f("rightID");

$q = "select count(*) as tally from task";
$db->query($q);
$db->next_record();
echo "<br/>Tally: ".$db->f("tally")." tally*2: ".$db->f("tally")*2;


?>
