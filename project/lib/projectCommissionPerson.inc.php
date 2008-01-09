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

class projectCommissionPerson extends db_entity {
  var $data_table = "projectCommissionPerson";
  var $display_field_name = "projectID";

  function projectCommissionPerson() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("projectCommissionPersonID");
    $this->data_fields = array("projectID"=>new db_field("projectID")
                               , "tfID"=>new db_field("tfID")
                               , "commissionPercent"=>new db_field("commissionPercent")
      );
  }


  function is_owner($person = "") {
    $project = new project;
    $project->set_id($this->get_value("projectID"));
    $project->select();
    return $project->is_owner($person);
  }

  function save() {
    global $TPL;
    // Just ensure multiple 0 entries cannot be saved.
    if ($this->get_value("commissionPercent") == 0) {
      $q = sprintf("SELECT * FROM projectCommissionPerson WHERE projectID = %d AND commissionPercent = 0",$this->get_value("projectID"));
      $db = new db_alloc();
      $db->query($q);
      if ($db->next_record()) { 
        $fail = true;
        $TPL["message"][] = "Only one Time Sheet Commission is allowed to be set to 0%";
      }
    }
    if (!$fail) {
      parent::save();
    }
  }


}



?>
