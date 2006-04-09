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

class item extends db_entity {
  var $data_table = "item";
  var $display_field_name = "itemName";


  function item() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("itemID");
    $this->data_fields = array("itemModifiedUser"=>new db_text_field("itemModifiedUser")
                               , "itemName"=>new db_text_field("itemName")
                               , "itemAuthor"=>new db_text_field("itemAuthor")
                               , "itemNotes"=>new db_text_field("itemNotes")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "itemType"=>new db_text_field("itemType")
			       , "personID"=>new db_text_field("personID")
      );
  }



}



?>
