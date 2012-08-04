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

class projectCommissionPerson extends db_entity {
  public $data_table = "projectCommissionPerson";
  public $display_field_name = "projectID";
  public $key_field = "projectCommissionPersonID";
  public $data_fields = array("projectID"
                             ,"tfID"
                             ,"commissionPercent"
                             );

  function is_owner($person = "") {
    $project = new project();
    $project->set_id($this->get_value("projectID"));
    $project->select();
    return $project->is_owner($person);
  }

  function save() {
    // Just ensure multiple 0 entries cannot be saved.
    if ($this->get_value("commissionPercent") == 0) {
      $q = prepare("SELECT * FROM projectCommissionPerson WHERE projectID = %d AND commissionPercent = 0 AND projectCommissionPersonID != %d",$this->get_value("projectID"), $this->get_id());
      $db = new db_alloc();
      $db->query($q);
      if ($db->next_record()) { 
        $fail = true;
        alloc_error("Only one Time Sheet Commission is allowed to be set to 0%");
      }
    }
    if (!$fail) {
      parent::save();
    }
  }


}



?>
