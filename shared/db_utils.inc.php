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

// When setting a field's value these constants distinguish between the source of a value
// This allows automatic conversions to take place
// e.g. convert a serialized object in the database in to a PHP object to be held in a PHP variable.
define("SRC_DATABASE", 1);      // Reading the value from the database
define("SRC_VARIABLE", 2);      // Reading the value from a PHP variable (except a form variable)
define("SRC_REQUEST",  3);      // Reading the value from a get or post variable

// When retrieving a field's value these constants distinguish between the destination of the value
define("DST_DATABASE",       1);  // For writing to a database
define("DST_VARIABLE",       2);  // For use within the PHP script itself
define("DST_HTML_ATTRIBUTE", 3);  // For use in a HTML elements attribute - e.g. a form input's value or a link's href
define("DST_HTML_DISPLAY",   4);  // For display to the user as non-editable HTML text


  // Convert date from database format (yyyy-mm-dd) to display format (d/m/yyyy)
  function get_display_date($db_date) {
    if ($db_date == "0000-00-00 00:00:00") {
      return "";
    } else if (ereg("([0-9]{4})-?([0-9]{2})-?([0-9]{2})", $db_date, $matches)) {
      return sprintf("%d/%d/%d", $matches[3], $matches[2], $matches[1]);
    } else {
      return "";
    }
  }


  // Converts from DB date string of YYYY-MM-DD to a Unix time stamp
  function get_date_stamp($db_date) {
    ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2})", $db_date, $matches);
    $date_stamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    return $date_stamp;
  }


  // Converts mysql timestamp 20011024161045 to YYYY-MM-DD - AL
  function get_mysql_date_stamp($db_date) {
    ereg("^([0-9]{4})([0-9]{2})([0-9]{2})", $db_date, $matches);
    $date_stamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    $date = date("Y", $date_stamp)."-".date("m", $date_stamp)."-".date("d", $date_stamp);
    return $date;
  }

 /**
 * Builds up options for use in a html select widget (works with multiple selected too)
 *
 * @param   $options          mixed   An sql query or an array of options
 * @param   $selected_value   string  The current selected element
 * @param   $max_length       int     The maximum string length of the label
 * @return                    string  The string of options
 */
  function get_select_options($options,$selected_value=NULL,$max_length=45) {

    // Build options from an SQL query: "SELECT col_a as name, col_b as value FROM"
    if (is_string($options)) {
      $db = new db_alloc;
      $db->query($options);
      while ($row = $db->row()) {
        $rows[$row["name"]] = $row["value"];
      }

    // Build options from an array: array(array("name1","value1"),array("name2","value2"))
    } else if (is_array($options)) {
      foreach ($options as $k => $v) {
        $rows[$k] = $v;
      }
    }

    if (is_array($rows)) {
      foreach ($rows as $value=>$label) {
        $sel = "";

        if (!$value && $value!==0 && !$value!=="0" && $label) {
          $value = $label; 
        }
        !$label && $value and $label = $value;

        // If an array of selected values!
        if (is_array($selected_value)) {
          foreach ($selected_value as $id) {
            $id == $value and $sel = " selected";
          }
        } else {
          $selected_value == $value and $sel = " selected";
        }

        $label = stripslashes($label);
        if (strlen($label) > $max_length) {
          $label = substr($label, 0, $max_length - 3)."...";
        } 

        $str.= "\n<option value=\"".$value."\"".$sel.">".$label."</option>";
      }
    }
    return $str;
  }



  // Get options for a <select> using an array of the form value=>label
  function get_options_from_array($options, $selected_value, $use_values = true, $max_label_length = 40, $bitwise_values = false, $reverse_results = false) {
    is_array($options) or $options = array();

    if ($reverse_results) {
      $options = array_reverse($options, TRUE);
    }
    foreach ($options as $value => $label) {
      $rtn.= "\n<option";
      if ($use_values) {
        $rtn.= " value=\"$value\"";

        if ($value == $selected_value || ($bitwise_values && (($selected_value & $value) == $value))) {
          $rtn.= " selected";
        }
      } else {
        $rtn.= " value=\"$label\"";
        if ($label == $selected_value) {
          $rtn.= " selected";
        }
      }
      $rtn.= ">";
      $label = stripslashes($label);
      if (strlen($label) > $max_label_length) {
        $rtn.= substr($label, 0, $max_label_length - 3)."...";
      } else {
        $rtn.= $label;
      }
      $rtn.= "</option>";
    }
    return $rtn;
  }


  // Constructs an array from a database containing 
  // $key_field=>$label_field entries
  // ALLA: Edited function so that an array of 
  // label_field could be passed $return is the 
  // _complete_ label string.
  // TODO: Make this function SORT
  function get_array_from_db($db, $key_field, $label_field) {
    $rtn = array();
    while ($db->next_record()) {
      if (is_array($label_field)) {
        $return = "";
        foreach($label_field as $key=>$label) {

          // Every second array element (starting with zero) will 
          // be the string separator. This really isn't quite as 
          // lame as it seems.  Although it's close.
          if (!is_int($key / 2)) {
            $return.= $db->f($label);
          } else {
            $return.= $label;
          }
        }
      } else {
        $return = $db->f($label_field);
      }
      if ($key_field) {
        $rtn[$db->f($key_field)] = stripslashes($return);
      } else {
        $rtn[] = stripslashes($return);
      }
    }
    return $rtn;
  }


  // Get options for a <select> using a database object
  function get_options_from_db($db, $label_field, $value_field = "", $selected_value, $max_label_length = 40, $reverse_results = false) {
    $options = get_array_from_db($db, $value_field, $label_field);
    return get_options_from_array($options, $selected_value, $value_field != "", $max_label_length, $bitwise_values = false, $reverse_results);
  }

  function get_options_from_query($query, $label_field, $value_field = "", $selected_value) {
    $db = new db_alloc;
    $db->query($query);
    return get_options_from_db($db, $label_field, $value_field, $selected_value);
  }

  function format_nav_links($nav_links) {
    return implode(" | ", $nav_links);
  }

  function get_tf_name($tfID) {
    if (!$tfID) {
      return false;
    } else {
      $db = new db_alloc;
      $db->query("select tfName from tf where tfID= ".$tfID);
      $db->next_record();
      return $db->f("tfName");
    }
  }

  function get_project_name($projectID) {
    if (!$projectID) {
      return false;
    } else {
      $db = new db_alloc;
      $db->query("select projectName from project where projectID= ".$projectID);
      $db->next_record();
      return $db->f("projectName");
    }
  }


  // wrapper
  function db_esc($str = "") {
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
    return $esc_function($str);
  }

