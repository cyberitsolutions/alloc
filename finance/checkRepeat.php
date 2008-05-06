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

define("NO_AUTH",true);
require_once("../alloc.php");


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



global $current_user;

$db = new db_alloc;
$dbMaxDate = new db_alloc;
$today = mktime(0, 0, 0, date(m), date(d), date(Y));

echo("<br/>".date("Y-m-d")."<br/>");

$db->query("select * from transactionRepeat WHERE status = 'approved'");

while ($db->next_record()) {
  $transactionRepeat = new transactionRepeat;
  $transactionRepeat->read_db_record($db);

  $startDate = get_date_stamp($transactionRepeat->get_value("transactionStartDate"));
  $finishDate = get_date_stamp($transactionRepeat->get_value("transactionFinishDate"));
  $timeBasisString = $transactionRepeat->get_value("paymentBasis");

  $query = "SELECT max(transactionDate) AS latestDate FROM transaction WHERE transactionRepeatID=".$transactionRepeat->get_id();

  $dbMaxDate->query($query);
  $dbMaxDate->next_record();

  if (!$dbMaxDate->f("latestDate")) {
    $nextScheduled = timeWarp($startDate, $timeBasisString);
  } else {
    $mostRecentTransactionDate = format_date("U",$dbMaxDate->f("latestDate"));
    $nextScheduled = timeWarp($mostRecentTransactionDate, $timeBasisString);
  }

  //echo '<br/><br/>$nextScheduled <= $today && $nextScheduled >= $startDate && $nextScheduled <= $finishDate';
  //echo "<br/>".$nextScheduled." <= ".$today." && ".$nextScheduled." >= ".$startDate." && ".$nextScheduled." <= ".$finishDate;
  while ($nextScheduled <= $today && $nextScheduled >= $startDate && $nextScheduled <= $finishDate) {

    $tf = new tf;
    $tf->set_id($transactionRepeat->get_value("tfID"));
    if ($tf->get_value("status") != 'active') {
      continue;
    }

    $transaction = new transaction;
    $transaction->set_value("tfID", $transactionRepeat->get_value("tfID"));
    $transaction->set_value("companyDetails", $transactionRepeat->get_value("companyDetails"));
    $transaction->set_value("amount", -$transactionRepeat->get_value("amount"));
    $transaction->set_value("product", $transactionRepeat->get_value("product"));
    $transaction->set_value("transactionType", $transactionRepeat->get_value("transactionType"));
    $transaction->set_value("status", "pending");
    $transaction->set_value("transactionRepeatID", $transactionRepeat->get_id());
    $transaction->set_value("transactionDate", date("Y-m-d", $nextScheduled));
    $transaction->save();

    echo "\n<br>".$transaction->get_value("transactionDate");
    echo " ".$transactionRepeat->get_value("paymentBasis")." $".$transaction->get_value("amount")." for TF: ".get_tf_name($transaction->get_value("tfID"));
    echo " (transactionID: ".$transaction->get_id()." transactionRepeatID:".$transactionRepeat->get_id()." name:".$transactionRepeat->get_value("product").")";

    $nextScheduled = timeWarp($nextScheduled, $timeBasisString);
  }
}

$TPL["main_alloc_title"] = "Execute Repeating Expenses - ".APPLICATION_NAME;
include_template("templates/checkRepeatM.tpl");

?>
