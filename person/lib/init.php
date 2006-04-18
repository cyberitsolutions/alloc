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

class person_module extends module {
  var $db_entities = array("person", "absence", "skillList", "skillProficiencys");

  function register_toolbar_items() {
    global $current_user;

    // Note: $current_user will not be set if we are sending email
    if (have_entity_perm("person", PERM_READ_WRITE)) {
      register_toolbar_item("personList", "Personnel");
    } else {
      register_toolbar_item("person", "Personal", "personID=".$current_user->get_id());
    }

  }
}

include(ALLOC_MOD_DIR."/person/lib/person.inc.php");
include(ALLOC_MOD_DIR."/person/lib/absence.inc.php");
include(ALLOC_MOD_DIR."/person/lib/skillList.inc.php");
include(ALLOC_MOD_DIR."/person/lib/skillProficiencys.inc.php");




?>
