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

class client extends db_entity {
  var $data_table = "client";
  var $display_field_name = "clientName";


  function client() {
    $this->db_entity();
    $this->display_field_name = "clientName";
    $this->key_field = new db_text_field("clientID");
    $this->data_fields = array("clientName"=>new db_text_field("clientName")
                               , "clientPrimaryContactID"=>new db_text_field("clientPrimaryContactID")
                               , "clientStreetAddressOne"=>new db_text_field("clientStreetAddressOne")
                               , "clientStreetAddressTwo"=>new db_text_field("clientStreetAddressTwo")
                               // , "clientContactNameOne"=> new db_text_field("clientContactNameOne")
                               // , "clientContactNameTwo"=> new db_text_field("clientContactNameTwo")
                               , "clientSuburbOne"=>new db_text_field("clientSuburbOne")
                               , "clientSuburbTwo"=>new db_text_field("clientSuburbTwo")
                               , "clientStateOne"=>new db_text_field("clientStateOne")
                               , "clientStateTwo"=>new db_text_field("clientStateTwo")
                               , "clientPostcodeOne"=>new db_text_field("clientPostcodeOne")
                               , "clientPostcodeTwo"=>new db_text_field("clientPostcodeTwo")
                               , "clientPhoneOne"=>new db_text_field("clientPhoneOne")
                               // , "clientPhoneTwo"=> new db_text_field("clientPhoneTwo")
                               , "clientFaxOne"=>new db_text_field("clientFaxOne")
                               // , "clientFaxTwo"=> new db_text_field("clientFaxTwo")
                               // , "clientEmailOne"=> new db_text_field("clientEmailOne")
                               // , "clientEmailTwo"=> new db_text_field("clientEmailTwo")
                               , "clientCountryOne"=>new db_text_field("clientCountryOne")
                               , "clientCountryTwo"=>new db_text_field("clientCountryTwo")
                               , "clientComment"=>new db_text_field("clientComment")
                               , "clientCreatedTime"=>new db_text_field("clientCreatedTime")
                               , "clientModifiedTime"=>new db_text_field("clientModifiedTime")
                               , "clientModifiedUser"=>new db_text_field("clientModifiedUser")
                               , "clientStatus"=>new db_text_field("clientStatus"));

  }

  
  function has_attachment_permission() {
    // Placeholder for security check in shared/get_attchment.php
    return true;
  }
  
}



?>
