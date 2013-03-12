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

$current_user =& singleton("current_user");

function show_filter() {
  global $TPL;
  global $defaults;
  $arr = timeSheetGraph::load_filter($defaults);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/timeSheetGraphFilterS.tpl");
}

$defaults = array("url_form_action"=>$TPL["url_alloc_timeSheetGraph"]
                 ,"form_name"=>"timeSheetGraph_filter"
                 ,"groupBy"=>"day"
                 ,"personID"=>$current_user->get_id()
                 );


$_FORM = timeSheetGraph::load_filter($defaults);

if ($_FORM["groupBy"] == "day") {
  $TPL["chart1"] = timeSheetItem::get_total_hours_worked_per_day($_FORM["personID"],$_FORM["dateFrom"],$_FORM["dateTo"]);
} else if ($_FORM["groupBy"] == "month") {
  $TPL["chart1"] = timeSheetItem::get_total_hours_worked_per_month($_FORM["personID"],$_FORM["dateFrom"],$_FORM["dateTo"]);
}

$TPL["groupBy"] = $_FORM["groupBy"];

include_template("templates/timeSheetGraphM.tpl");

?>
