<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */


class timeUnit extends db_entity
{
  var $classname = "timeUnit";
  var $data_table = "timeUnit";
  var $display_field_name = "timeUnitLabelA";

  function timeUnit() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("timeUnitID");
    $this->data_fields = array("timeUnitName"=>new db_text_field("timeUnitName")
                               ,"timeUnitLabelA"=>new db_text_field("timeUnitLabelA")
                               ,"timeUnitLabelB"=>new db_text_field("timeUnitLabelB")
                               ,"timeUnitSeconds"=>new db_text_field("timeUnitSeconds")
                               ,"timeUnitActive"=>new db_text_field("timeUnitActive")
                               ,"timeUnitSequence"=>new db_text_field("timeUnitSequence")
        );
  }


  function seconds_to_display_time_unit($seconds) {
    $q = "SELECT * FROM timeUnit";
    $db = new db_alloc;
    $db->query($q);
    while ($db->next_record()) {
      //blag someother time
    }  

  }



}  




?>
