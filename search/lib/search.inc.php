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

    $e = new $entity;
    $rows = array();
    $q = sprintf("SELECT * FROM %s WHERE %s MATCH(%s) AGAINST('%s') LIMIT 10"
                ,$entity,$extra,$fieldName,db_esc($criteria));
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $rows[$row[$e->key_field->get_name()]] = $row;
    }
    return $rows;


    // This may be how you do this in postgres: http://www.postgresql.org/docs/8.3/static/textsearch-intro.html
    // SELECT to_tsvector('fat cats ate fat rats') @@ to_tsquery('fat & rat');

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

  function by_file($file, $needle) {
    if (file_exists($file) && is_readable($file) && !is_dir($file)) {
      $rtn = array();
      $fp = fopen($file, 'r');
      if ($fp) {
        while (!feof($fp)) {
          $line = stream_get_line($fp,65535,"\n"); // faster than fgets
          strpos(strtolower($line), strtolower($needle)) !== false and $rtn[] = $line;
        }
        fclose($fp);
      }
    }
    return $rtn;
  }

  function get_trimmed_description($haystack, $needle, $category) {

    $position = strpos(strtolower($haystack),strtolower($needle));
    if ($position !== false) {
      $prefix = "...";
      $suffix = "...";

      // Nuke trailing ellipses if the string ends in the match
      if (strlen(substr($haystack,$position)) == strlen($needle)) {
        $suffix = "";
      } 

      $buffer = 30;
      $position = $position - $buffer;

      // Reset position to zero cause negative number will make it wrap around, 
      // and nuke ellipses prefix because the string begins with the match
      if ($position < 0) {
        $position = 0;
        $prefix = "";
      } 

      $str = substr($haystack,$position,strlen($needle)+100);
      $str = str_replace($needle,"[[[".$needle."]]]",$str);
      $str = $prefix.$str.$suffix;
      return $str;
    } 

    if ($category == "Clients") {
      return substr($haystack,0,100);
    } 
    
  }

  function get_recursive_dir_list($dir) {
    $rtn = array();
    $dir = realpath($dir).DIRECTORY_SEPARATOR;
    $dont_search_these_dirs = array(".","..","CVS",".hg",".bzr","_darcs",".git");
    $files = scandir($dir);
    foreach ($files as $file) {
      if(!in_array($file, $dont_search_these_dirs)) {
        if (is_file($dir.$file) && !is_dir($dir.$file)) {
          $rtn[] = $dir.$file;

        } else if (is_dir($dir.$file)) {
          $rtn += search::get_recursive_dir_list($dir.$file);
        }
      }
    }
    return $rtn;
  }



}


?>
