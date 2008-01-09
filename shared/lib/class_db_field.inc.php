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

class db_field {
  var $classname = "db_field";
 // list of options
 // var $persistent_slots = array("name", "value", "label", "empty_to_null", "write_perm_name");
  var $name;
  var $value;
  var $label;
  var $empty_to_null = true;
  var $write_perm_name = 0;     // Name of a permission a user must have to write to this field, if any.  E.g. "admin"
  var $read_perm_name = 0;      // Name of the permission a user must have to read this field, if any.  E.g. "read details"

  function db_field($name = "", $options = array()) {
    $this->name = $name;
    $this->label = $name;

    if (!is_array($options)) {
      $options = array();
      #echo "<br/>".$this->name;
    }
    reset($options);
    foreach ($options as $option_name => $option_value) {
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
        return "'".db_esc($this->value)."'";
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

  // Holder
  function validate() {
  }
}





?>
