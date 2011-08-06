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


$TPL["main_alloc_title"] = "Expense Form List - ".APPLICATION_NAME;
include_template("templates/expenseFormListM.tpl");

function show_expense_form_list($template_name) {

  global $db, $dbTwo, $TPL;

  $db = new db_alloc;
  $dbTwo = new db_alloc;
  $transDB = new db_alloc;
  $expenseForm = new expenseForm;
  $transaction = new transaction;

  $db->query("SELECT expenseForm.*, SUM(transaction.amount * pow(10,-currencyType.numberToBasic)) as formTotal, transaction.currencyTypeID
                FROM expenseForm, transaction
           LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
               WHERE expenseForm.expenseFormID = transaction.expenseFormID
                 AND transaction.status = 'pending'
            GROUP BY expenseForm.expenseFormID, transaction.currencyTypeID
            ORDER BY expenseFormID");

  $rr_options = expenseForm::get_reimbursementRequired_array();

  while ($row = $db->row()) {
    $amounts[$row["expenseFormID"]].= $sp[$row["expenseFormID"]].page::money($row["currencyTypeID"],$row["formTotal"],"%s%m");
    $sp[$row["expenseFormID"]] = " + ";
    $rows[$row["expenseFormID"]] = $row;
  }
  foreach ((array)$rows as $expenseFormID => $row) {
    $expenseForm = new expenseForm();
    if ($expenseForm->read_row_record($row, false)) {
      $i++;
      $expenseForm->set_values();
      //$TPL["formTotal"] =  -$db->f("formTotal");
      $TPL["formTotal"] = $amounts[$expenseFormID];
      $TPL["expenseFormModifiedUser"] = person::get_fullname($expenseForm->get_value("expenseFormModifiedUser"));
      $TPL["expenseFormModifiedTime"] = $expenseForm->get_value("expenseFormModifiedTime");
      $TPL["expenseFormCreatedUser"] = person::get_fullname($expenseForm->get_value("expenseFormCreatedUser"));
      $TPL["expenseFormCreatedTime"] = $expenseForm->get_value("expenseFormCreatedTime");
      unset($extra);
      $expenseForm->get_value("paymentMethod") and $extra = " (".$expenseForm->get_value("paymentMethod").")";
      $TPL["rr_label"] = $rr_options[$expenseForm->get_value("reimbursementRequired")].$extra;
      include_template($template_name);
    }
  }

}

function show_pending_transaction_list($template_name) {
  global $TPL;
  $transactionTypes = transaction::get_transactionTypes();
  $q = "SELECT * FROM transaction 
            LEFT JOIN transactionRepeat on transactionRepeat.transactionRepeatID = transaction.transactionRepeatID 
                WHERE transaction.transactionRepeatID IS NOT NULL AND transaction.status = 'pending'";
  $db = new db_alloc;
  $db->query($q);
  while ($db->next_record()) {
    $i++;
    $transaction = new transaction;
    $transaction->read_db_record($db);
    $transaction->set_values();
    $transactionRepeat = new transactionRepeat;
    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_values();
    $TPL["transactionType"] = $transactionTypes[$transaction->get_value("transactionType")];
    $TPL["formTotal"] =  -$db->f("amount");
    $TPL["transactionModifiedTime"] = $transaction->get_value("transactionModifiedTime");
    $TPL["transactionCreatedTime"] = $transaction->get_value("transactionCreatedTime");
    $TPL["transactionCreatedUser"] = person::get_fullname($transaction->get_value("transactionCreatedUser"));
    include_template($template_name);
  }
}


?>
