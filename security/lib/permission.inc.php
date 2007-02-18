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

class permission extends db_entity {
  var $data_table = "permission";
  var $display_field_name = "tableName";

  function permission() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("permissionID");
    $this->data_fields = array("tableName"=>new db_field("tableName")
                               , "entityID"=>new db_field("entityID", array("empty_to_null"=>false))
                               , "personID"=>new db_field("personID", array("empty_to_null"=>false))
                               , "roleName"=>new db_field("roleName", array("empty_to_null"=>false))
                               , "actions"=>new db_field("actions")
                               , "sortKey"=>new db_field("sortKey")
                               , "allow"=>new db_field("allow")
                               , "comment"=>new db_field("comment")
      );
  }

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
