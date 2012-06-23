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

require_once("../alloc.php");

function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = timeSheet::load_form_data($defaults);
  $arr = timeSheet::load_timeSheet_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/timeSheetListFilterS.tpl");
}

$defaults = array("url_form_action"=>$TPL["url_alloc_timeSheetList"]
                 ,"form_name"=>"timeSheetList_filter"
                 ,"showFinances"=>$_REQUEST["showFinances"]
                 ,"dateFromComparator"=>">="
                 ,"dateToComparator"=>"<="
                 );

$_FORM = timeSheet::load_form_data($defaults);
$rtn = timeSheet::get_list($_FORM);
$TPL["timeSheetListRows"] = $rtn["rows"];
$TPL["timeSheetListExtra"] = $rtn["extra"];

if (!$current_user->prefs["timeSheetList_filter"]) {
  $TPL["message_help"][] = "

allocPSA allows you to record the time that you've worked on various
Projects using Time Sheets. This page allows you to view a list of Time Sheets. 

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Time Sheets. 
If you would prefer to create a new Time Sheet, click the <b>New Time Sheet</b> link
in the top-right hand corner of the box below.";
}




$TPL["main_alloc_title"] = "Timesheet List - ".APPLICATION_NAME;
include_template("templates/timeSheetListM.tpl");
?>
