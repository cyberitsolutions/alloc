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

class timeSheetStatusHomeItem extends home_item {

  function timeSheetStatusHomeItem() {
    global $current_user, $TPL;
    home_item::home_item("time_status_list", "Time Sheet Statistics", "time", "timeSheetStatusHomeM.tpl", "narrow", 29);

    // Get averages for hours worked over the past fortnight and year
    $t = new timeSheetItem;
    list($hours_sum,$dollars_sum) = $t->get_averages(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y"))),$current_user->get_id());
    list($hours_avg,$dollars_avg) = $t->get_fortnightly_average($current_user->get_id());
    $TPL["hours_sum"] = sprintf("%d",$hours_sum[$current_user->get_id()]);
    $TPL["hours_avg"] = sprintf("%d",$hours_avg[$current_user->get_id()]);
    $TPL["dollars_sum"] = page::money_print($dollars_sum[$current_user->get_id()]);
    $TPL["dollars_avg"] = page::money(config::get_config_item("currency"),$dollars_avg[$current_user->get_id()],"%s%m %c");
  }

}

?>
