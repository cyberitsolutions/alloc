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

class expenseForm extends db_entity {
  var $data_table = "expenseForm";

  function expenseForm() {
    $this->db_entity();
    $this->key_field = new db_text_field("expenseFormID");
    $this->data_fields = array("expenseFormModifiedUser"=>new db_text_field("expenseFormModifiedUser")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "paymentMethod"=>new db_text_field("paymentMethod")
                               , "reimbursementRequired"=>new db_text_field("reimbursementRequired", "Reimbursement Required", "", array("empty_to_null"=>false))
                               , "transactionRepeatID"=>new db_text_field("transactionRepeatID", "Transaction Repeat", "", array("empty_to_null"=>false))
                               , "enteredBy"=>new db_text_field("enteredBy")
                               , "expenseFormFinalised"=>new db_text_field("expenseFormFinalised", "Expense Finalised", "", array("empty_to_null"=>false))
      );
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }
    // Return true if this user created the expense form
    if ($person->get_id() == $this->get_value("enteredBy", DST_VARIABLE)) {
      return true;
    }
    // Return true if any of the transactions on the expense form are accessible by the current user
    $query = "SELECT * FROM transaction WHERE expenseFormID=".$this->get_id();
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $transaction = new transaction;
      $transaction->read_db_record($db, false);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }

    if ($current_user->have_role("admin") || $current_user->have_role("god")) {
      return true;
    }

    return false;
  }

  // This sets the status of the expense form.
  // Actually, the expense form doesn't have its own status - this sets the status of the transactions on the expense form
  function set_status($status) {
    global $current_user;

    $transactions = $this->get_foreign_objects("transaction");
    while (list(, $transaction) = each($transactions)) {
      $transaction->set_value("status", $status);
      $transaction->save();
    }
  }

  function insert() {
    global $current_user;
    $this->set_value("enteredBy", $current_user->get_id());
    db_entity::insert();
  }

  function delete_transactions($transactionID="") {
    global $TPL;

    $transactionID and $extra_sql = sprintf("AND transactionID = %d",$transactionID);

    $db = new db_alloc;
    if ($this->is_owner()) {
      $db->query(sprintf("DELETE FROM transaction WHERE expenseFormID = %d %s",$this->get_id(),$extra_sql));
      $transactionID and $TPL["message_good"][] = "Expense Form Line Item deleted.";
    }
  }


  
}



?>
