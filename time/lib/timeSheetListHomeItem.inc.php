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
    home_item::home_item("time_list", "Time Sheets", "time", "timeSheetHomeM.tpl", "narrow", 30);

    // Get averages for hours worked over the past fortnight and year
    $t = new timeSheetItem;
    list($hours_sum,$dollars_sum) = $t->get_averages(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y"))),$current_user->get_id());
    list($hours_avg,$dollars_avg) = $t->get_fortnightly_average($current_user->get_id());
    $TPL["hours_sum"] = sprintf("%d",$hours_sum[$current_user->get_id()]);
    $TPL["hours_avg"] = sprintf("%d",$hours_avg[$current_user->get_id()]);
    $TPL["dollars_sum"] = sprintf("%d",$dollars_sum[$current_user->get_id()]);
    $TPL["dollars_avg"] = sprintf("%d",$dollars_avg[$current_user->get_id()]);
  }

  function time_sheet_items() {
    global $current_user, $TPL;
    $grand_total = 0;

    $query = sprintf("SELECT timeSheet.*
                        FROM timeSheet
                       WHERE timeSheet.personID=%d 
                         AND timeSheet.status != 'finished' 
                    ORDER BY timeSheet.status, timeSheet.dateFrom", $current_user->get_id());
    $db = new db_alloc;
    $db->query($query);
    $lines = array();
    while ($db->next_record()) {
      $timeSheet = new timeSheet;
      $timeSheet->read_db_record($db);
      $timeSheet->set_tpl_values();
      $timeSheet->load_pay_info();
      $line = array();

        $line["status"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\">".ucwords($timeSheet->get_value("status"))."</a>";

      $line["total_dollars"] = "\$" . sprintf("%d", $timeSheet->pay_info["total_dollars"]);
      $grand_total += $timeSheet->pay_info["total_dollars"];

      $project = $timeSheet->get_foreign_object("project");
      if ($project->get_value("projectShortName")) {
        $line["projectName"] = $project->get_value("projectShortName");
      } else {
        $line["projectName"] = $project->get_value("projectName");
      }
      $lines[] = $line;
    }
  $rtn["total"] = sprintf("%d", $grand_total);
  $rtn["lines"] = $lines;
  return $rtn;
  }

}

?>
