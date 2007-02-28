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


include(ALLOC_MOD_DIR."project/lib/project.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectPerson.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectPersonRole.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectModificationNote.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectCommissionPerson.inc.php");


class project_module extends module
{
  var $db_entities = array("project"
                         , "projectPerson"
                         , "projectModificationNote"
                         , "projectCommissionPerson"
                         );

  function register_home_items() {
    global $current_user;
    if (sprintf("%d",$current_user->prefs["projectListNum"]) > 0) {
      include(ALLOC_MOD_DIR."project/lib/project_list_home_item.inc.php");
      register_home_item(new project_list_home_item());
    }
  }

}




?>
