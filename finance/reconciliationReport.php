<?php
require_once("alloc.inc");
check_entity_perm("transaction", PERM_FINANCE_RECONCILIATION_REPORT);

function load_transaction_total($info_field, $transaction_type) {
  global $tf_info, $month, $year;

  $query = sprintf("SELECT tf.tfID, tf.tfName, sum(transaction.amount) as total_amount
                      FROM tf 
                           LEFT JOIN transaction ON transaction.tfID = tf.tfID 
                                            AND transactionType='%s' 
                                            AND transactionDate LIKE '%02d-%02d-%%'
                                            AND transaction.status <> 'rejected'
                      GROUP BY tfID", addslashes($transaction_type), $year, $month);
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
              FROM tf LEFT JOIN transaction ON tf.tfID = transaction.tfID AND $where
              WHERE status <> 'rejected'
              GROUP BY tf.tfID";

  $db = new db_alloc;
  $db->query($query);

  while ($db->next_record()) {
    $tfName = $db->f("tfName");
    $tf_info[$tfName][$info_field] = $db->f("balance");
  }
}

function load_tf_info() {
  global $tf_info, $start_date, $end_date;

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
  global $TPL, $tf_info;

  ksort($tf_info);

  reset($tf_info);

  while (list($tfName, $info) = each($tf_info)) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

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
  global $month, $year, $TPL;

  $TPL["total_amount"] = 0;

  $query = sprintf("SELECT transaction.*, tf.tfName  
                      FROM transaction LEFT JOIN tf ON transaction.tfID = tf.tfID
                      WHERE transactionDate LIKE '%04d-%02d-%%' AND transactionType='%s'
                      ORDER BY transactionDate", $year, $month, addslashes($transactionType));
  $db = new db_alloc;
  $db->query($query);


  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

    $transaction = new transaction;
    $transaction->read_db_record($db);
    $transaction->set_tpl_values();

    $TPL["tfName"] = $db->f("tfName");
    $TPL["total_amount"] += $transaction->get_value("amount");
    $TPL["amount"] = number_format($TPL["amount"], 2);

    include_template("templates/reconciliationTransactionR.tpl");
  }

  $TPL["total_amount"] = number_format($TPL["total_amount"], 2);
}

if (!isset($month)) {
  $month = date("m");
}
if (!isset($year)) {
  $year = date("Y");
}

$start_date = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date("t", $start_date);
$end_date = mktime(0, 0, 0, $month, $days_in_month, $year);

if ($month == 1) {
  $prev_month = 12;
  $next_month = 2;
  $prev_year = $year - 1;
  $next_year = $year;
} else if ($month == 12) {
  $prev_month = 11;
  $next_month = 1;
  $prev_year = $year;
  $next_year = $year + 1;
} else {
  $prev_month = $month - 1;
  $next_month = $month + 1;
  $prev_year = $year;
  $next_year = $year;
}

$base_url = $TPL["url_alloc_reconciliationReport"];
$prev_url = $base_url."&month=$prev_month&year=$prev_year";
$next_url = $base_url."&month=$next_month&year=$next_year";
$TPL["month_links"] = "<a href=\"$prev_url\">&lt;-- Previous Month</a> &nbsp; ".date("F, Y", $start_date)."  &nbsp; <a href=\"$next_url\">Next Month --&gt;</a>";


$TPL["start_date"] = date("Y-m-d", $start_date);
$TPL["end_date"] = date("Y-m-d", $end_date);

load_tf_info();

include_template("templates/reconciliationReportM.tpl");

page_close();



?>
