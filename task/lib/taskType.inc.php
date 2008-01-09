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

class taskType extends db_entity {
  var $data_table = "taskType";
  var $display_field_name = "taskTypeName";

  function taskType() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("taskTypeID");
    $this->data_fields = array("taskTypeName"=>new db_field("taskTypeName")
                              ,"taskTypeActive"=>new db_field("taskTypeActive")
                              ,"taskTypeSequence"=>new db_field("taskTypeSequence")
                              );
  }
}



?>
