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

class announcement_module extends module {
  var $db_entities = array("announcement");

  function register_toolbar_items() {
    // if (have_entity_perm("announcement", PERM_READ_WRITE)) {
    // register_toolbar_item("announcementList", "Announcements");
    // }
  }

  // announcements are registered in the projects init so that they come
  // before the massive list of projects....
  // function register_home_items() {
  // 
  // include(ALLOC_MOD_DIR."/announcement/lib/announcements_home_item.inc.php");
  // register_home_item(new announcements_home_item());
  // }
}

include(ALLOC_MOD_DIR."/announcement/lib/announcement.inc.php");



?>
