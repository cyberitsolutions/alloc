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

  $timeSheetItem = new timeSheetItem;
  if ($timeSheetItem->get_id()) {
    $timeSheetItem->select();
  }
  $timeSheetItem->read_globals();
  $timeSheetItem->read_globals("timeSheetItem_");

  if ($_POST["timeSheetItem_save"]) {
    $timeSheetItem->read_globals();
    $timeSheetItem->read_globals("timeSheetItem_");
    $rtn = $timeSheetItem->save();
    $rtn and $TPL["message_good"][] = "Time Sheet Item saved.";
    alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID);

  } else if ($_POST["timeSheetItem_edit"]) {
    alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID."&timeSheetItem_edit=true&timeSheetItemID=".$timeSheetItem->get_id());

  } else if ($_POST["timeSheetItem_delete"]) {
    $timeSheetItem->select();
    $timeSheetItem->delete();
    $TPL["message_good"][] = "Time Sheet Item deleted.";
    alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheetID);
  }
}




?>
