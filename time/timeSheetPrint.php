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

define("DEFAULT_SEP","\n");


  function get_timeSheetItem_vars($timeSheetID) {
 
    $timeSheet = new timeSheet();
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();

    $timeUnit = new timeUnit;
    $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");

    $q = sprintf("SELECT * from timeSheetItem WHERE timeSheetID=%d ", $timeSheetID);
    $q.= sprintf("GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
    $db = new db_alloc;
    $db->query($q);

    $project = $timeSheet->get_foreign_object("project");
    $customerBilledDollars = $project->get_value("customerBilledDollars");

    return array($db, $customerBilledDollars,$timeSheet,$unit_array);
  }

  function get_timeSheetItem_list_money($timeSheetID) {
    list($db,$customerBilledDollars,$timeSheet,$unit_array) = get_timeSheetItem_vars($timeSheetID);

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->read_db_record($db);

      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));

      if ($customerBilledDollars > 0) {
        $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration") * $customerBilledDollars);
      } else {
        $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration") * $timeSheetItem->get_value('rate'));
      }

      $info["total"] += $num;
      $rows[$taskID]["tally"] += $num; //fix so that can have values like 2 weeks, 3 hours

      unset($str);
      $d = $timeSheetItem->get_value('description');
      $d && !$rows[$taskID]["desc"] and $str[] = stripslashes($d);

      // Get task description
      if ($taskID && $_GET["printDesc"]) {
        $t = new task;
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription"));

        $d2 && !$d2s[$taskID] and $str[] = stripslashes($d2);
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c and $str[] = stripslashes($c);

      is_array($str) and $rows[$taskID]["desc"].= trim(implode(DEFAULT_SEP,$str));
    }

    // If we are in dollar mode, then prefix the total with a dollar sign
    $info["total"] = sprintf("$%0.2f",$info["total"]);
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }

  function get_timeSheetItem_list_units($timeSheetID) {
    list($db,$customerBilledDollars,$timeSheet,$unit_array) = get_timeSheetItem_vars($timeSheetID);

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->read_db_record($db);

      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));
      $taskID or $taskID = "hey"; // Catch entries without task selected. ie timesheetitem.comment entries.

      $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration"));
      $info["total"] += $num;

      $unit = $unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];
      $units[$taskID][$unit] += $num;

      unset($str);
      $d = $timeSheetItem->get_value('description');
      $d && !$rows[$taskID]["desc"] and $str[] = stripslashes($d);


      // Get task description
      if ($taskID && $_GET["printDesc"]) {
        $t = new task;
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription"));

        $d2 && !$d2s[$taskID] and $str[] = stripslashes($d2);
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c  && !$cs[$c] and $str[] = stripslashes($c);
      $cs[$c] = true;

      is_array($str) and $rows[$taskID]["desc"].= trim(implode(DEFAULT_SEP,$str));

    }

    // Group by units ie, a particular row/task might have  3 Weeks, 2 Hours of work done.
    foreach ($units as $tid => $u) {
      unset($commar);
      foreach ($u as $unit => $amount) {
        $rows[$tid]["tally"] .= $commar.$amount." ".$unit;
        $commar = ", ";
      }
    }

    $timeSheet->load_pay_info();
    $info["total"] = $timeSheet->pay_info["summary_unit_totals"];
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }

  function get_timeSheetItem_list_items($timeSheetID) {
    list($db,$customerBilledDollars,$timeSheet,$unit_array) = get_timeSheetItem_vars($timeSheetID);

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->read_db_record($db);

      $row_num++;
      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));
      $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration"));

      $info["total"] += $num;
      $rows[$row_num]["date"] = $timeSheetItem->get_value("dateTimeSheetItem");
      $rows[$row_num]["tally"] = $num." ".$unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];

      unset($str);
      $d = $timeSheetItem->get_value('description');
      $d && !$rows[$row_num]["desc"] and $str[] = stripslashes($d);

      // Get task description
      if ($taskID && $_GET["printDesc"]) {
        $t = new task;
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription"));

        $d2 && !$d2s[$taskID] and $str[] = stripslashes($d2);
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c and $str[] = stripslashes($c);

      is_array($str) and $rows[$row_num]["desc"].= trim(implode(DEFAULT_SEP,$str));
    }

    $timeSheet->load_pay_info();
    $info["total"] = $timeSheet->pay_info["summary_unit_totals"];
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }



// ============ END FUNCTIONS 

$timeSheetID = $_POST["timeSheetID"] or $timeSheetID = $_GET["timeSheetID"];
$TPL["timeSheetID"] = $timeSheetID;


$db = new db_alloc;


