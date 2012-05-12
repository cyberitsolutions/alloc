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

define("NO_AUTH",true);
define("IS_GOD",true);
require_once("../alloc.php");

ini_set('max_execution_time',180000); 
ini_set('memory_limit',"512M");


function echoo($str) {
  $nl = "<br>";
  $nl = "\n";
  echo $nl.$str;
}


foreach (array("client","comment","item","project","task","timeSheet","wiki") as $i) {
  if (!is_dir(ATTACHMENTS_DIR.'search/'.$i)) {
    $index = Zend_Search_Lucene::create(ATTACHMENTS_DIR.'search/'.$i);
    $index->commit();
  }
}


$q = "SELECT * FROM indexQueue ORDER BY entity";

$db = new db_alloc();
$db->query($q);

echoo("Beginning ...");

while ($row = $db->row()) {

  $z++;
  if ($z % 1000 == 0 && is_object($index)) {
    echoo($z." Committing index: ".$current_index);
    $index->commit();
    flush();
  }

  if (!$current_index || $current_index != $row["entity"]) {

    // commit previous index
    if (is_object($index)) { 
      echoo("Committing index: ".$current_index);
      $index->commit();
    }

    // start a new index
    echoo("New \$index: ".$row["entity"]);
    $index = Zend_Search_Lucene::open(ATTACHMENTS_DIR.'search/'.$row["entity"]);
  }

  $current_index = $row["entity"];

  echoo("  Updating index ".$row["entity"]." #".$row["entityID"]);

  $e = new $row["entity"];
  $e->set_id($row["entityID"]);
  $e->select();
  $e->delete_search_index_doc($index);
  $e->update_search_index_doc($index);

  // Nuke item from queue
  $i = new indexQueue();
  $i->set_id($row["indexQueueID"]);
  $i->delete();
}

// commit index
if (is_object($index)) { 
  echoo("Committing index(2): ".$current_index);
  $index->commit();
}




?>
