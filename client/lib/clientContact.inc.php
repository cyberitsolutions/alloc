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

class clientContact extends db_entity {
  var $data_table = "clientContact";
  var $display_field_name = "clientContactName";

  function clientContact() {
    $this->db_entity();
    $this->key_field = new db_text_field("clientContactID");
    $this->data_fields = array("clientID"=>new db_text_field("clientID"),
                               "clientContactName"=>new db_text_field("clientContactName"),
                               "clientContactStreetAddress"=>new db_text_field("clientContactStreetAddress"),
                               "clientContactSuburb"=>new db_text_field("clientContactSuburb"),
                               "clientContactState"=>new db_text_field("clientContactState"),
                               "clientContactPostcode"=>new db_text_field("clientContactPostcode"),
                               "clientContactPhone"=>new db_text_field("clientContactPhone"),
                               "clientContactMobile"=>new db_text_field("clientContactMobile"), "clientContactFax"=>new db_text_field("clientContactFax"), "clientContactEmail"=>new db_text_field("clientContactEmail"), "clientContactOther"=>new db_text_field("clientContactOther"));
  }
}



?>
