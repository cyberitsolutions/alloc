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

require_once("../alloc.php");

function show_announcements($template_name) {
  global $TPL;

  $people =& get_cached_table("person");
  $query = "SELECT announcement.* 
              FROM announcement 
              ORDER BY displayFromDate DESC";
  $db = new db_alloc();
  $db->query($query);
  while ($db->next_record()) {
    $announcement = new announcement();
    $announcement->read_db_record($db);
    $announcement->set_values();
    $TPL["personName"] = $people[$announcement->get_value("personID")]["name"];
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    include_template($template_name);
  }
}

$TPL["main_alloc_title"] = "Announcement List - ".APPLICATION_NAME;

include_template("templates/announcementListM.tpl");




?>
