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

class db_field {
  var $classname = "db_field";
  var $name;
  var $value;
  var $label;
  var $empty_to_null = true;

  var $audit = false;

  var $write_perm_name = 0;     // Name of a permission a user must have to write to this field, if any.  E.g. "admin"
  var $read_perm_name = 0;      // Name of the permission a user must have to read this field, if any.  E.g. "read details"

  function __construct($name = "", $options = array()) {
    $this->name = $name;
    $this->label = $name;

    if (!is_array($options)) {
      $options = array();
      #echo "<br>".$this->name;
    }
    reset($options);
    foreach ($options as $option_name => $option_value) {
      $this->$option_name = $option_value;
    }
  }

  function set_value($value, $source = SRC_VARIABLE) {
    if (isset($value) || $this->empty_to_null == false) {
      $this->value = $value;
    }
  }

  function has_value() {
    return isset($this->value) && imp($this->value);
  }

  function get_name() {
    return $this->name;
  }

  function is_audited() {
    return $this->audit;
  }

  function get_value($dest = DST_VARIABLE,$parent=null) {
    if ($dest == DST_DATABASE) {
      if ((isset($this->value) && imp($this->value)) || $this->empty_to_null == false) {
        return "'".db_esc($this->value)."'";
      } else {
        return "NULL";
      }
    } else if ($dest == DST_HTML_DISPLAY) {
      if ($this->type == "money" && imp($this->value)) {

        $c = $parent->currency;
        if ($this->currency && isset($parent->data_fields[$this->currency])) {
          $c = $parent->get_value($this->currency);
        }

        if (!$c) {
          alloc_error("db_field::get_value(): No currency specified for ".$parent->classname.".".$this->name." (currency:".$c.")");
        } else if ($this->value == $parent->all_row_fields[$this->name]) {
          return page::money($c,$this->value,"%mo");
        }
      }
      return page::htmlentities($this->value);
    } else {
      return $this->value;
    }
  }

  function clear_value() {
    unset($this->value);
  }

  function validate($parent) {
    global $TPL;
    if ($parent->doMoney && $this->type == "money") {
      $c = $parent->currency;
      if ($this->currency && isset($parent->data_fields[$this->currency])) {
        $c = $parent->get_value($this->currency);
      }
      if (!$c) {
        return "db_field::validate(): No currency specified for ".$parent->classname.".".$this->name." (currency:".$c.")";
      } else if ($this->value != $parent->all_row_fields[$this->name]) {
        $this->set_value(page::money($c,$this->value,"%mi"));
      }
    }
  }
}





?>
