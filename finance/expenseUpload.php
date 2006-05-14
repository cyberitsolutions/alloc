<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

check_entity_perm("transaction", PERM_FINANCE_UPLOAD_EXPENSES_FILE);

$field_map = array("date"=>0, "account"=>1, "num"=>2, "description"=>3, "memo"=>4, "category"=>5, "clr"=>6, "amount"=>7);

if ($_POST["upload"]) {
  $db = new db_alloc;
  is_uploaded_file($_FILES["expenses_file"]["tmp_name"]) || die("File referred to was not an uploaded file"); // Prevent attacks by setting $expenses_file in URL
  $lines = file($_FILES["expenses_file"]["tmp_name"]);

  reset($lines);
  while (list(, $line) = each($lines)) {
    // Ignore blank lines
    if (eregi("^(\\t)*$", trim($line))) {
      continue;
    }
    // Read field values from the line
    $fields = explode("\t", $line);

    $date = trim($fields[$field_map["date"]]);
    $account = trim($fields[$field_map["account"]]);
    $num = trim($fields[$field_map["num"]]);
    $description = trim($fields[$field_map["description"]]);
    $memo = trim($fields[$field_map["memo"]]);
    $category = trim($fields[$field_map["category"]]);
    $clr = trim($fields[$field_map["clr"]]);
    $amount = trim($fields[$field_map["amount"]]);

    // Idenitify lines containing totals as the date field will contain the text TOTAL
    // Identify the column headings as the date field will be "Date"
    // Ignore ignore these lines
    if (eregi("total", $date) || $date == "Date") {
      continue;
    }
    // Convert the date to yyyy-mm-dd
    if (!eregi("^([0-9]{1,2})/([0-9]{1,2})'([0-9])$", $date, $matches)) {
      $msg.= "<b>Warning: Could not convert date '$date'</b><br>";
      continue;
    }
    $date = sprintf("200%d-%02d-%02d", $matches[3], $matches[2], $matches[1]);


    // Strip $ and , from amount
    $amount = ereg_replace("[\$,]", "", $amount);
    if (!ereg("^-?[0-9]+(\\.[0-9]+)?$", $amount)) {
      $msg.= "<b>Warning: Could not convert amount '$amount'</b><br>";
      continue;
    }
    // Ignore positive amounts
    if ($amount > 0) {
      $msg.= "<b>Warning: Ignored positive '$amount' for $memo on $date</b><br>";
      continue;
    }
    // Find the TF ID for the expense
    $query = sprintf("SELECT * FROM tf WHERE quickenAccount='%s'", addslashes($account));
    $db->query($query);
    if ($db->next_record()) {
      $tfID = $db->f("tfID");
    } else {
      $msg.= "<b>Warning: Could not find TF for account '$account'</b><br>";
      continue;
    }

    // Check for an existing transaction
    $query = sprintf("SELECT * FROM transaction WHERE transactionType='expense' AND transactionDate='%s' AND product='%s' AND amount > %0.3f and amount < %0.3f", addslashes($date), addslashes($memo), $amount - 0.004, $amount + 0.004);
    $db->query($query);
    if ($db->next_record()) {
      $msg.= "Warning: Expense '$memo' on $date already exixsts.<br>";
      continue;
    }
    // Create a transaction object and then save it
    $transaction = new transaction;
    $transaction->set_value("companyDetails", $description);
    $transaction->set_value("product", $memo);
    $transaction->set_value("amount", $amount);
    $transaction->set_value("status", "pending");
    $transaction->set_value("expenseFormID", "0");
    $transaction->set_value("tfID", $tfID);
    $transaction->set_value("quantity", 1);
    $transaction->set_value("invoiceItemID", "0");
    $transaction->set_value("transactionType", "expense");
    $transaction->set_value("transactionDate", "$date");
    $transaction->save();

    $msg.= "Expense '$memo' on $date saved.<br>";
  }
  $TPL["msg"] = $msg;
}

include_template("templates/expenseUploadM.tpl");

page_close();



?>
