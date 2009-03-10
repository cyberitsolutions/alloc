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

require_once(dirname(__FILE__)."/timeUnit.inc.php");
require_once(dirname(__FILE__)."/timeSheet.inc.php");
require_once(dirname(__FILE__)."/timeSheetItem.inc.php");
require_once(dirname(__FILE__)."/pendingApprovalTimeSheetListHomeItem.inc.php");
require_once(dirname(__FILE__)."/timeSheetListHomeItem.inc.php");
require_once(dirname(__FILE__)."/timeSheetStatusHomeItem.inc.php");

class time_module extends module
{
  var $db_entities = array("timeSheet", "timeSheetItem","timeUnit");

  function register_home_items() {
    global $current_user;

    if (isset($current_user) && $current_user->is_employee()) {
      register_home_item(new timeSheetListHomeItem);
      if (has_pending_timesheet()) {
        register_home_item(new pendingApprovalTimeSheetListHomeItem);
      }

      register_home_item(new timeSheetStatusHomeItem);

      $timeSheetAdminPersonIDs = config::get_config_item("defaultTimeSheetAdminList");
      
      if (in_array($current_user->get_id(), $timeSheetAdminPersonIDs) && has_pending_admin_timesheet()) {
        register_home_item(new pendingAdminApprovalTimeSheetListHomeItem);
      }
    }
  }

}






?>
