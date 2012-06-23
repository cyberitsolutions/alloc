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

$download = $_GET["download"] or $download = $_POST["download"];
$applyFilter = $_GET["applyFilter"] or $applyFilter = $_POST["applyFilter"];

$defaults = array("url_form_action"=>$TPL["url_alloc_searchTransaction"]
                 ,"form_name"=>"searchTransaction_filter"
                 ,"applyFilter"=>$applyFilter
                 );

function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = transaction::load_form_data($defaults);
  $arr = transaction::load_transaction_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/searchTransactionFilterS.tpl");
}

if ($download) {
  $_FORM = transaction::load_form_data($defaults);
  $rtn = transaction::get_list($_FORM);
  $totals = $rtn["totals"];
  $rows = $rtn["rows"];
  $csv = transaction::arr_to_csv($rows);
  header('Content-Type: application/octet-stream');
  header("Content-Length: ".strlen($csv));
  header('Content-Disposition: attachment; filename="'.date("Ymd_His").'.csv"');
  echo $csv;
  exit();
}

if ($applyFilter) {
  $_FORM = transaction::load_form_data($defaults);
  $rtn = transaction::get_list($_FORM);
  $TPL["totals"] = $rtn["totals"];
  $TPL["transactionListRows"] = $rtn["rows"];
}

$TPL["main_alloc_title"] = "Search Transactions - ".APPLICATION_NAME;
include_template("templates/searchTransactionM.tpl");


?>
