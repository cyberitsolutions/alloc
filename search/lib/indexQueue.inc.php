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

class indexQueue extends db_entity {
  public $classname = "indexQueue";
  public $data_table = "indexQueue";
  public $key_field = "indexQueueID";
  public $data_fields = array("entity"
                             ,"entityID"
                             );

  
  function save() {
    $q = prepare("SELECT * FROM indexQueue WHERE entity = '%s' AND entityID = %d"
                ,$this->get_value("entity"),$this->get_value("entityID"));
    $db = new db_alloc();
    $db->query($q);
    if (!$db->row()) {
      return parent::save();
    }
  }

}
?>
