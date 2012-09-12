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
  public static $started_transaction = false;
  public static $stop_doing_queries = false;

  function __construct($username="",$password="",$hostname="",$database="") { // Constructor
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
    if (!$this->link_id) {
      $this->link_id = mysql_connect($this->hostname,$this->username,$this->password);
      if ($this->link_id && is_resource($this->link_id) && !$this->error) {
        $this->database && $this->select_db($this->database);
        // Force connection to use utf-8
        function_exists("mysql_set_charset") && mysql_set_charset("utf8", $this->link_id);

      } else {
        $this->error("Unable to connect to database: ".mysql_error()."<br>",mysql_errno());
        unset($this->link_id);
      }
    }
    return $this->link_id;
  } 

  function start_transaction() {
    $this->query("SET autocommit=0");
    $this->query("START TRANSACTION");
    self::$started_transaction = true;
  }

  function commit() {
    if (self::$started_transaction) {
      $this->query("SET autocommit=1");
      $this->query("COMMIT");
    }
  }

  function error($msg=false,$errno=false) {
    if ($errno == 1451 || $errno == 1217) { 
      $m = "Error: ".$errno." There are other records in the database that depend on the item you just tried to delete. 
            Remove those other records first and then try to delete this item again. 
            <br><br>".$msg;

    } else if ($errno == 1216) {
      $m = "Error: ".$errno." The parent record of the item you just tried to create does not exist in the database. 
            Create that other record first and then try to create this item again. 
            <br><br>".$msg;

    } else if (preg_match("/(ALLOC ERROR:(.*)\n\n)/m",$msg,$matches)) {
      $m = "Error: ".$matches[2];

    } else if ($msg || $errno) {
      $m = "Error: ".$errno." ".$msg;
    }

    if ($m) {
      alloc_error($m);
    }

    $this->error = $msg;
  }

  function get_error() {
    return trim($this->error);
  }

  function get_insert_id() {
    if ($this->link_id) {
      return @mysql_insert_id($this->link_id);
    }
  }
  
  function esc($str) {
    $esc_function = "mysql_escape_string";
    if (version_compare(phpversion(), "4.3.0", ">")) {
      $esc_function = "mysql_real_escape_string";
    }
    
    if (is_numeric($str)) {
      return $str;
    }
    $rtn = @$esc_function($str);
    if ($rtn === false) {
      $err = error_get_last();
      $e = new Exception();
      alloc_error("Error in db->esc(".$str."): \n".$e->getTraceAsString()."\n".print_r($err,1));
    } else {
      return $rtn;
    }
  }

  function select_db($db="") { 
    static $selected;

    if (!$selected || $selected != $db) {
      // Select a database
      if (mysql_select_db($db)) {
        $this->database = $db;
        $selected = $db;
        return true;
      } else {
        $this->error("<b>Could not select database: ".$db."</b>",mysql_errno()); 
        return false;
      }
    }
    return true;
  } 

  function qr() {
    // Quick Row run it like this:
    // $row = $db->qr("SELECT * FROM hey WHERE heyID = %d",$heyID);
    // arguments will be automatically escaped
    $args = func_get_args();
    $query = $this->get_escaped_query_str($args);
    $id = $this->query($query);
    return $this->row($id);
  }

  private function _query($query) {
    // wrapper for mysql_query
    if (!self::$stop_doing_queries || $query == "ROLLBACK") {
      return @mysql_query($query);
    }
  }

  function query() {
    global $TPL;
    $current_user = &singleton("current_user");
    $start = microtime();
    $this->connect();
    $args = func_get_args();
    $query = $this->get_escaped_query_str($args);
    #echo "<br><br>Query: ".$query;
    #echo "<br><pre>".print_r(debug_backtrace(),1)."</pre>";

    if ($query && !self::$stop_doing_queries) {

      if (is_object($current_user) && method_exists($current_user,"get_id") && $current_user->get_id()) {
        $this->_query(prepare("SET @personID = %d",$current_user->get_id()),$this->link_id);
      } else {
        $this->_query("SET @personID = NULL",$this->link_id);
      }

      $id = $this->_query($query,$this->link_id);

      if ($str = mysql_error()) {
        $rtn = false;
        $this->error("Query failed: ".$str."\n".$query,mysql_errno());
        if (self::$started_transaction) {
          $this->_query("ROLLBACK");
          self::$started_transaction = false;
        }

      } else if ($id) {
        $this->query_id = $id;
        $rtn = $this->query_id;
        $this->error();
      }
    } else if (self::$stop_doing_queries) {
      //alloc_error("DB queries halted. Will not execute: ".$query);
    }

    $result = timetook($start,false);
    if ($result > $TPL["slowest_query_time"]) {
      $TPL["slowest_query"] = $query;
      $TPL["slowest_query_time"] = $result;
    }
    $TPL["all_page_queries"][] = array("time"=>$result, "query"=>$query);
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
    if (!self::$stop_doing_queries) {
      $id = $query_id or $id = $this->query_id;
      if (is_resource($id)) {
        unset($this->row);
        $this->row = mysql_fetch_array($id,$method);
        return $this->row;
      }
    }
  } 

  // DEPRACATED
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
    $query = prepare('SHOW TABLES LIKE "%s"',$table);
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
    
    $this->query("SHOW KEYS FROM %s",$table);
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
      $row[$k] and $do_update = true;
      $keys[$k] = $row[$k]; 
    }
    $row = $this->unset_invalid_field_names($table, $row, $keys);

    if ($do_update) {
      $q = sprintf("UPDATE %s SET %s WHERE %s"
                  , $table, $this->get_update_str($row), $this->get_update_str($keys, " AND "));
      $debug &&  sizeof($row) and print ("<br>SAVE -> UPDATE -> Would have executed this query: <br>".$q);
      $debug && !sizeof($row) and print ("<br>SAVE -> UPDATE -> Would NOT have executed this query: <br>".$q);
      !$debug && sizeof($row) and $this->query($q);
      reset($keys);
      return current($keys);

   } else {
      $q = sprintf("INSERT INTO %s (%s) VALUES (%s)"
                  , $table, $this->get_insert_str_fields($row), $this->get_insert_str_values($row));
      $debug &&  sizeof($row) and print ("<br>SAVE -> INSERT -> Would have executed this query: <br>".$q);
      $debug && !sizeof($row) and print ("<br>SAVE -> INSERT -> Would NOT have executed this query: <br>".$q);
      !$debug && sizeof($row) and $this->query($q);
      if (mysql_affected_rows() != 0) { 
        return mysql_insert_id(); // The primary key needs to be of type AUTO_INCREMENT for this to work.
      }
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
    return call_user_func_array("prepare",$args);
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

  function get_encoding() {
    return mysql_client_encoding($this->link_id);
  }

  function get_db_version() {
    $link_id = $this->link_id;
    if (!$link_id) {
      $link_id = @mysql_connect($this->hostname);
    }
    $a = @mysql_get_server_info($link_id);
    $b = substr($a, 0, strpos($a, "-"));
    return $b;
  }

  function dump_db($filename) {
    if ($this->password) {
      $pw = " -p" . $this->password;
    }
    $command = sprintf("mysqldump -B -c --add-drop-table -h %s -u %s %s %s", $this->hostname, $this->username, $pw, $this->database);

    $command .= " >" . $filename;

    system($command);
  }

}




?>
