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

class announcement extends db_entity {
  public $data_table = "announcement";
  public $display_field_name = "heading";
  public $key_field = "announcementID";
  public $data_fields = array("heading"
                             ,"body"
                             ,"personID"
                             ,"displayFromDate"
                             ,"displayToDate"
                             );

  function has_announcements() {
    $db = new db_alloc();
    $today = date("Y-m-d");
    $query = prepare("select * from announcement where displayFromDate <= '%s' and displayToDate >= '%s'", $today, $today);
    $db->query($query);
    if ($db->next_record()) {
      return true;
    }
    return false;
  }

}



?>
