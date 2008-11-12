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

define("PERM_PROJECT_READ_TASK_DETAIL", 256);

class search {

  function by_fulltext($entity, $fieldName, $criteria, $extra="") {
    // FULLTEXT search only work on columns that have a FULLTEXT KEY added to them
    // see patch-00123-alla.sql .. the table needs to be MyISAM as well.
    $rows = array();
    $q = sprintf("SELECT * FROM %s WHERE %s MATCH(%s) AGAINST('%s') LIMIT 10"
                ,$entity,$extra,$fieldName,db_esc($criteria));
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $rows[$row[$entity."ID"]] = $row;
    }
    return $rows;
  }

  function by_explode($entity, $fieldName, $criteria, $extra="") {
    $bits = explode(" ",$criteria);
    if (is_array($bits) && count($bits)) {
      foreach ($bits as $bit) {
        $sql[] = $fieldName." = '".db_esc($bit)."'";
      }
      $criteria = " OR (".implode(" OR ",$sql);
    }

    $rows = array();
    $q = sprintf("SELECT * FROM %s WHERE %s %s"
                , $entity, $extra, $criteria);
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $rows[$row[$entity."ID"]] = $row;
    }
    return $rows;
  }



}


?>
