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

require_once("../alloc.php");

if (!$current_user->is_employee()) {
  die("You do not have permission to access time sheets");
}

$timeSheetID = $_POST["timeSheetID"];

if (($_POST["timeSheetItem_save"] || $_POST["timeSheetItem_edit"] || $_POST["timeSheetItem_delete"]) && $timeSheetID) {

  $timeSheet = new timeSheet;
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->load_pay_info();
  list($amount_used,$amount_allocated) = $timeSheet->get_amount_allocated();

  if ($timeSheet->get_value("status") == "edit") {
    $timeSheetItem = new timeSheetItem;
    $timeSheetItem->read_globals();
    $timeSheetItem->read_globals("timeSheetItem_");

    if ($_POST["timeSheetItem_save"]) {

      if ($_POST["timeSheetItem_taskID"]) {
        $selectedTask = new task();
        $selectedTask->set_id($_POST["timeSheetItem_taskID"]);
        $selectedTask->select();
        $taskName = $selectedTask->get_task_name();

        if (!$selectedTask->get_value("dateActualStart")) {
          $selectedTask->set_value("dateActualStart", $timeSheetItem->get_value("dateTimeSheetItem"));
        }
      }

      $timeSheetItem->set_value("description", $taskName);
      $_POST["timeSheetItem_commentPrivate"] and $timeSheetItem->set_value("commentPrivate", 1);
      $timeSheetItem->set_value("comment",rtrim($timeSheetItem->get_value("comment")));

      $amount_of_item = $timeSheetItem->calculate_item_charge($timeSheet->pay_info["customerBilledDollars"]);
      if ($amount_allocated && ($amount_of_item + $amount_used) > $amount_allocated) {
        $TPL["message"][] = "Adding this Time Sheet Item would exceed the amount allocated on the Pre-paid invoice.<br>Time Sheet Item not saved.";
      } else {
        $rtn = $timeSheetItem->save();
        $timeSheet->update_invoiceItem();
        $rtn or $TPL["message_good"][] = "Time Sheet Item saved.";
      }

      $rtn and $TPL["message"][] = $rtn;
      alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID);

    } else if ($_POST["timeSheetItem_edit"]) {
      alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID."&timeSheetItem_edit=true&timeSheetItemID=".$timeSheetItem->get_id());

    } else if ($_POST["timeSheetItem_delete"]) {
      $timeSheetItem->select();
      $timeSheetItem->delete();
      $timeSheet->update_invoiceItem();
      $TPL["message_good"][] = "Time Sheet Item deleted.";
      alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID);
    }
  }
}




?>
