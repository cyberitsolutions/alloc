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

class announcements_home_item extends home_item {
  function __construct() {
    parent::__construct("announcements", "Announcements", "announcement", "announcementsH.tpl", "standard", 10);
  }

  function visible() {
    $announcement = new announcement();
    return $announcement->has_announcements();
  }

  function render() {
    return true;
  }

  function show_announcements($template_name) {
    $current_user = &singleton("current_user");
    global $TPL;

    $query = "SELECT *
                FROM announcement 
               WHERE displayFromDate <= CURDATE() AND displayToDate >= CURDATE()
            ORDER BY displayFromDate desc";
    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $announcement = new announcement();
      $announcement->read_db_record($db);
      $announcement->set_tpl_values();
      $person = $announcement->get_foreign_object("person");
      $TPL["personName"] = $person->get_name();
      include_template($this->get_template_dir().$template_name);
    }
  }
}



?>
