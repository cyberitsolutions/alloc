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

class expenseForm extends db_entity {
  public $data_table = "expenseForm";
  public $key_field = "expenseFormID";
  public $data_fields = array("expenseFormModifiedUser"
                             ,"expenseFormModifiedTime"
                             ,"paymentMethod"
                             ,"reimbursementRequired"=>array("empty_to_null"=>false)
                             ,"seekClientReimbursement"=>array("empty_to_null"=>false)
                             ,"transactionRepeatID"
                             ,"clientID"
                             ,"expenseFormCreatedUser"
                             ,"expenseFormCreatedTime"
                             ,"expenseFormFinalised"=>array("empty_to_null"=>false)
                             ,"expenseFormComment"
                             );

  function is_owner($person = "") {
    $current_user = &singleton("current_user");

    if ($person == "") {
      $person = $current_user;
    }
    // Return true if this user created the expense form
    if ($person->get_id() == $this->get_value("expenseFormCreatedUser", DST_VARIABLE)) {
      return true;
    }

    if ($this->get_id()) {
      // Return true if any of the transactions on the expense form are accessible by the current user
      $current_user_tfIDs = $current_user->get_tfIDs();
      $query = prepare("SELECT * FROM transaction WHERE expenseFormID=%d",$this->get_id());
      $db = new db_alloc();
      $db->query($query);
      while ($db->next_record()) {
        if (is_array($current_user_tfIDs) && (in_array($db->f("tfID"),$current_user_tfIDs) || in_array($db->f("fromTfID"),$current_user_tfIDs))) {
          return true;
        }
      }

    // If no expenseForm ID, then it hasn't been created yet...
    } else {
      return true;
    }

    if ($current_user->have_role("admin")) {
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

  function set_status($status) {
    // This sets the status of the expense form. Actually, the expense form
    // doesn't have its own status - this sets the status of the transactions on the
    // expense form
    $current_user = &singleton("current_user");
    $transactions = $this->get_foreign_objects("transaction");
    while (list(, $transaction) = each($transactions)) {
      $transaction->set_value("status", $status);
      $transaction->save();
    }
  }

  function get_status() {
    $q = prepare("SELECT status FROM transaction WHERE expenseFormID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $arr[$row["status"]] = 1;
    }
    $arr or $arr = array();
    foreach ($arr as $s => $v) {
      $return .= $sp.$s;
      $sp = "&nbsp;&amp;&nbsp;";
    }
    return $return;
  }

  function delete_transactions($transactionID="") {
    global $TPL;

    $transactionID and $extra_sql = prepare("AND transactionID = %d",$transactionID);

    $db = new db_alloc();
    if ($this->is_owner()) {
      $db->query(prepare("DELETE FROM transaction WHERE expenseFormID = %d ".$extra_sql,$this->get_id()));
      $transactionID and $TPL["message_good"][] = "Expense Form Line Item deleted.";
    }
  }

  function get_invoice_link() {
    global $TPL;
    $db = new db_alloc();
    if ($this->get_id()) {
      $db->query("SELECT invoice.* FROM invoiceItem LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID WHERE expenseFormID = %d",$this->get_id());
      while ($row = $db->next_record()) {
        $str.= $sp."<a href=\"".$TPL["url_alloc_invoice"]."invoiceID=".$row["invoiceID"]."\">".$row["invoiceNum"]."</a>";
        $sp = "&nbsp;&nbsp;";
      }
      return $str;
    }
  }

  function save_to_invoice($invoiceID=false) {

    if ($this->get_value("clientID")) {
      $invoiceID and $extra = prepare(" AND invoiceID = %d",$invoiceID);
      $client = $this->get_foreign_object("client");
      $db = new db_alloc();
      $q = prepare("SELECT * FROM invoice WHERE clientID = %d AND invoiceStatus = 'edit' ".$extra,$this->get_value("clientID"));
      $db->query($q);

      // Create invoice
      if (!$db->next_record()) {
        $invoice = new invoice();
        $invoice->set_value("clientID",$this->get_value("clientID"));
        $invoice->set_value("invoiceDateFrom",$this->get_min_date());
        $invoice->set_value("invoiceDateTo",$this->get_max_date());
        $invoice->set_value("invoiceNum",invoice::get_next_invoiceNum());
        $invoice->set_value("invoiceName",$client->get_value("clientName"));
        $invoice->set_value("invoiceStatus","edit");
        $invoice->save();
        $invoiceID = $invoice->get_id();

      // Use existing invoice
      } else {
        $invoiceID = $db->f("invoiceID");
      }

      // Add invoiceItem and add expense form transactions to invoiceItem
      $invoiceItem = new invoiceItem();
      if ($_POST["split_invoice"]) {
        $invoiceItem->add_expenseFormItems($invoiceID,$this->get_id());
      } else {
        $invoiceItem->add_expenseForm($invoiceID,$this->get_id());
      }
    }
  }

  function get_min_date() {
    $db = new db_alloc();
    $q = prepare("SELECT min(transactionDate) as date FROM transaction WHERE expenseFormID = %d",$this->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f('date');
  }

  function get_max_date() {
    $db = new db_alloc();
    $q = prepare("SELECT max(transactionDate) as date FROM transaction WHERE expenseFormID = %d",$this->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f('date');
  }

  function get_url() {
    global $sess;
    $sess or $sess = new session();

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

  function get_abs_sum_transactions($id=false) {
    if (is_object($this)) {
      $id = $this->get_id();
    }
    $db = new db_alloc();
    $q = prepare("SELECT sum(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS amount
                    FROM transaction 
               LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                   WHERE expenseFormID = %d",$id);
    $db->query($q);
    $row = $db->row();
    return $row["amount"];
  } 

  function get_list_filter($filter=array()) {
    $filter["projectID"] and $sql[] = prepare("transaction.projectID = %d",$filter["projectID"]);
    $filter["status"]    and $sql[] = prepare("transaction.status = '%s'",$filter["status"]);
    isset($filter["finalised"]) and $sql[] = prepare("expenseForm.expenseFormFinalised = %d",$filter["finalised"]);
    return $sql;
  }

  function get_list($_FORM=array()) {
    global $TPL;
    $filter = expenseForm::get_list_filter($_FORM);
    if (is_array($filter) && count($filter)) {
      $f = " AND ".implode(" AND ",$filter);
    }

    $db = new db_alloc();
    $dbTwo = new db_alloc();
    $transDB = new db_alloc();
    $expenseForm = new expenseForm();
    $transaction = new transaction();
    $rr_options = expenseForm::get_reimbursementRequired_array();

    $q = prepare("SELECT expenseForm.*
                        ,SUM(transaction.amount * pow(10,-currencyType.numberToBasic)) as formTotal
                        ,transaction.currencyTypeID
                    FROM expenseForm, transaction
               LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                   WHERE expenseForm.expenseFormID = transaction.expenseFormID
                         ".$f."
                GROUP BY expenseForm.expenseFormID, transaction.currencyTypeID
                ORDER BY expenseFormID");

    $db->query($q);

    while ($row = $db->row()) {
      $amounts[$row["expenseFormID"]].= $sp[$row["expenseFormID"]].page::money($row["currencyTypeID"],$row["formTotal"],"%s%m");
      $sp[$row["expenseFormID"]] = " + ";
      $allrows[$row["expenseFormID"]] = $row;
    }
    foreach ((array)$allrows as $expenseFormID => $row) {
      $expenseForm = new expenseForm();
      if ($expenseForm->read_row_record($row)) {
        $expenseForm->set_values();
        $row["formTotal"] = $amounts[$expenseFormID];
        $row["expenseFormModifiedUser"] = person::get_fullname($expenseForm->get_value("expenseFormModifiedUser"));
        $row["expenseFormModifiedTime"] = $expenseForm->get_value("expenseFormModifiedTime");
        $row["expenseFormCreatedUser"] = person::get_fullname($expenseForm->get_value("expenseFormCreatedUser"));
        $row["expenseFormCreatedTime"] = $expenseForm->get_value("expenseFormCreatedTime");
        unset($extra);
        $expenseForm->get_value("paymentMethod") and $extra = " (".$expenseForm->get_value("paymentMethod").")";
        $row["rr_label"] = $rr_options[$expenseForm->get_value("reimbursementRequired")].$extra;
        $rows[] = $row;
      }
    }
    return (array)$rows;
  }

  function get_pending_repeat_transaction_list() {
    global $TPL;
    $transactionTypes = transaction::get_transactionTypes();
    $q = "SELECT * FROM transaction 
              LEFT JOIN transactionRepeat on transactionRepeat.transactionRepeatID = transaction.transactionRepeatID 
                  WHERE transaction.transactionRepeatID IS NOT NULL AND transaction.status = 'pending'";
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $transaction = new transaction();
      $transaction->read_db_record($db);
      $transaction->set_values();
      $transactionRepeat = new transactionRepeat();
      $transactionRepeat->read_db_record($db);
      $transactionRepeat->set_values();
      $row["transactionType"] = $transactionTypes[$transaction->get_value("transactionType")];
      $row["formTotal"] =  $db->f("amount");
      $row["transactionModifiedTime"] = $transaction->get_value("transactionModifiedTime");
      $row["transactionCreatedTime"] = $transaction->get_value("transactionCreatedTime");
      $row["transactionCreatedUser"] = person::get_fullname($transaction->get_value("transactionCreatedUser"));
      $rows[] = $row;
    }
    return (array)$rows;
  }

}



?>
