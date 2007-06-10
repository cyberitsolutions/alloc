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

// DB abstraction
class db {

  var $username;
  var $password;
  var $hostname;
  var $database;
  var $query_id;
  var $link_id;
  var $row = array();
  var $error;
  var $verbose = 1;

  function db($username="",$password="",$hostname="",$database="") { // Constructor
    $this->username = $username;
    $this->password = $password;
    $this->hostname = $hostname;
    $this->database = $database;
  }

  function get_db($username="",$password="",$hostname="",$database="") {         // Singleton
    static $db;
    $db or $db = new db($username,$password,$hostname,$database);
    return $db;
  }

  function connect() {
    $this->link_id = mysql_connect($this->hostname,$this->username,$this->password);
    if ($this->link_id && is_resource($this->link_id) && !mysql_error($this->link_id)) {
      $this->database && $this->select_db($this->database);
    } else {
      $this->error("Unable to connect to database: ".mysql_error()."<br>");
      unset($this->link_id);
    }
  
  } 

  function error($msg=false) {
    strlen($msg) && $this->verbose and print "<br/>".$msg;
    $this->error = $msg;
  }

  function get_error() {
    return trim($this->error);
  }
  
  function esc($str) {
    // If they're using magic_quotes_gpc then we gotta strip the 
    // automatically added backslashes otherwise they'll be added again..
    if (get_magic_quotes_gpc()) {
      $str = stripslashes($str);
    }
    $esc_function = "mysql_escape_string";
    if (version_compare(phpversion(), "4.3.0", ">")) {
      $esc_function = "mysql_real_escape_string";
    }
    
    if (is_numeric($str)) {
      return $str;
    }
    return "'".$esc_function($str)."'";
  }

  function select_db($db="") { 

    static $selected;

    if (!$selected || $selected != $db) {
      // Select a database
      if (mysql_select_db($db)) {
        $this->database = $db;
        $selected = $db;
      } else {
        $this->error("<b>Could not select database: ".$db."</b>"); 
      }
    }
  } 

  function qr() {
    // Quick Row run it like this:
    // $row = $db->qr("SELECT * FROM hey WHERE heyID = %s",$heyID);
    // sprintf is applied! sprintf arguments will be automatically escaped!
    $args = func_get_args();
    $query = $this->get_escaped_query_str($args);
    $this->query($query);
    return $this->row();
  }

  function query() {
    $this->connect();
    $args = func_get_args();
    $query = $this->get_escaped_query_str($args);
    $id = mysql_query($query);
    if ($id && is_resource($this->link_id) && !mysql_error($this->link_id)) {
      $this->query_id = $id;
      $rtn = $this->query_id;
      $this->error();
    } else {
      $rtn = false;
      is_resource($this->link_id) and $str = mysql_error($this->link_id);
      $this->error("Query failed: ".$str."<br><pre>".$query."</pre>");
    }
    return $rtn;
  } 

  function num($query_id="") {
    $id = $query_id or $id = $this->query_id;
    if (is_resource($id)) return mysql_num_rows($id);
  } 

  function num_rows($query_id="") {
    return $this->num($query_id);
  } 

  function row($query_id="",$method=MYSQL_ASSOC) { 
    $id = $query_id or $id = $this->query_id;
    if (is_resource($id)) {
      unset($this->row);
      $this->row =  mysql_fetch_array($id,$method);
      $this->Record = $this->row;
      return $this->row;
    }
  } 

  function next_record() {
    return $this->row();
  }

  function f($name) {
    return $this->row[$name];
  }

  // Return true if a particular table exists
  function table_exists($table,$db="") {
    $db or $db = $this->database;
    $prev_db = $this->database;
    $this->select_db($db);
    $query = sprintf('SHOW TABLES LIKE "%s"',$table);
    $this->query($query);
    while ($row = $this->row($this->query_id,MYSQL_NUM)) {
      if ($row[0] == $table) $yep = true;
    }
    $this->select_db($prev_db);
    return $yep;
  }

  function get_table_fields($table) {
    static $fields;

    if ($fields[$table]) {
      return $fields[$table];
    }
    $database = $this->database;
    if (strstr($table,".")) {
      list($database,$table) = explode(".",$table);
    }

    $list = mysql_list_fields($database, $table);
    $cols = mysql_num_fields($list);
    $i = 0;
    while ($i < $cols) {
      $fields[$table][] = mysql_field_name($list, $i);
      $i++;
    }
    $fields[$table] or $fields[$table] = array();
    return $fields[$table];
  }

