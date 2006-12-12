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

require_once("../alloc.php");

if (!$current_user->is_employee()) {
  die("You do not have permission to access time sheets");
}



  function get_timeSheetItem_info() {
    global $TPL, $timeSheet, $db, $tskDesc;
    global $timeSheetItem, $timeSheetID;

    $db_task = new db_alloc;
    $timeUnit = new timeUnit;


    $q = sprintf("SELECT * from timeSheetItem WHERE timeSheetID=%d ", $timeSheetID);
    $q.= sprintf("GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
    $db->query($q);

    $timeSheetPrintUnit = config::get_config_item("timeSheetPrintUnit");
    $project = $timeSheet->get_foreign_object("project");
    $customerBilledDollars = $project->get_value("customerBilledDollars");

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->read_db_record($db);
      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));

      if ($timeSheetPrintUnit == "money") {
        if ($customerBilledDollars > 0) {
          $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration") * $customerBilledDollars);
        } else {
          $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration") * $timeSheetItem->get_value('rate'));
        }
      } else if ($timeSheetPrintUnit == "quantity") {
        $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration"));
      }


      $rows[$taskID]["unit"] += $num;
      $info["total"] += $num;


      unset($str);
      $d = stripslashes($timeSheetItem->get_value('description'));
      $d && !$rows[$taskID]["desc"] and $str[] = "<b>".$d."</b>";

      $c = nl2br($timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c and $str[] = $c;

      $rows[$taskID]["desc"].= implode("<br/>",$str);
    }

    // If we are in dollar mode, then prefix the total with a dollar sign
    $timeSheetPrintUnit == "money" and $info["total"] = sprintf("$%0.2f",$info["total"]);
    $rows or $rows = array();
    $info or $info = array();

    return array($rows,$info);


  }




// ============ END FUNCTIONS 

global $timeSheet, $timeSheetItem, $timeSheetItemID, $db, $current_user, $TPL;

$timeSheetID = $_POST["timeSheetID"] or $timeSheetID = $_GET["timeSheetID"];


$db = new db_alloc;


if ($timeSheetID) {
  $timeSheet = new timeSheet;
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->set_tpl_values();


$person = $timeSheet->get_foreign_object("person");
$TPL["timeSheet_personName"] = $person->get_username(1);
$timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");
$TPL["timeSheet_status_label"] = ucwords($TPL["timeSheet_status"]);



// display the project name.
$project = new project;
$project->set_id($timeSheet->get_value("projectID"));
$project->select();
$TPL["timeSheet_projectName"] = $project->get_value("projectName");

// Get client name
$client = $project->get_foreign_object("client");
$TPL["clientName"] = $client->get_value("clientName");

// These variables populate the time sheet printer friendly version
$TPL["companyName"] = config::get_config_item("companyName");

$acn = config::get_config_item("companyACN");
$abn = config::get_config_item("companyABN");

$acn && $abn and $br = "<br/>";
$acn and $TPL["companyACN"] = "ACN ".$acn;
$abn and $TPL["companyABN"] = $br."ABN ".$abn;

unset($br);
$phone = config::get_config_item("companyContactPhone");
$fax = config::get_config_item("companyContactFax");
$phone and $TPL["phone"] = "Ph: ".$phone;
$fax and $TPL["fax"] = "Fax: ".$fax;



$TPL["companyInfoLine2"] = "Phone: ".config::get_config_item("companyContactPhone")." Fax: ".config::get_config_item("companyContactFax")." Email: ".config::get_config_item("companyContactEmail");
$TPL["companyInfoLine2"].= " Web: ".config::get_config_item("companyContactHomePage");



$timeSheet->load_pay_info();

$timeSheetPrintUnit = config::get_config_item("timeSheetPrintUnit");
$tspu_arr = config::get_array_timeSheetPrintUnit();
$TPL["timeSheetPrintUnitLabel"] = $tspu_arr[$timeSheetPrintUnit];


if ($timeSheetPrintUnit == "money") {
  $TPL["total"] = "$".sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars"]);
  $TPL["total"].= " ($".sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars_minus_gst"])." excl GST )";

} else if ($timeSheetPrintUnit == "quantity") {
  $TPL["total"] = $timeSheet->pay_info["summary_unit_totals"];
}






$db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) as count
      FROM timeSheetItem WHERE timeSheetID=%d ", $timeSheetID));

$db->next_record();
$timeSheet->set_id($timeSheetID);
$timeSheet->select() || die("unable to determine timeSheetID for purposes of latest date.");
$TPL["period"] = "Billing period ".$db->f("minDate")." to ".$db->f("maxDate");


include_template("templates/timeSheetPrintM.tpl");


}


page_close();
?>
