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

if (!$current_user->have_role("admin")) {
  alloc_die();
}

function load_transaction_total($info_field, $transaction_type) {
  global $tf_info;

  $query = prepare("SELECT tf.tfID, tf.tfName, sum(transaction.amount) AS total_amount
                      FROM tf 
                 LEFT JOIN transaction ON transaction.tfID = tf.tfID 
                                      AND transactionType='%s' 
                                      AND transactionDate LIKE '%02d-%02d-%%'
                                      AND transaction.status <> 'rejected'
                      GROUP BY tfID", $transaction_type, $_GET["year"], $_GET["month"]);
  $db = new db_alloc;
  $db->query($query);

  while ($db->next_record()) {
    $tfName = $db->f("tfName");
    $tf_info[$tfName][$info_field] = $db->f("total_amount");
  }
}

function load_balances($info_field, $where) {
  global $tf_info;

  $query = "SELECT tfName, sum(amount) AS balance 
              FROM tf 
         LEFT JOIN transaction ON tf.tfID = transaction.tfID 
                              AND $where
              WHERE transaction.status <> 'rejected'
              GROUP BY tf.tfID";

  $db = new db_alloc;
  $db->query($query);

  while ($db->next_record()) {
    $tfName = $db->f("tfName");
    $tf_info[$tfName][$info_field] = $db->f("balance");
  }
}

function load_tf_info() {
  global $tf_info;
  global $start_date;
  global $end_date;

  $tf_info = array();
  load_transaction_total("expenses", "expense");
  load_transaction_total("salaries", "salary");
  load_transaction_total("invoices", "invoice");

  $closing_balance_where = "transactionDate <= '".date("Y-m-d", $end_date)."'";
  load_balances("closing_balance", $closing_balance_where);

  $opening_balance_where = "transactionDate < '".date("Y-m-d", $start_date)."'";
  load_balances("opening_balance", $opening_balance_where);
}

function show_tf_balances($template) {
  global $TPL;
  global $tf_info;

  ksort($tf_info);

  reset($tf_info);

  while (list($tfName, $info) = each($tf_info)) {
    $i++;

    $TPL["tfName"] = $tfName;
    $TPL["expenses"] = number_format($info["expenses"], 2);
    $TPL["salaries"] = number_format($info["salaries"], 2);
    $TPL["invoices"] = number_format($info["invoices"], 2);
    $TPL["openingBalance"] = number_format($info["opening_balance"], 2);
    $TPL["closingBalance"] = number_format($info["closing_balance"], 2);
    include_template($template);
  }
}

function show_transaction_list($transactionType) {
  global $TPL;

  $TPL["total_amount"] = 0;

  $query = prepare("SELECT transaction.*, tf.tfName  
                      FROM transaction LEFT JOIN tf ON transaction.tfID = tf.tfID
                      WHERE transactionDate LIKE '%04d-%02d-%%' AND transactionType='%s'
                      ORDER BY transactionDate", $_GET["year"], $_GET["month"], $transactionType);
  $db = new db_alloc;
  $db->query($query);


  while ($db->next_record()) {
    $i++;

    $transaction = new transaction;
    $transaction->read_db_record($db);
    $transaction->set_values();

    $TPL["tfName"] = $db->f("tfName");
    $TPL["total_amount"] += $transaction->get_value("amount");
    $TPL["amount"] = number_format($TPL["amount"], 2);

    include_template("templates/reconciliationTransactionR.tpl");
  }

  $TPL["total_amount"] = number_format($TPL["total_amount"], 2);
}

if (!isset($_GET["month"])) {
  $_GET["month"] = date("m");
}
if (!isset($_GET["year"])) {
  $_GET["year"] = date("Y");
}

$start_date = mktime(0, 0, 0, $_GET["month"], 1, $_GET["year"]);
$days_in_month = date("t", $start_date);
$end_date = mktime(0, 0, 0, $_GET["month"], $days_in_month, $_GET["year"]);

if ($_GET["month"] == 1) {
  $prev_month = 12;
  $next_month = 2;
  $prev_year = $_GET["year"] - 1;
  $next_year = $_GET["year"];
} else if ($_GET["month"] == 12) {
  $prev_month = 11;
  $next_month = 1;
  $prev_year = $_GET["year"];
  $next_year = $_GET["year"] + 1;
} else {
  $prev_month = $_GET["month"] - 1;
  $next_month = $_GET["month"] + 1;
  $prev_year = $_GET["year"];
  $next_year = $_GET["year"];
}

$base_url = $TPL["url_alloc_reconciliationReport"];
$prev_url = $base_url."&month=$prev_month&year=$prev_year";
$next_url = $base_url."&month=$next_month&year=$next_year";
$TPL["month_links"] = "<a href=\"$prev_url\">&lt;-- Previous Month</a> &nbsp; ".date("F, Y", $start_date)."  &nbsp; <a href=\"$next_url\">Next Month --&gt;</a>";


$TPL["start_date"] = date("Y-m-d", $start_date);
$TPL["end_date"] = date("Y-m-d", $end_date);

load_tf_info();

$TPL["main_alloc_title"] = "Reconciliation Report - ".APPLICATION_NAME;
include_template("templates/reconciliationReportM.tpl");

?>
