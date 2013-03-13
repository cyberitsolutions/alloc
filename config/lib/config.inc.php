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

class config extends db_entity {
  public $data_table = "config";
  public $key_field = "configID";
  public $data_fields = array("name"
                             ,"value"
                             ,"type"
                             );

  function get_config_item($name='',$anew=false) {
    $table =& get_cached_table("config",$anew);
    if ($table[$name]["type"] == "array") {
      $val = unserialize($table[$name]["value"]) or $val = array();
      return $val;

    } else if ($table[$name]["type"] == "text") {
      $val = $table[$name]["value"];
      return $val;
    }
  }

  function get_config_item_id($name='') {
    $db = new db_alloc();
    $db->query(prepare("SELECT configID FROM config WHERE name = '%s'",$name));
    $db->next_record();
    return $db->f('configID');
  }

  function get_config_logo($anew=false) {
    global $TPL;
    $table =& get_cached_table("config",$anew);
    $val = '';
    if(file_exists(ALLOC_LOGO)) {
      $val = '<img src="'.$TPL["url_alloc_logo"].'type=small" alt="'.$table['companyName']['value'].'" />';
    } else {
      $val = $table['companyName']['value'];
    }
    return $val;
  }

  function for_cyber() {
    return config::get_config_item("companyHandle") == "cybersource";
  }

}



?>
