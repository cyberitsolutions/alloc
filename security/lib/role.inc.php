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

class role extends db_entity {
  var $data_table = "role";

  function role() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("roleID");
    $this->data_fields = array("roleHandle"=>new db_field("roleHandle")
                              ,"roleName"=>new db_field("roleName")
                              ,"roleLevel"=>new db_field("roleLevel")
                              ,"roleSequence"=>new db_field("roleSequence")
      );
  }

  function get_roles_array($level="person") {
    $rows = array();
    $db = new db_alloc();
    $q = sprintf("SELECT * FROM role WHERE roleLevel = '%s' ORDER BY roleSequence",db_esc($level));
    $db->query($q);
    while ($row = $db->row()) {
      $rows[$row["roleHandle"]] = $row["roleName"];
    }
    return $rows;
  }
  


}



?>
