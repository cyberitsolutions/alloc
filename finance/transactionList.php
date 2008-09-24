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

$tfID = $_GET["tfID"] or $tfID = $_POST["tfID"];
$startDate = $_GET["startDate"] or $startDate = $_POST["startDate"];
$endDate = $_GET["endDate"] or $endDate = $_POST["endDate"];
$monthDate = $_GET["monthDate"] or $monthDate = $_POST["monthDate"];
$download = $_GET["download"] or $download = $_POST["download"];
$applyFilter = $_GET["applyFilter"] or $applyFilter = $_POST["applyFilter"];

if (!$startDate && !$endDate && !$monthDate && !$applyFilter) {
  $monthDate = date("Y-m-d");
}

$defaults = array("url_form_action"=>$TPL["url_alloc_transactionList"]
                 ,"form_name"=>"transactionList_filter"
                 ,"applyFilter"=>$applyFilter
                 ,"tfID"=>$tfID
                 ,"startDate"=>$startDate
                 ,"endDate"=>$endDate
                 ,"monthDate"=>$monthDate
                 );

if ($download) {
  $_FORM = transaction::load_form_data($defaults);
  $_FORM["return"] = "csv";
  $csv = transaction::get_list($_FORM);
  header('Content-Type: application/octet-stream');
  header("Content-Length: ".strlen($csv));
  header('Content-Disposition: attachment; filename="'.date("Ymd_His").'.csv"');
  echo $csv;
  exit();
}


function show_filter() {
  global $TPL,$defaults;
  $_FORM = transaction::load_form_data($defaults);
  $arr = transaction::load_transaction_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/transactionFilterS.tpl");
}

function show_transaction_list() {
  global $defaults;
  $_FORM = transaction::load_form_data($defaults);
  echo transaction::get_list($_FORM);
}


// Check perm of requested tf
$tf = new tf;
$tf->set_id($tfID);
$tf->select();
$tf->check_perm();
$rtn["pending_amount"] = sprintf("%0.2f",$tf->get_balance(array("status"=>"pending")));
$rtn["title"] = "Statement for TF ".$tf->get_value("tfName")." from ".$startDate." to ".$endDate;
$TPL["tfID"] = $tfID;

// Transaction status filter
if ($status) {
  $where["status"] = $status;
}

// Transaction status filter
if ($transactionType) {
  $where["transactionType"] = $transactionType;
}

// WHERE transactionModifiedTime or transactionDate is <= end date
$where[$sortTransactions] = array(" <= ",$statement_end_date);

// Add pending transactions filter to get pending amount balance
$where["status"] = "pending";
$TPL["pending_amount"] = sprintf("%0.2f",$tf->get_balance(array("status"=>"pending")));

// Overall balance
$TPL["balance"] = sprintf("%0.2f",$tf->get_balance());

$TPL["title"] = "Statement for TF ".$tf->get_value("tfName");


include_template("templates/transactionListM.tpl");

?>
