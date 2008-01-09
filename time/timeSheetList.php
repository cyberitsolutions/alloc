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
define("PAGE_IS_PRINTABLE",1);


$defaults = array("showHeader"=>true
                 ,"showProjectLink"=>true
                 ,"showAmount"=>true
                 ,"showAmountTotal"=>true
                 ,"showDuration"=>true
                 ,"showPerson"=>true
                 ,"showDateFrom"=>true
                 ,"showDateTo"=>true
                 ,"showStatus"=>true
                 ,"url_form_action"=>$TPL["url_alloc_timeSheetList"]
                 ,"form_name"=>"timeSheetList_filter"
                 );

function show_filter() {
  global $TPL,$defaults;
  $_FORM = timeSheet::load_form_data($defaults);
  $arr = timeSheet::load_timeSheet_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/timeSheetListFilterS.tpl");
}

function show_timeSheet_list() {
  global $defaults;
  $_FORM = timeSheet::load_form_data($defaults);
  echo timeSheet::get_timeSheet_list($_FORM);
}

$TPL["main_alloc_title"] = "Timesheet List - ".APPLICATION_NAME;
include_template("templates/timeSheetListM.tpl");
page_close();
?>
