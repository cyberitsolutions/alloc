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
