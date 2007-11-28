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
    $this->key_field = new db_field("expenseFormID");
    $this->data_fields = array("expenseFormModifiedUser"=>new db_field("expenseFormModifiedUser")
                               , "expenseFormModifiedTime"=>new db_field("expenseFormModifiedTime")
                               , "paymentMethod"=>new db_field("paymentMethod")
                               , "reimbursementRequired"=>new db_field("reimbursementRequired", array("empty_to_null"=>false))
                               , "seekClientReimbursement"=>new db_field("seekClientReimbursement", array("empty_to_null"=>false))
                               , "transactionRepeatID"=>new db_field("transactionRepeatID", array("empty_to_null"=>false))
                               , "clientID"=>new db_field("clientID")
                               , "expenseFormCreatedUser"=>new db_field("expenseFormCreatedUser")
                               , "expenseFormCreatedTime"=>new db_field("expenseFormCreatedTime")
                               , "expenseFormFinalised"=>new db_field("expenseFormFinalised", array("empty_to_null"=>false))
                               , "expenseFormComment"=>new db_field("expenseFormComment")
      );
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }
    // Return true if this user created the expense form
    if ($person->get_id() == $this->get_value("expenseFormCreatedUser", DST_VARIABLE)) {
      return true;
    }
    // Return true if any of the transactions on the expense form are accessible by the current user
    $query = sprintf("SELECT * FROM transaction WHERE expenseFormID=%d",$this->get_id());
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

  function get_reimbursementRequired_array() {
    return array("0"=>"Unpaid"
                ,"1"=>"Paid by me"
                ,"2"=>"Paid by company"
                );
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

  function delete_transactions($transactionID="") {
    global $TPL;

    $transactionID and $extra_sql = sprintf("AND transactionID = %d",$transactionID);

    $db = new db_alloc;
    if ($this->is_owner()) {
      $db->query(sprintf("DELETE FROM transaction WHERE expenseFormID = %d %s",$this->get_id(),$extra_sql));
      $transactionID and $TPL["message_good"][] = "Expense Form Line Item deleted.";
    }
  }

  function get_invoice_link() {
    global $TPL;
    $db = new db_alloc();
    if ($this->get_id()) {
      $db->query("SELECT invoice.* FROM invoiceItem LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID WHERE expenseFormID = %s",$this->get_id());
      if ($db->next_record()) {
        return "<a href=\"".$TPL["url_alloc_invoice"]."invoiceID=".$db->f("invoiceID")."\">".$db->f("invoiceNum")."</a>";
      }
    }
  }

  function save_to_invoice() {
    if ($this->get_value("clientID")) {
      $client = $this->get_foreign_object("client");
      $db = new db_alloc;
      $q = sprintf("SELECT * FROM invoice WHERE clientID = %d AND invoiceStatus = 'edit'",$this->get_value("clientID"));
      $db->query($q);

      // Create invoice
      if (!$db->next_record()) {
        $invoice = new invoice;
        $invoice->set_value("clientID",$this->get_value("clientID"));
        $invoice->set_value("invoiceDateFrom",$this->get_min_date());
        $invoice->set_value("invoiceDateTo",$this->get_max_date());
        $invoice->set_value("invoiceNum",invoice::get_next_invoiceNum());
        $invoice->set_value("invoiceName",stripslashes($client->get_value("clientName")));
        $invoice->set_value("invoiceStatus","edit");
        $invoice->save();
        $invoiceID = $invoice->get_id();

      // Use existing invoice
      } else {
        $invoiceID = $db->f("invoiceID");
      }

      // Add invoiceItem and add expense form transactions to invoiceItem
      $invoiceItem = new invoiceItem;
      if ($_POST["split_invoice"]) {
        $invoiceItem->add_expenseFormItems($invoiceID,$this->get_id());
      } else {
        $invoiceItem->add_expenseForm($invoiceID,$this->get_id());
      }
    }
  }

  function get_min_date() {
    $db = new db_alloc();
    $q = sprintf("SELECT min(transactionDate) as date FROM transaction WHERE expenseFormID = '%s'",$this->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f('date');
  }

  function get_max_date() {
    $db = new db_alloc();
    $q = sprintf("SELECT max(transactionDate) as date FROM transaction WHERE expenseFormID = '%s'",$this->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f('date');
  }

  function get_url() {
    global $sess;
    $sess or $sess = new Session;

    $url = "finance/expenseForm.php?expenseFormID=".$this->get_id();

    if ($sess->Started()) {
      $url = $sess->url(SCRIPT_PATH.$url);

    // This for urls that are emailed
    } else {
      static $prefix;
      $prefix or $prefix = config::get_config_item("allocURL");
      $url = $prefix.$url;
    }
    return $url;
  }




}



?>
