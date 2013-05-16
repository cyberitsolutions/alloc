<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

define("DEFAULT_SEP","\n");

class timeSheetPrint {

  function get_timeSheetItem_vars($timeSheetID) {
 
    $timeSheet = new timeSheet();
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();

    $timeUnit = new timeUnit();
    $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");

    $q = prepare("SELECT * from timeSheetItem WHERE timeSheetID=%d ", $timeSheetID);
    $q.= prepare("GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
    $db = new db_alloc();
    $db->query($q);

    $customerBilledDollars = $timeSheet->get_value("customerBilledDollars");
    $currency = page::money($timeSheet->get_value("currencyTypeID"),'',"%S");

    return array($db, $customerBilledDollars,$timeSheet,$unit_array,$currency);
  }

  function get_timeSheetItem_list_money($timeSheetID) {
    global $TPL;
    list($db,$customerBilledDollars,$timeSheet,$unit_array,$currency) = $this->get_timeSheetItem_vars($timeSheetID);

    $taxPercent = config::get_config_item("taxPercent");
    $taxPercentDivisor = ($taxPercent/100) + 1;

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem();
      $timeSheetItem->read_db_record($db);

      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));

      $num = $timeSheetItem->calculate_item_charge($currency, $customerBilledDollars ? $customerBilledDollars : $timeSheetItem->get_value("rate"));

      if ($taxPercent !== '') {
        $num_minus_gst = $num / $taxPercentDivisor;
        $gst =           $num - $num_minus_gst; 

        if (($num_minus_gst + $gst) != $num) {
          $num_minus_gst += $num - ($num_minus_gst + $gst); // round it up.
        }

        $rows[$taskID]["money"]+= page::money($timeSheet->get_value("currencyTypeID"),$num_minus_gst,"%mo");
        $rows[$taskID]["gst"] += page::money($timeSheet->get_value("currencyTypeID"),$gst,"%mo");
        $info["total_gst"] += $gst;
        $info["total"] += $num_minus_gst;
      } else {
        $rows[$taskID]["money"] += page::money($timeSheet->get_value("currencyTypeID"),$num,"%mo");
        $info["total"] += $num;
      }


