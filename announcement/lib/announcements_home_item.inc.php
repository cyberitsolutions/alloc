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

class announcements_home_item extends home_item {
  function announcements_home_item() {
    home_item::home_item("announcements", "Announcements", "announcement", "announcementsH.tpl", "standard", 10);
  }

  function show_announcements($template_name) {
    global $current_user, $TPL;

    $query = "SELECT announcement.*, person.username
             FROM announcement LEFT JOIN person ON announcement.personID = person.personID
             WHERE displayFromDate <= CURDATE() AND displayToDate >= CURDATE()
             ORDER BY displayFromDate desc";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $announcement = new announcement;
      $announcement->read_db_record($db);
      $announcement->set_tpl_values();
      $TPL["personName"] = $db->f("username");
      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
