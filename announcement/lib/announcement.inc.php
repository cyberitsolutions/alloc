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

class announcement extends db_entity {
  var $data_table = "announcement";
  var $display_field_name = "heading";


  function announcement() {
    $this->db_entity();
    $this->key_field = new db_text_field("announcementID");
    $this->data_fields = array("heading"=>new db_text_field("heading")
                               , "body"=>new db_text_field("body")
                               , "personID"=>new db_text_field("personID")
                               , "displayFromDate"=>new db_text_field("displayFromDate")
                               , "displayToDate"=>new db_text_field("displayToDate")
      );
  }

  function has_announcements() {
    $db = new db_alloc;
    $today = date("Y-m-d");
    $query = sprintf("select * from announcement where displayFromDate <= '%s' and displayToDate >= '%s'", $today, $today);
    $db->query($query);
    if ($db->next_record()) {
      return true;
    }
    return false;
  }

}



?>