      $unit = $unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];
      $units[$taskID][$unit] += sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration") * $timeSheetItem->get_value("multiplier"));

      unset($str);
      $d = $timeSheetItem->get_value('taskID', DST_HTML_DISPLAY) . ": " . $timeSheetItem->get_value('description',DST_HTML_DISPLAY);
      $d && !$rows[$taskID]["desc"] and $str[] = "<b>".$d."</b>"; //inline because the PDF needs it that way

      // Get task description
      if ($taskID && $TPL["printDesc"]) {
        $t = new task();
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription",DST_HTML_DISPLAY));
	$d2 .= "\n";

        $d2 && !$d2s[$taskID] and $str[] = $d2;
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c and $str[] = page::htmlentities($c);

      is_array($str) and $rows[$taskID]["desc"].= trim(implode(DEFAULT_SEP,$str));
    }

    // Group by units ie, a particular row/task might have  3 Weeks, 2 Hours of work done.
    $units or $units = array();
    foreach ($units as $tid => $u) {
      unset($commar);
      foreach ($u as $unit => $amount) {
        $rows[$tid]["units"] .= $commar.$amount." ".$unit;
        $commar = ", ";
        $i[$unit] += $amount;
      }
    }
    unset($commar);
    $i or $i = array();
    foreach ($i as $unit => $amount) {
      $info["total_units"].= $commar.$amount." ".$unit;
      $commar = ", ";
    }

    $info["total_inc_gst"] = page::money($timeSheet->get_value("currencyTypeID"),$info["total"]+$info["total_gst"],"%s%mo");

    // If we are in dollar mode, then prefix the total with a dollar sign
    $info["total"] = page::money($timeSheet->get_value("currencyTypeID"),$info["total"],"%s%mo");
    $info["total_gst"] = page::money($timeSheet->get_value("currencyTypeID"),$info["total_gst"],"%s%mo");
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }

  function get_timeSheetItem_list_units($timeSheetID) {
    global $TPL;
    list($db,$customerBilledDollars,$timeSheet,$unit_array,$currency) = $this->get_timeSheetItem_vars($timeSheetID);

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem();
      $timeSheetItem->read_db_record($db);

      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));
      $taskID or $taskID = "hey"; // Catch entries without task selected. ie timesheetitem.comment entries.

      $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration"));
      #$info["total"] += $num;

      $unit = $unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];
      $units[$taskID][$unit] += $num;

      unset($str);
      $d = $timeSheetItem->get_value('taskID', DST_HTML_DISPLAY) . ": " . $timeSheetItem->get_value('description',DST_HTML_DISPLAY);
      $d && !$rows[$taskID]["desc"] and $str[] = "<b>".$d."</b>";


      // Get task description
      if ($taskID && $TPL["printDesc"]) {
        $t = new task();
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription",DST_HTML_DISPLAY));
	$d2 .= "\n";

        $d2 && !$d2s[$taskID] and $str[] = $d2;
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c  && !$cs[$c] and $str[] = page::htmlentities($c);
      $cs[$c] = true;

      is_array($str) and $rows[$taskID]["desc"].= trim(implode(DEFAULT_SEP,$str));

    }

    // Group by units ie, a particular row/task might have  3 Weeks, 2 Hours of work done.
    $units or $units = array();
    foreach ($units as $tid => $u) {
      unset($commar);
      foreach ($u as $unit => $amount) {
        $rows[$tid]["units"] .= $commar.$amount." ".$unit;
        $commar = ", ";
        $i[$unit] += $amount;
      }
    }

    unset($commar);
    $i or $i = array();
    foreach ($i as $unit => $amount) {
      $info["total"].= $commar.$amount." ".$unit;
      $commar = ", ";
    }

    $timeSheet->load_pay_info();
    $info["total"] = $timeSheet->pay_info["summary_unit_totals"];
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }

  function get_timeSheetItem_list_items($timeSheetID) {
    global $TPL;
    list($db,$customerBilledDollars,$timeSheet,$unit_array,$currency) = $this->get_timeSheetItem_vars($timeSheetID);

    $m = new meta("timeSheetItemMultiplier");
    $multipliers = $m->get_list();

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem();
      $timeSheetItem->read_db_record($db);

      $row_num++;
      $taskID = sprintf("%d",$timeSheetItem->get_value("taskID"));
      $num = sprintf("%0.2f",$timeSheetItem->get_value("timeSheetItemDuration"));

      $info["total"] += $num;
      $rows[$row_num]["date"] = $timeSheetItem->get_value("dateTimeSheetItem");
      $rows[$row_num]["units"] = $num." ".$unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];
      $rows[$row_num]["multiplier_string"] = $multipliers[$timeSheetItem->get_value("multiplier")]["timeSheetItemMultiplierName"];

      unset($str);
      $d = $timeSheetItem->get_value('taskID', DST_HTML_DISPLAY) . ": " . $timeSheetItem->get_value('description',DST_HTML_DISPLAY);
      $d && !$rows[$row_num]["desc"] and $str[] = "<b>".$d."</b>";

      // Get task description
      if ($taskID && $TPL["printDesc"]) {
        $t = new task();
        $t->set_id($taskID);
        $t->select();
        $d2 = str_replace("\r\n","\n",$t->get_value("taskDescription",DST_HTML_DISPLAY));
	$d2 .= "\n";

        $d2 && !$d2s[$taskID] and $str[] = $d2;
        $d2 and $d2s[$taskID] = true;
      }

      $c = str_replace("\r\n","\n",$timeSheetItem->get_value("comment"));
      !$timeSheetItem->get_value("commentPrivate") && $c and $str[] = page::htmlentities($c);

      is_array($str) and $rows[$row_num]["desc"].= trim(implode(DEFAULT_SEP,$str));
    }

    $timeSheet->load_pay_info();
    $info["total"] = $timeSheet->pay_info["summary_unit_totals"];
    $rows or $rows = array();
    $info or $info = array();
    return array($rows,$info);
  }

  function get_printable_timeSheet_file($timeSheetID,$timeSheetPrintMode,$printDesc,$format) {
    global $TPL;

    $TPL["timeSheetID"] = $timeSheetID;
    $TPL["timeSheetPrintMode"] = $timeSheetPrintMode;
    $TPL["printDesc"] = $printDesc;
    $TPL["format"] = $format;

    $db = new db_alloc();

    if ($timeSheetID) {

      $timeSheet = new timeSheet();
      $timeSheet->set_id($timeSheetID);
      $timeSheet->select();
      $timeSheet->set_tpl_values();


      $person = $timeSheet->get_foreign_object("person");
      $TPL["timeSheet_personName"] = $person->get_name();
      $timeSheet->set_tpl_values("timeSheet_");


      // Display the project name.
      $project = new project();
      $project->set_id($timeSheet->get_value("projectID"));
      $project->select();
      $TPL["timeSheet_projectName"] = $project->get_value("projectName",DST_HTML_DISPLAY);

      // Get client name
      $client = $project->get_foreign_object("client");
      $client->set_tpl_values();
      $TPL["clientName"] = $client->get_value("clientName",DST_HTML_DISPLAY);
      $TPL["companyName"] = config::get_config_item("companyName");

      $TPL["companyNos1"] = config::get_config_item("companyACN");
      $TPL["companyNos2"] = config::get_config_item("companyABN");

      unset($br);
      $phone = config::get_config_item("companyContactPhone");
      $fax = config::get_config_item("companyContactFax");
      $phone and $TPL["phone"] = "Ph: ".$phone;
      $fax and $TPL["fax"] = "Fax: ".$fax;


      $timeSheet->load_pay_info();

      $db->query(prepare("SELECT max(dateTimeSheetItem) AS maxDate
                                ,min(dateTimeSheetItem) AS minDate
                                ,count(timeSheetItemID) as count
                            FROM timeSheetItem 
                           WHERE timeSheetID=%d ", $timeSheetID));

      $db->next_record();
      $timeSheet->set_id($timeSheetID);
      $timeSheet->select() || alloc_error("unable to determine timeSheetID for purposes of latest date.");
      $TPL["period"] = format_date(DATE_FORMAT, $db->f("minDate"))." to ".format_date(DATE_FORMAT, $db->f("maxDate"));

      $TPL["img"] = config::get_config_item("companyImage");
      $TPL["companyContactAddress"] = config::get_config_item("companyContactAddress");
      $TPL["companyContactAddress2"] = config::get_config_item("companyContactAddress2");
      $TPL["companyContactAddress3"] = config::get_config_item("companyContactAddress3");
      $email = config::get_config_item("companyContactEmail");
      $email and $TPL["companyContactEmail"] = "Email: ".$email;
      $web = config::get_config_item("companyContactHomePage");
      $web and $TPL["companyContactHomePage"] = "Web: ".$web;

      $TPL["footer"] = config::get_config_item("timeSheetPrintFooter");
      $TPL["taxName"] = config::get_config_item("taxName");



      $default_header = "Time Sheet";
      $default_id_label = "Time Sheet ID";
      $default_contractor_label = "Contractor";
      $default_total_label = "TOTAL AMOUNT PAYABLE";

      if ($timeSheetPrintMode == "money") {
        $default_header = "Tax Invoice";
        $default_id_label = "Invoice Number";
      }
      if ($timeSheetPrintMode == "estimate") {
        $default_header = "Estimate";
        $default_id_label = "Estimate Number";
        $default_contractor_label = "Issued By";
        $default_total_label = "TOTAL AMOUNT ESTIMATED";
      }


      if ($format != "html") {

        // Build PDF document
        $font1 = ALLOC_MOD_DIR."util/fonts/Helvetica.afm";
        $font2 = ALLOC_MOD_DIR."util/fonts/Helvetica-Oblique.afm";

        $pdf_table_options = array("showLines"=>0,"shaded"=>0,"showHeadings"=>0,"xPos"=>"left","xOrientation"=>"right","fontSize"=>10,"rowGap"=>0,"fontSize"=>10);


        $cols = array("one"=>"","two"=>"","three"=>"","four"=>"");
        $cols3 = array("one"=>"","two"=>"");
        $cols_settings["one"] = array("justification"=>"right");
        $cols_settings["three"] = array("justification"=>"right");
        $pdf_table_options2 = array("showLines"=>0,"shaded"=>0,"showHeadings"=>0, "width"=>400, "fontSize"=>10, "xPos"=>"center", "xOrientation"=>"center", "cols"=>$cols_settings);
        $cols_settings2["gst"] = array("justification"=>"right");
        $cols_settings2["money"] = array("justification"=>"right");
        $pdf_table_options3 = array("showLines"=>2,"shaded"=>0,"width"=>400, "xPos"=>"center","fontSize"=>10,"cols"=>$cols_settings2,"lineCol"=>array(0.8, 0.8, 0.8),"splitRows"=>1,"protectRows"=>0);
        $cols_settings["two"] = array("justification"=>"right","width"=>80);
        $pdf_table_options4 = array("showLines"=>2,"shaded"=>0,"width"=>400, "showHeadings"=>0, "fontSize"=>10, "xPos"=>"center", "cols"=>$cols_settings,"lineCol"=>array(0.8, 0.8, 0.8));

        $pdf = new Cezpdf();
        $pdf->ezSetMargins(90,90,90,90);

        $pdf->selectFont($font1);
        $pdf->ezStartPageNumbers(436,80,10,'right','Page {PAGENUM} of {TOTALPAGENUM}');
        $pdf->ezStartPageNumbers(200,80,10,'left','<b>'.$default_id_label.': </b>'.$TPL["timeSheetID"]);
        $pdf->ezSetY(775);

        $TPL["companyName"]            and $contact_info[] = array($TPL["companyName"]);
        $TPL["companyContactAddress"]  and $contact_info[] = array($TPL["companyContactAddress"]);
        $TPL["companyContactAddress2"] and $contact_info[] = array($TPL["companyContactAddress2"]);
        $TPL["companyContactAddress3"] and $contact_info[] = array($TPL["companyContactAddress3"]);
        $TPL["companyContactEmail"]    and $contact_info[] = array($TPL["companyContactEmail"]);
        $TPL["companyContactHomePage"] and $contact_info[] = array($TPL["companyContactHomePage"]);
        $TPL["phone"]                  and $contact_info[] = array($TPL["phone"]);
        $TPL["fax"]                    and $contact_info[] = array($TPL["fax"]);

        $pdf->selectFont($font2);

        $y = $pdf->ezTable($contact_info,false,"",$pdf_table_options);
        $pdf->selectFont($font1);

        $line_y = $y-10;
        $pdf->setLineStyle(1,"round");
        $pdf->line(90,$line_y,510,$line_y);


        $pdf->ezSetY(782);
        $image_jpg = ALLOC_LOGO;
        if (file_exists($image_jpg)) {
          $pdf->ezImage($image_jpg,0,0,'none');
          $y = 700;
        } else {
          $y = $pdf->ezText($TPL["companyName"],27, array("justification"=>"right"));
        }
        $nos_y = $line_y + 22;
        $TPL["companyNos2"] and $nos_y = $line_y + 34;
        $pdf->ezSetY($nos_y);
        $TPL["companyNos1"] and $y = $pdf->ezText($TPL["companyNos1"],10, array("justification"=>"right"));
        $TPL["companyNos2"] and $y = $pdf->ezText($TPL["companyNos2"],10, array("justification"=>"right"));



        $pdf->ezSetY($line_y -20);
        $y = $pdf->ezText($default_header,20, array("justification"=>"center"));
        $pdf->ezSetY($y -20);

        $ts_info[] = array("one"=>"<b>".$default_id_label.":</b>","two"=>$TPL["timeSheetID"],"three"=>"<b>Date Issued:</b>","four"=>date("d/m/Y"));
        $ts_info[] = array("one"=>"<b>Client:</b>"        ,"two"=>$TPL["clientName"],"three"=>"<b>Project:</b>","four"=>$TPL["timeSheet_projectName"]);
        $ts_info[] = array("one"=>"<b>".$default_contractor_label.":</b>"    ,"two"=>$TPL["timeSheet_personName"],"three"=>"<b>Billing Period:</b>","four"=>$TPL["period"]);
        if ($timeSheetPrintMode == "estimate") { // This line needs to be glued to the above line
          $temp = array_pop($ts_info);
          $temp["three"] = ""; // Nuke Billing Period for the Estimate version of the pdf.
          $temp["four"] = ""; // Nuke Billing Period for the Estimate version of the pdf.
          $ts_info[] = $temp;
        }


        $y = $pdf->ezTable($ts_info,$cols,"",$pdf_table_options2);

        $pdf->ezSetY($y -20);

        if ($timeSheetPrintMode == "money" || $timeSheetPrintMode == "estimate") {
          list($rows,$info) = $this->get_timeSheetItem_list_money($TPL["timeSheetID"]);
          $cols2 = array("desc"=>"Description","units"=>"Units","money"=>"Charges","gst"=>$TPL["taxName"]);
          $taxPercent = config::get_config_item("taxPercent");
          if ($taxPercent === '') unset($cols2["gst"]);
          $rows[] = array("desc"=>"<b>TOTAL</b>","units"=>$info["total_units"], "money"=>$info["total"],"gst"=>$info["total_gst"]);
          $y = $pdf->ezTable($rows,$cols2,"",$pdf_table_options3);
          $pdf->ezSetY($y -20);
          if ($taxPercent !== '') $totals[] = array("one"=>"TOTAL ".$TPL["taxName"],"two"=>$info["total_gst"]);
          $totals[] = array("one"=>"TOTAL CHARGES","two"=>$info["total"]);
          $totals[] = array("one"=>"<b>".$default_total_label."</b>","two"=>"<b>".$info["total_inc_gst"]."</b>");
          $y = $pdf->ezTable($totals,$cols3,"",$pdf_table_options4);

        } else if ($timeSheetPrintMode == "units") {
          list($rows,$info) = $this->get_timeSheetItem_list_units($TPL["timeSheetID"]);
          $cols2 = array("desc"=>"Description","units"=>"Units");
          $rows[] = array("desc"=>"<b>TOTAL</b>","units"=>"<b>".$info["total"]."</b>");
          $y = $pdf->ezTable($rows,$cols2,"",$pdf_table_options3);

        } else if ($timeSheetPrintMode == "items") {
          list($rows,$info) = $this->get_timeSheetItem_list_items($TPL["timeSheetID"]);
          $cols2 = array("date"=>"Date","units"=>"Units","multiplier_string"=>"Multiplier","desc"=>"Description");
          $rows[] = array("date"=>"<b>TOTAL</b>","units"=>"<b>".$info["total"]."</b>");
          $y = $pdf->ezTable($rows,$cols2,"",$pdf_table_options3);
        }


        $pdf->ezSetY($y -20);
        $pdf->ezText(str_replace(array("<br>","<br/>","<br />"),"\n",$TPL["footer"]),10);
        $pdf->ezStream(array("Content-Disposition"=>"timeSheet_".$timeSheetID.".pdf"));

      // Else HTML format
      } else {
        if(file_exists(ALLOC_LOGO)) {
          $TPL["companyName"] = '<img alt="Company logo" src="'.$TPL["url_alloc_logo"].'" />';
        }
        $TPL["this_tsp"] = $this;
        $TPL["main_alloc_title"] = "Time Sheet - ".APPLICATION_NAME;
        include_template(dirname(__FILE__)."/../templates/timeSheetPrintM.tpl");
      }

    }
  }

}

?>
