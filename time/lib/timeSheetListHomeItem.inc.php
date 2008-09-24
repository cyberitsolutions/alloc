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

class timeSheetListHomeItem extends home_item {

  function timeSheetListHomeItem() {
    global $current_user, $TPL;
    home_item::home_item("time_list", "Current Time Sheets", "time", "timeSheetHomeM.tpl", "narrow", 30);

    $ops["showHeader"] = "true";
    $ops["showShortProjectLink"] = "true";
    $ops["showAmount"] = "true";
    $ops["showAmountTotal"] = "true";
    $ops["showStatus"] = "true";
    $ops["personID"] = $current_user->get_id();
    $ops["status"] = array('edit','manager','admin','invoiced');

    $TPL["time_sheet_list"] = timeSheet::get_list($ops);
  }
}

?>