// Okay so $value can be like eg: $where["status"] = array(" LIKE ","hey")
// Or $where["status"] = "hey";
  function db_get_where($where = array()) {
    foreach($where as $column_name=>$value) {
      $op = " = ";
      if (is_array($value)) {
        $op = $value[0];
        $value = $value[1];
      }
      $rtn.= " ".$and.$column_name.$op." '".db_esc($value)."'";
      $and = " AND ";
    }
    return $rtn;
  }


// Date formatting
  function format_date($format="Y/m/d", $date="") {

    // If looks like this: 2003-07-07 21:37:01
    if (preg_match("/^[\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}$/",$date)) {
      list($d,$t) = explode(" ", $date);

    // If looks like this: 2003-07-07
    } else if (preg_match("/^[\d]{4}-[\d]{2}-[\d]{2}$/",$date)) {
      $d = $date;

    // If looks like this: 12:01:01
    } else if (preg_match("/^[\d]{2}:[\d]{2}:[\d]{2}$/",$date)) {
      $d = "2000-01-01";
      $t = $date;

    // Nasty hobbitses!
    } else {
      return "Date unrecognized: ".$date;
    }

    list($y,$m,$d) = explode("-", $d);
    list($h,$i,$s) = explode(":", $t);
    return date($format, mktime(date($h),date($i),date($s),date($m),date($d),date($y)));
  }









?>
