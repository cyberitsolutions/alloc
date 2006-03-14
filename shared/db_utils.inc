<?php
// When setting a field's value these constants distinguish between the source of a value
  // This allows automatic conversions to take place
  // e.g. convert a serialized object in the database in to a PHP object to be held in a PHP variable.
define("SRC_DATABASE", 1);      // Reading the value from the database
define("SRC_VARIABLE", 2);      // Reading the value from a PHP variable (except a form variable)
define("SRC_REQUEST", 3);       // Reading the value from a get or post variable

  // When retrieving a field's value these constants distinguish between the destination of the value
define("DST_DATABASE", 1);      // For writing to a database
define("DST_VARIABLE", 2);      // For use within the PHP script itself
define("DST_HTML_ATTRIBUTE", 3);        // For use in a HTML elements attribute - e.g. a form input's value or a link's href
define("DST_HTML_DISPLAY", 4);  // For display to the user as non-editable HTML text
include($MOD_SHARED_DIR."/db_field.inc");
include($MOD_SHARED_DIR."/db_filter.inc");
include($MOD_SHARED_DIR."/db_list.inc");
include($MOD_SHARED_DIR."/db_entity.inc");

  // Make a where clause from arrays of the form field_name=> field_value
  // For each array item in where fields the where clause is constructed as follows:
  // field_name value_prefix field_value value_suffix
  // Items are 'and'd together
  // An existing where clause can be past as the where_clause parameter in which case additional criteria are added to it
function make_where_clause($where_fields, $value_prefix, $value_suffix, $where_clause = "", $include_empty_values = false) {
  reset($where_fields);
  while (list($field_name, $field_value) = each($where_fields)) {
    if ($field_value || $include_empty_values) {
      if ($where_clause) {
        $where_clause.= " AND ";
      }
      $where_clause.= $field_name." ".$value_prefix.addslashes($field_value).$value_suffix." ";
    }
  }
  return $where_clause;
}


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


  // Convert date from database format (yyyy-mm-dd) to display format (d/m/yyyy)
function get_db_date($display_date) {
  if (ereg("([0-9]*)/([0-9]*)/([0-9]*)", $display_date, $matches)) {
    return sprintf("%d-%02d-%02d", $matches[3], $matches[2], $matches[1]);
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
 * Builds up options for use in a html select widget (works with multiple selected)
 *
 * @param   $options          mixed   An sql query or an array of options
 * @param   $selected_value   string  The current selected element
 * @return                    string  The string of options
 */
function get_select_options($options,$selected_value=NULL) {

  // Build options from an SQL query: "SELECT name,value FROM"
  if (is_string($options)) {
    $db = run_query($options);
    while ($row = $db->row()) {
      $rows[current($row)] = end($row);
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

      // If an array of selected values!
      if (is_array($selected_value)) {
        foreach ($selected_value as $id) {
          $id == $value and $sel = " selected";
        }
      } else {
        $selected_value == $value and $sel = " selected";
      }

      $label = stripslashes($label);
      if (strlen($label) > 45) {
        $label = substr($label, 0, 45 - 3)."...";
      } 

      $str.= "\n<option value=\"".$value."\"".$sel.">".$label."</option>";
    }
  }
  return $str;
}



  // Get options for a <select> using an array of the form value=>label
function get_options_from_array($options, $selected_value, $use_values = true, $max_label_length = 40, $bitwise_values = false, $reverse_results = false) {
  $rtn = "";
  reset($options);
  if ($reverse_results) {
    $options = array_reverse($options, TRUE);
  }
  while (list($value, $label) = each($options)) {
    if ($rtn) {
      $rtn.= "\n";
    }
    $rtn.= "<option";
    if ($use_values) {
      $rtn.= " value=\"$value\"";

#echo "($value == $selected_value || ($bitwise_values && (($selected_value & $value) == $value)))<br>";
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
  return mysql_escape_string($str);
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
