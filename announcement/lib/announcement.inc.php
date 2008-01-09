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

class announcement extends db_entity {
  var $data_table = "announcement";
  var $display_field_name = "heading";


  function announcement() {
    $this->db_entity();
    $this->key_field = new db_field("announcementID");
    $this->data_fields = array("heading"=>new db_field("heading")
                               , "body"=>new db_field("body")
                               , "personID"=>new db_field("personID")
                               , "displayFromDate"=>new db_field("displayFromDate")
                               , "displayToDate"=>new db_field("displayToDate")
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
