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

class role extends db_entity {
  public $data_table = "role";
  public $key_field = "roleID";
  public $data_fields = array("roleHandle"
                             ,"roleName"
                             ,"roleLevel"
                             ,"roleSequence"
                             );

  function get_roles_array($level="person") {
    $rows = array();
    $db = new db_alloc();
    $q = prepare("SELECT * FROM role WHERE roleLevel = '%s' ORDER BY roleSequence",$level);
    $db->query($q);
    while ($row = $db->row()) {
      $rows[$row["roleHandle"]] = $row["roleName"];
    }
    return $rows;
  }
  


}



?>
