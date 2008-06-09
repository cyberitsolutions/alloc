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

include(ALLOC_MOD_DIR."time/lib/timeUnit.inc.php");
include(ALLOC_MOD_DIR."time/lib/timeSheet.inc.php");
include(ALLOC_MOD_DIR."time/lib/timeSheetItem.inc.php");
include(ALLOC_MOD_DIR."time/lib/pendingApprovalTimeSheetListHomeItem.inc.php");

class time_module extends module
{
  var $db_entities = array("timeSheet", "timeSheetItem","timeUnit");

  function register_home_items() {
    global $current_user;

    if (isset($current_user) && $current_user->is_employee()) {
      include(ALLOC_MOD_DIR."time/lib/timeSheetListHomeItem.inc.php");
      register_home_item(new timeSheetListHomeItem);
      if (has_pending_timesheet()) {
        register_home_item(new pendingApprovalTimeSheetListHomeItem);
      }

      include(ALLOC_MOD_DIR."time/lib/timeSheetStatusHomeItem.inc.php");
      register_home_item(new timeSheetStatusHomeItem);


      $c = new config;
      $timeSheetAdminEmailPersonID = $c->get_config_item("timeSheetAdminEmail");
      
      if ($timeSheetAdminEmailPersonID == $current_user->get_id() && has_pending_admin_timesheet()) {
        register_home_item(new pendingAdminApprovalTimeSheetListHomeItem);
      }
    }
  }

}






?>
