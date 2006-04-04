<?php
define("NO_AUTH",true);
include("alloc.inc");



include_template("templates/checkRepeatM.tpl");




$dbTransactionRepeat = new db_alloc;
$dbMaxDate = new db_alloc;
$transactionRepeat = new transactionRepeat;
$expenseForm = new expenseForm;

$today = mktime(0, 0, 0, date(m), date(d), date(Y));



atomicSwing();




function atomicSwing() {

  global $today, $dbTransactionRepeat, $dbMaxDate, $transactionRepeat, $expenseForm;

  echo("<br>");
  echo("<br><b>Today = <b>".strftime("%Y-%m-%d", $today)."<br><br>");

  $dbTransactionRepeat->query("select * from transactionRepeat");

  while ($dbTransactionRepeat->next_record()) {
    $transactionRepeat->read_db_record($dbTransactionRepeat);

    $startDate = get_date_stamp($transactionRepeat->get_value("transactionStartDate"));
    $finishDate = get_date_stamp($transactionRepeat->get_value("transactionFinishDate"));
    $timeBasisString = $transactionRepeat->get_value("paymentBasis");

    $maxDateQuery = "SELECT max(transactionDate) AS latestDate 
		FROM transaction,expenseForm 
		WHERE expenseForm.transactionRepeatID=".$transactionRepeat->get_id()."
		AND expenseForm.expenseFormID=transaction.expenseFormID";

    $dbMaxDate->query($maxDateQuery);
    $dbMaxDate->next_record();
    $mostRecentTransactionDate = get_date_stamp($dbMaxDate->f("latestDate"));

    if ($mostRecentTransactionDate != -1) {
      $nextScheduled = timeWarp($mostRecentTransactionDate, $timeBasisString);
    } else {
      $nextScheduled = get_date_stamp($transactionRepeat->get_value("transactionStartDate"));
    }


    while ($nextScheduled <= $today && $nextScheduled >= $startDate && $nextScheduled <= $finishDate) {
      // echo $nextScheduled . " should be smaller or equal than " . $today . " AND greater than or equal to " 
      // . $startDate . " AND smaller than or equal to " . $finishDate . "<br>"; 
      createTransaction($nextScheduled);
      $nextScheduled = timeWarp($nextScheduled, $timeBasisString);
    }
  }
}





function timeWarp($mostRecent, $basis) {

  if ($basis == "weekly") {
    return mktime(0, 0, 0, date("m", $mostRecent), date("d", $mostRecent) + 7, date("Y", $mostRecent));
  }
  if ($basis == "fortnightly") {
    return mktime(0, 0, 0, date("m", $mostRecent), date("d", $mostRecent) + 14, date("Y", $mostRecent));
  }
  if ($basis == "monthly") {
    return mktime(0, 0, 0, date("m", $mostRecent) + 1, date("d", $mostRecent), date("Y", $mostRecent));
  }
  if ($basis == "quarterly") {
    return mktime(0, 0, 0, date("m", $mostRecent) + 3, date("d", $mostRecent), date("Y", $mostRecent));
  }
  if ($basis == "yearly") {
    return mktime(0, 0, 0, date("m", $mostRecent), date("d", $mostRecent), date("Y", $mostRecent) + 1);
  }
}





function createTransaction($nextScheduled) {

  global $transactionRepeat, $dbTransactionRepeat, $expenseForm, $transaction;
  echo $nextScheduled;

  $transactionRepeat->read_db_record($dbTransactionRepeat);

  $expenseForm = new expenseForm;
  $expenseForm->set_value("transactionRepeatID", $transactionRepeat->get_id("transactionRepeatID"));
  $expenseForm->set_value("reimbursementRequired", $transactionRepeat->get_value("reimbursementRequired"));
  $expenseForm->save();

  $transaction = new transaction;
  $transaction->set_value("tfID", $transactionRepeat->get_value("tfID"));
  $transaction->set_value("companyDetails", $transactionRepeat->get_value("companyDetails"));
  $transaction->set_value("amount", -$transactionRepeat->get_value("amount"));
  $transaction->set_value("product", $transactionRepeat->get_value("product"));
  $transaction->set_value("transactionType", $transactionRepeat->get_value("transactionType"));
  $transaction->set_value("status", $transactionRepeat->get_value("status"));
  $transaction->set_value("expenseFormID", $expenseForm->get_id("expenseFormID"));
  $transaction->set_value("transactionDate", date("Y-m-d", $nextScheduled));
  $transaction->save();

  echo("<b><u><br>PRODUCT = ".$transactionRepeat->get_value("product"));
  echo("<br>COMPANY = ".$transactionRepeat->get_value("companyDetails"));
  echo("<br>TRANSACTIONDATE = ".$transaction->get_value("transactionDate"));
  echo("<br>AMOUNT = ".$transaction->get_value("amount"));
  echo("<br>Payment Basis = ".$transactionRepeat->get_value("paymentBasis"));
  echo("<br>TF = ".get_tf_name($transaction->get_value("tfID"))."</u>");
  echo("<br>amount = ".$transactionRepeat->get_value("amount"));
  echo("<br>");
  echo("<br><u>IF ".$expenseForm->get_id()." == ".$transaction->get_value("expenseFormID"));
  echo(" AND ".$expenseForm->get_value("transactionRepeatID")." == ".$transactionRepeat->get_id()." ...then all is good</u>");
  echo("<br>");
  echo("<br>");
  echo("<hr width='50%' align='left'>");

}





?>
