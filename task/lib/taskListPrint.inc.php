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

class taskListPrint {

  function get_printable_file($_FORM=array()) {
    global $TPL;
  
    $db = new db_alloc();

    $TPL["companyName"] = config::get_config_item("companyName");
    $TPL["companyNos1"] = config::get_config_item("companyACN");
    $TPL["companyNos2"] = config::get_config_item("companyABN");
    $TPL["img"] = config::get_config_item("companyImage");
    $TPL["companyContactAddress"] = config::get_config_item("companyContactAddress");
    $TPL["companyContactAddress2"] = config::get_config_item("companyContactAddress2");
    $TPL["companyContactAddress3"] = config::get_config_item("companyContactAddress3");
    $email = config::get_config_item("companyContactEmail");
    $email and $TPL["companyContactEmail"] = "Email: ".$email;
    $web = config::get_config_item("companyContactHomePage");
    $web and $TPL["companyContactHomePage"] = "Web: ".$web;
    $phone = config::get_config_item("companyContactPhone");
    $fax = config::get_config_item("companyContactFax");
    $phone and $TPL["phone"] = "Ph: ".$phone;
    $fax and $TPL["fax"] = "Fax: ".$fax;


    $taskPriorities = config::get_config_item("taskPriorities");
    $projectPriorities = config::get_config_item("projectPriorities");

    // Add requested fields to pdf
    $_FORM["showEdit"] = false;
                                  $fields["taskID"]               = "ID";
                                  $fields["taskName"]             = "Task";
    $_FORM["showProject"]     and $fields["projectName"]          = "Project";
    $_FORM["showPriority"]    and $fields["priorityFactor"]       = "Pri";
    $_FORM["showPriority"]    and $fields["taskPriority"]         = "Task Pri";
    $_FORM["showPriority"]    and $fields["projectPriority"]      = "Proj Pri";
    $_FORM["showCreator"]     and $fields["creator_name"]         = "Creator";
    $_FORM["showManager"]     and $fields["manager_name"]         = "Manager";
    $_FORM["showAssigned"]    and $fields["assignee_name"]        = "Assigned To";
    $_FORM["showDate1"]       and $fields["dateTargetStart"]      = "Targ Start";
    $_FORM["showDate2"]       and $fields["dateTargetCompletion"] = "Targ Compl";
    $_FORM["showDate3"]       and $fields["dateActualStart"]      = "Start";
    $_FORM["showDate4"]       and $fields["dateActualCompletion"] = "Compl";
    $_FORM["showDate5"]       and $fields["dateCreated"]          = "Created";
    $_FORM["showTimes"]       and $fields["timeBestLabel"]        = "Best";
    $_FORM["showTimes"]       and $fields["timeExpectedLabel"]    = "Likely";
    $_FORM["showTimes"]       and $fields["timeWorstLabel"]       = "Worst";
    $_FORM["showTimes"]       and $fields["timeActualLabel"]      = "Actual";
    $_FORM["showTimes"]       and $fields["timeLimitLabel"]       = "Limit"; 
    $_FORM["showPercent"]     and $fields["percentComplete"]      = "%";
    $_FORM["showStatus"]      and $fields["taskStatusLabel"]      = "Status";

    $rows = task::get_list($_FORM);
    $taskListRows = array();
    foreach ((array)$rows as $row) {
      $row["taskPriority"] = $taskPriorities[$row["priority"]]["label"];
      $row["projectPriority"] = $projectPriorities[$row["projectPriority"]]["label"];
      $row["taskDateStatus"] = strip_tags($row["taskDateStatus"]);
      $row["percentComplete"] = strip_tags($row["percentComplete"]);
      $taskListRows[] = $row;
    }


    if ($_FORM["format"] != "html" && $_FORM["format"] != "html_plus") {

      // Build PDF document
      $font1 = ALLOC_MOD_DIR."util/fonts/Helvetica.afm";
      $font2 = ALLOC_MOD_DIR."util/fonts/Helvetica-Oblique.afm";

      $pdf_table_options = array("showLines"=>0,"shaded"=>0,"showHeadings"=>0,"xPos"=>"left"
                                ,"xOrientation"=>"right","fontSize"=>10,"rowGap"=>0,"fontSize"=>10);
      $pdf_table_options3 = array("showLines"=>2,"shaded"=>0,"width"=>750, "xPos"=>"center","fontSize"=>10
                                 ,"lineCol"=>array(0.8, 0.8, 0.8),"splitRows"=>1,"protectRows"=>0);

      $pdf = new Cezpdf(null,'landscape');
      $pdf->ezSetMargins(40,40,40,40);
      $pdf->selectFont($font1);
      $pdf->ezStartPageNumbers(436,30,10,'center','Page {PAGENUM} of {TOTALPAGENUM}');
      $pdf->ezSetY(560);

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
      $pdf->line(40,$line_y,801,$line_y);

      $pdf->ezSetY(570);

      $image_jpg = ALLOC_LOGO;
      if (file_exists($image_jpg)) {
        $pdf->ezImage($image_jpg,0,0,'none');
        $y = 700;
      } else {
        $y = $pdf->ezText($TPL["companyName"],27,array("justification"=>"right"));
      }
      $nos_y = $line_y + 22;
      $TPL["companyNos2"] and $nos_y = $line_y + 34;
      $pdf->ezSetY($nos_y);
      $TPL["companyNos1"] and $y = $pdf->ezText($TPL["companyNos1"],10, array("justification"=>"right"));
      $TPL["companyNos2"] and $y = $pdf->ezText($TPL["companyNos2"],10, array("justification"=>"right"));

      $pdf->ezSetY($line_y -10);
      $y = $pdf->ezText("Task List",20,array("justification"=>"center"));
      $pdf->ezSetY($y -20);

      $y = $pdf->ezTable($taskListRows,$fields,"",$pdf_table_options3);

      $pdf->ezSetY($y -20);
      $pdf->ezStream();

    // Else HTML format
    } else {
      echo task::get_list_html($taskListRows,$_FORM);
    }

  }

}

?>