if ($timeSheetID) {

  $timeSheet = new timeSheet;
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->set_tpl_values();


  $person = $timeSheet->get_foreign_object("person");
  $TPL["timeSheet_personName"] = $person->get_username(1);
  $timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");


  // Display the project name.
  $project = new project;
  $project->set_id($timeSheet->get_value("projectID"));
  $project->select();
  $TPL["timeSheet_projectName"] = $project->get_value("projectName");

  // Get client name
  $client = $project->get_foreign_object("client");
  $TPL["clientName"] = $client->get_value("clientName");
  $TPL["companyName"] = config::get_config_item("companyName");

  $TPL["companyNos1"] = config::get_config_item("companyACN");
  $TPL["companyNos2"] = config::get_config_item("companyABN");

  unset($br);
  $phone = config::get_config_item("companyContactPhone");
  $fax = config::get_config_item("companyContactFax");
  $phone and $TPL["phone"] = "Ph: ".$phone;
  $fax and $TPL["fax"] = "Fax: ".$fax;


  $timeSheet->load_pay_info();

  $db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) as count
        FROM timeSheetItem WHERE timeSheetID=%d ", $timeSheetID));

  $db->next_record();
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select() || die("unable to determine timeSheetID for purposes of latest date.");
  $TPL["period"] = "Billing Period: ".format_date(DATE_FORMAT, $db->f("minDate"))." to ".format_date(DATE_FORMAT, $db->f("maxDate"));

  $TPL["img"] = config::get_config_item("companyImage");
  $TPL["companyContactAddress"] = config::get_config_item("companyContactAddress");
  $TPL["companyContactAddress2"] = config::get_config_item("companyContactAddress2");
  $TPL["companyContactAddress3"] = config::get_config_item("companyContactAddress3");
  $TPL["companyContactEmail"] = config::get_config_item("companyContactEmail");
  $TPL["companyContactHomePage"] = config::get_config_item("companyContactHomePage");
  $TPL["footer"] = stripslashes(config::get_config_item("timeSheetPrintFooter"));


  // Build PDF document
  require_once("../pdf/class.ezpdf.php");

  $font1 = ALLOC_MOD_DIR."/util/fonts/Helvetica.afm";
  $font2 = ALLOC_MOD_DIR."/util/fonts/Helvetica-Oblique.afm";

  $pdf_table_options = array("showLines"=>0,"shaded"=>0,"showHeadings"=>0,"width"=>400,"xPos"=>"center","fontSize"=>11);
  $pdf_table_options2 = array("showLines"=>0,"shaded"=>0,"showHeadings"=>0,"width"=>400, "xPos"=>"center","fontSize"=>11);
  $pdf_table_options3 = array("showLines"=>2,"shaded"=>0,"width"=>400, "xPos"=>"center","fontSize"=>10);

  $pdf =& new Cezpdf();
  $pdf->selectFont($font1);
  $pdf->ezText("ID: <b>".$TPL["timeSheetID"]."</b>",12, array("justification"=>"right"));
  $pdf->ezText("<b>".$TPL["companyName"]."</b>",17, array("justification"=>"center"));
  $y = $pdf->ezText($TPL["companyNos1"],12, array("justification"=>"center"));
  $y = $pdf->ezText($TPL["companyNos2"],12, array("justification"=>"center"));
  $pdf->ezSetY($y -20);

  $contact_info[] = array($TPL["companyName"],"Email: ".$TPL["companyContactEmail"]);
  $contact_info[] = array($TPL["companyContactAddress"],"Web: ".$TPL["companyContactHomePage"]);
  $contact_info[] = array($TPL["companyContactAddress2"],$TPL["phone"]);
  $contact_info[] = array($TPL["companyContactAddress3"],$TPL["fax"]);

  $pdf->selectFont($font2);
  $y = $pdf->ezTable($contact_info,false,"",$pdf_table_options);
  $pdf->ezSetY($y -20);
  $pdf->selectFont($font1);

  $ts_info[] = array("Client: ".$TPL["clientName"]);
  $ts_info[] = array("Project: ".$TPL["timeSheet_projectName"]);
  $ts_info[] = array("Contractor: ".$TPL["timeSheet_personName"]);
  $ts_info[] = array($TPL["period"]);
  $y = $pdf->ezTable($ts_info,false,"",$pdf_table_options2);
  $pdf->ezSetY($y -20);


  if ($_GET["timeSheetPrintMode"] == "money") {
    list($rows,$info) = get_timeSheetItem_list_money($TPL["timeSheetID"]);
    $cols = array("desc"=>"Description","tally"=>"Charges");
    $rows[] = array("desc"=>"<b>TOTAL</b>","tally"=>"<b>".$info["total"]."</b>");
    $y = $pdf->ezTable($rows,$cols,"",$pdf_table_options3);

  } else if ($_GET["timeSheetPrintMode"] == "units") {
    list($rows,$info) = get_timeSheetItem_list_units($TPL["timeSheetID"]);
    $cols = array("desc"=>"Description","tally"=>"Units");
    $rows[] = array("desc"=>"<b>TOTAL</b>","tally"=>"<b>".$info["total"]."</b>");
    $y = $pdf->ezTable($rows,$cols,"",$pdf_table_options3);

  } else if ($_GET["timeSheetPrintMode"] == "items") {
    list($rows,$info) = get_timeSheetItem_list_items($TPL["timeSheetID"]);
    $cols = array("date"=>"Date","tally"=>"Units","desc"=>"Description");
    $rows[] = array("date"=>"<b>TOTAL</b>","tally"=>"<b>".$info["total"]."</b>");
    $y = $pdf->ezTable($rows,$cols,"",$pdf_table_options3);
  }

  $pdf->ezSetY($y -20);
  $footer = array(array(str_replace(array("<br/>","<br>"),"\n",$TPL["footer"])));
  $pdf->ezTable($footer,false,"",$pdf_table_options2);
  $pdf->ezText("ID: <b>".$TPL["timeSheetID"]."</b>",12, array("justification"=>"right"));

  $pdf->ezStream();

}


?>
