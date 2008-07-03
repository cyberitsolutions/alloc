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


include(ALLOC_MOD_DIR."project/lib/project.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectPerson.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectModificationNote.inc.php");
include(ALLOC_MOD_DIR."project/lib/projectCommissionPerson.inc.php");
include(ALLOC_MOD_DIR."shared/lib/import_export.inc.php");


class project_module extends module
{
  var $db_entities = array("project"
                         , "projectPerson"
                         , "projectModificationNote"
                         , "projectCommissionPerson"
                         );

  function register_home_items() {
    global $current_user;
    if (sprintf("%d",$current_user->prefs["projectListNum"]) > 0 || $current_user->prefs["projectListNum"] == "all") {
      include(ALLOC_MOD_DIR."project/lib/project_list_home_item.inc.php");
      register_home_item(new project_list_home_item());
    }
  }

}




?>
