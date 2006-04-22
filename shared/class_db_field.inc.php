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

class db_field {
  var $classname = "db_field";
  var $persistent_slots = array("name", "value", "label", "empty_to_null", "allow_null", "write_perm_name");
  var $name;
  var $value;
  var $label;
  var $empty_to_null = true;
  var $allow_null = true;
  var $write_perm_name = 0;     // Name of a permission a user must have to write to this field, if any.  E.g. "admin"
  var $read_perm_name = 0;      // Name of the permission a user must have to read this field, if any.  E.g. "read details"

  function db_field($name = "", $label = "", $value = "", $options = "") {
    $this->name = $name;
    if ($this->label) {
      $this->label = $label;
    } else {
      $this->label = $name;
    }
    $this->value = $value;

    if ($options == "")
      $options = array();
    reset($options);
    while (list($option_name, $option_value) = each($options)) {
      $this->$option_name = $option_value;
    }
  }

  function set_value($value, $source = SRC_VARIABLE) {
    if (isset($value)) {
      $this->value = $value;
    }
  }

  function has_value() {
    return isset($this->value) && $this->value != "";
  }

  function get_name() {
    return $this->name;
  }

  function get_value($dest = DST_VARIABLE) {
    if ($dest == DST_DATABASE) {
      if ((isset($this->value) && $this->value != "") || !$this->empty_to_null) {
        return "'".addslashes($this->value)."'";
      } else {

        return "NULL";
      }
    } else {
      return $this->value;
    }
  }

  function clear_value() {
    unset($this->value);
  }

  function validate() {
    if (((!isset($this->value)) || $this->value == "") && $this->empty_to_null && !$this->allow_null) {
      return "You must enter a value for ".$this->label."\n";
    }
  }
}

class db_text_field extends db_field {
  var $classname = "db_text_field";
}

class db_object_field extends db_field {
  var $classname = "db_object_field";
  var $object_value;

  function set_value($value, $source = SRC_VARIABLE) {
    if (!isset($value)) {
      return;
    }

    if ($source == SRC_DATABASE || $source == SRC_REQUEST) {
      $this->value = unserialize($value);
    } else if ($source == SRC_VARIABLE) {
      $this->value = $value;
    } else {
      die("Unexpected source setting object field value: ".$source);
    }
  }

  function get_value($target = DST_VARIABLE) {
    if ($target == DST_VARIABLE) {
      return $this->value;
    } else if ($target == DST_DATABASE) {
      return "'".addslashes(serialize($this->value))."'";
    } else if ($target == DST_HTML_ATTRIBUTE) {
      return serialize($this->value);
    } else if ($target == DST_HTML_DISPLAY) {
      if (is_object($this->value)) {
        return $this->value->get_display_value();
      } else {
        return "";
      }
    } else {
      die("Unrecognized destination for object field value: ".$target);
    }
  }
}



?>