  function get_table_keys($table) {
    static $keys;
    if ($keys[$table]) {
      return $keys[$table];
    }
    
    $this->query(sprintf("SHOW KEYS FROM %s",$table));
    while ($row = $this->row()) {
      if (!$row["Non_unique"]) {
        $keys[$table][] = $row["Column_name"]; 
      }
    }
    return $keys[$table];
  }

  function save($table, $row=array(), $debug=0) {
    $table_keys = $this->get_table_keys($table) or $table_keys = array();
    foreach ($table_keys as $k) {
      !$row[$k] and $do_insert = true;
      $keys[$k] = $row[$k]; 
    }
    $row = $this->unset_invalid_field_names($table, $row, $keys);

    if ($do_insert) {
      $q = sprintf("INSERT INTO %s (%s) VALUES (%s)"
                  , $table, $this->get_insert_str_fields($row), $this->get_insert_str_values($row));
      $debug &&  sizeof($row) and print ("<br>SAVE -> INSERT -> Would have executed this query: <br>".$q);
      $debug && !sizeof($row) and print ("<br>SAVE -> INSERT -> Would NOT have executed this query: <br>".$q);
      !$debug && sizeof($row) and $this->query($q);
      if (mysql_affected_rows() != 0) { 
        return mysql_insert_id(); // The primary key needs to be of type AUTO_INCREMENT for this to work.
      }
    } else {
      $q = sprintf("UPDATE %s SET %s WHERE %s"
                  , $table, $this->get_update_str($row), $this->get_update_str($keys, " AND "));
      $debug &&  sizeof($row) and print ("<br>SAVE -> UPDATE -> Would have executed this query: <br>".$q);
      $debug && !sizeof($row) and print ("<br>SAVE -> UPDATE -> Would NOT have executed this query: <br>".$q);
      !$debug && sizeof($row) and $this->query($q);
      return current($keys);
    }
  }

  function delete($table, $row=array(), $debug=0) {
    $row = $this->unset_invalid_field_names($table, $row);
    $q = sprintf("DELETE FROM %s WHERE %s"
                 , $table, $this->get_update_str($row, " AND "));
    $debug &&  sizeof($row) and print ("<br>DELETE -> WILL execute this query: <br>".$q);
    $debug && !sizeof($row) and print ("<br>DELETE -> WONT execute this query: <br>".$q);
    sizeof($row) and $this->query($q);
    return mysql_affected_rows();
  }

  function get_insert_str_fields($row) {
    foreach ($row as $fieldname => $value) {
      $rtn .= $commar.$fieldname;
      $commar = ", ";
    }
    return $rtn;
  }

  function get_insert_str_values($row) {
    foreach ($row as $fieldname => $value) {
      $rtn .= $commar.$this->esc($value);
      $commar = ", ";
    }
    return $rtn;
  }

  function get_update_str($row, $glue=", ") {
    foreach ($row as $fieldname => $value) {
      $rtn .= $commar." ".$fieldname." = ".$this->esc($value);
      $commar = $glue;
    }
    return $rtn;
  }

  function unset_invalid_field_names($table, $row, $keys=array()) {
    $valid_field_names = $this->get_table_fields($table);
    $keys = array_keys($keys);
    
    foreach ($row as $field_name => $v) {
      if (!in_array($field_name, $valid_field_names) || in_array($field_name, $keys)) {
        unset($row[$field_name]);
      }
    }
    $row or $row = array();
    return $row;
  }

  function get_escaped_query_str($args) {

    // If they've only passed a query then no substitution is required
    if (count($args) == 1) {
      return $args[0];
    }

    // First element of $args get assigned to zero index of $clean_args
    // Array_shift removes the first value and returns it..
    $clean_args[] = array_shift($args);

    // The rest of $args are escaped and then assigned to $clean_args
    foreach ($args as $arg) {
      $clean_args[] = $this->esc($arg);
    } 

    // Have to use this coz we don't know how many args we're gonna pass to sprintf..
    $query = call_user_func_array("sprintf",$clean_args); 
    return $query;
  }

  function seek($pos = 0) {
    $status = @mysql_data_seek($this->query_id, $pos);
    if ($status) {
      $this->pos = $pos;
    } else {
      /* half assed attempt to save the day, but do not consider this documented or even desireable behaviour. */
      @mysql_data_seek($this->query_id, $this->num_rows());
      $this->pos = $this->num_rows;
      return 0;
    }
    return 1;
  }

  function get_db_version() {
    $a = mysql_get_server_info($this->link_id);
    $b = substr($a, 0, strpos($a, "-"));
    return $b;
  }

}




?>
