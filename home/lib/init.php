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

class home_module extends module {
  var $db_entities = array("history");
  function register_toolbar_items() {
    register_toolbar_item("home", "Home");
  }

  function register_home_items() {

    $modules = get_alloc_modules();

    if (isset($modules["finance"]) && $modules["finance"]) {
      include(ALLOC_MOD_DIR."/home/lib/tfList_home_item.inc.php");
      register_home_item(new tfList_home_item);
    } else {
      include(ALLOC_MOD_DIR."/home/lib/date_home_item.inc.php");
      register_home_item(new date_home_item);
    }

    include(ALLOC_MOD_DIR."/home/lib/customize_alloc_home_item.inc.php");
    register_home_item(new customize_alloc_home_item);


    // include(ALLOC_MOD_DIR."/home/lib/quick_links_home_item.inc.php");
    // register_home_item(new quick_links_home_item);
    // include(ALLOC_MOD_DIR."/home/lib/history_home_item.inc.php");
    // register_home_item(new history_home_item);
  }
}




?>
