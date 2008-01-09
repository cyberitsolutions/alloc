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


$TPL["main_alloc_title"] = "Expense Form List - ".APPLICATION_NAME;
include_template("templates/expenseFormListM.tpl");

function show_expense_form_list($template_name) {

  global $db, $dbTwo, $TPL;

  $db = new db_alloc;
  $dbTwo = new db_alloc;
  $transDB = new db_alloc;
  $expenseForm = new expenseForm;
  $transaction = new transaction;

  $db->query("SELECT expenseForm.*, SUM(transaction.amount) as formTotal
                FROM expenseForm, transaction
               WHERE expenseForm.expenseFormID = transaction.expenseFormID
                 AND transaction.status = 'pending'
            GROUP BY expenseForm.expenseFormID
            ORDER BY expenseFormID");

  $rr_options = expenseForm::get_reimbursementRequired_array();

  while ($db->next_record()) {
    $expenseForm = new expenseForm();
    if ($expenseForm->read_db_record($db, false)) {
      $i++;
      $TPL["row_class"] = "odd";
      $i % 2 == 0 and $TPL["row_class"] = "even";
      $expenseForm->set_tpl_values();
      $TPL["formTotal"] = sprintf("%0.2f", -$db->f("formTotal"));
      $TPL["expenseFormModifiedUser"] = person::get_fullname($expenseForm->get_value("expenseFormModifiedUser"));
      $TPL["expenseFormModifiedTime"] = get_mysql_date_stamp($expenseForm->get_value("expenseFormModifiedTime"));
      $TPL["expenseFormCreatedUser"] = person::get_fullname($expenseForm->get_value("expenseFormCreatedUser"));
      $TPL["expenseFormCreatedTime"] = get_mysql_date_stamp($expenseForm->get_value("expenseFormCreatedTime"));
      unset($extra);
      $expenseForm->get_value("paymentMethod") and $extra = " (".$expenseForm->get_value("paymentMethod").")";
      $TPL["rr_label"] = $rr_options[$expenseForm->get_value("reimbursementRequired")].$extra;
      include_template($template_name);
    }
  }

}

function show_pending_transaction_list($template_name) {
  global $TPL;
  $q = "SELECT * FROM transaction 
            LEFT JOIN transactionRepeat on transactionRepeat.transactionRepeatID = transaction.transactionRepeatID 
                WHERE transaction.transactionRepeatID IS NOT NULL AND transaction.status = 'pending'";
  $db = new db_alloc;
  $db->query($q);
  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";
    $transaction = new transaction;
    $transaction->read_db_record($db);
    $transaction->set_tpl_values();
    $transactionRepeat = new transactionRepeat;
    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_tpl_values();
    $TPL["formTotal"] = sprintf("%0.2f", -$db->f("amount"));
    $TPL["transactionModifiedTime"] = get_mysql_date_stamp($transaction->get_value("transactionModifiedTime"));
    $TPL["transactionCreatedTime"] = get_mysql_date_stamp($transaction->get_value("transactionCreatedTime"));
    $TPL["transactionCreatedUser"] = person::get_fullname($transaction->get_value("transactionCreatedUser"));
    include_template($template_name);
  }
}

page_close();



?>
