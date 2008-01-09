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

class htmlAttribute extends db_entity {
  var $data_table = "htmlAttribute";

  function htmlAttribute() {
    $this->db_entity();
    $this->key_field = new db_field("htmlAttributeID");
    $this->data_fields = array("htmlElementID"=>new db_field("htmlElementID")
                              ,"name"=>new db_field("name")
                              ,"value"=>new db_field("value")
                              ,"isDefault"=>new db_field("isDefault")
      );
  }

  function get_list($htmlElementID) {
    $rows = array();
    $db = new db_alloc();
    $q = sprintf("SELECT * FROM htmlAttribute WHERE htmlElementID = %d",$htmlElementID);
    $db->query($q);
    while ($row = $db->row()) {
      $rows[] = $row;
    }
    return $rows;
  }


}



?>
