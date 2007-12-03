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

class config extends db_entity {
  var $data_table = "config";

  function config() {
    $this->db_entity();
    $this->key_field = new db_field("configID");
    $this->data_fields = array("name"=>new db_field("name")
                              ,"value"=>new db_field("value")
                              ,"type"=>new db_field("type")
      );
  }

  function get_config_item($name='',$anew=false) {
    $table = get_cached_table("config",$anew);
    if ($table[$name]["type"] == "array") {
      $val = unserialize($table[$name]["value"]) or $val = array();
      return $val;

    } else if ($table[$name]["type"] == "text") {
      $val = $table[$name]["value"];
      return $val;
    }
  }

  function get_config_item_id($name='') {
    $db = new db_alloc;
    $db->query(sprintf("SELECT configID FROM config WHERE name = '%s'",$name));
    $db->next_record();
    return $db->f('configID');
  }

  function for_cyber() {
    return config::get_config_item("companyHandle") == "cybersource";
  }

}



?>
