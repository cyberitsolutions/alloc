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

class permission extends db_entity {
  public $data_table = "permission";
  public $display_field_name = "tableName";
  public $key_field = "permissionID";
  public $data_fields = array("tableName"
                             ,"entityID"
                             ,"personID"=>array("empty_to_null"=>true)
                             ,"roleName"=>array("empty_to_null"=>false)
                             ,"actions"
                             ,"sortKey"
                             ,"allow"
                             ,"comment"
                             );

  function describe_actions() {
    $actions = $this->get_value("actions");
    $description = "";

    $entity_class = $this->get_value("tableName");
    $entity = new $entity_class;
    $permissions = $entity->permissions;

    reset($permissions);
    while (list($a, $d) = each($permissions)) {
      if ((($actions & $a) == $a) && $d != "") {
        if ($description) {
          $description.= ",";
        }
        $description.= $d;
      }
    }

    return $description;
  }
}



?>
