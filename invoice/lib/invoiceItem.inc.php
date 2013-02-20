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

class invoiceItem extends db_entity {
  public $data_table = "invoiceItem";
  public $display_field_name = "iiMemo";
  public $key_field = "invoiceItemID";
  public $data_fields = array("invoiceID"
                             ,"timeSheetID"
                             ,"timeSheetItemID"
                             ,"expenseFormID"
                             ,"transactionID"
                             ,"productSaleID"
                             ,"productSaleItemID"
                             ,"iiMemo"
                             ,"iiQuantity"
                             ,"iiUnitPrice" => array("type"=>"money")
                             ,"iiAmount" => array("type"=>"money")
                             ,"iiTax"
                             ,"iiDate"
                             );

  function is_owner($person = "") {
    $current_user = &singleton("current_user");

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $q = prepare("SELECT * FROM transaction WHERE invoiceItemID = %d OR transactionID = %d",$this->get_id(),$this->get_value("transactionID"));
    $db->query($q);
    while ($db->next_record()) {
      $transaction = new transaction();
      $transaction->read_db_record($db);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }

    if ($this->get_value("timeSheetID")) {
      $q = prepare("SELECT * FROM timeSheet WHERE timeSheetID = %d",$this->get_value("timeSheetID"));
      $db->query($q);
      while ($db->next_record()) {
        $timeSheet = new timeSheet();
        $timeSheet->read_db_record($db);
        if ($timeSheet->is_owner($person)) {
          return true;
        }
      }
    }

    if ($this->get_value("expenseFormID")) {
      $q = prepare("SELECT * FROM expenseForm WHERE expenseFormID = %d",$this->get_value("expenseFormID"));
      $db->query($q);
      while ($db->next_record()) {
        $expenseForm = new expenseForm();
        $expenseForm->read_db_record($db);
        if ($expenseForm->is_owner($person)) {
          return true;
        }
      }
    }

    return false;
  }

  function delete() {

    $db = new db_alloc();
    $q = prepare("DELETE FROM transaction WHERE invoiceItemID = %d",$this->get_id());
    $db->query($q);

    $invoiceID = $this->get_value("invoiceID");
    $status = parent::delete();
    $status2 = invoice::update_invoice_dates($invoiceID);
    return $status && $status2;
  }

  function save() {

    if (!imp($this->get_value("iiAmount"))) {
      $this->set_value("iiAmount",$this->get_value("iiQuantity") * $this->get_value("iiUnitPrice"));
    }

    $status = parent::save();
    $status2 = invoice::update_invoice_dates($this->get_value("invoiceID"));
    return $status && $status2;
  }

  function add_timeSheet($invoiceID,$timeSheetID) {
    global $TPL;

    $invoice = $this->get_foreign_object("invoice");

    if ($invoice->get_value("invoiceStatus") != "finished") {
      $timeSheet = new timeSheet();
      $timeSheet->set_id($timeSheetID);
      $timeSheet->select();
      $timeSheet->load_pay_info();
      $project = $timeSheet->get_foreign_object("project");
      $date = $timeSheet->get_value("dateFrom") or $date = date("Y-m-d");

      // customerBilledDollars will not be set if the actual field is blank,
      // and thus there won't be a usable total_customerBilledDollars.
      if (isset($timeSheet->pay_info["customerBilledDollars"])) { 
        $amount = $timeSheet->pay_info["total_customerBilledDollars"];
        $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
        $iiQuantity = $timeSheet->pay_info["total_duration"];
      } else {
        $amount = $timeSheet->pay_info["total_dollars"];
        $iiUnitPrice = $amount; 
        $iiQuantity = 1;
      }

      $this->set_value("invoiceID",$invoiceID);
      $this->set_value("timeSheetID",$timeSheet->get_id());
      $this->set_value("iiMemo","Time Sheet #".$timeSheet->get_id()." for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName"));
      $this->set_value("iiQuantity",$iiQuantity);
      $this->set_value("iiUnitPrice",$iiUnitPrice);
      $this->set_value("iiAmount",$amount);
      $this->set_value("iiDate",$date);
      $this->set_value("iiTax",config::get_config_item("taxPercent"));
      $this->currency = $timeSheet->get_value("currencyTypeID");
      $this->save();
    } else {
      alloc_error("Unable to update related Invoice (ID:".$this->get_value("invoiceID")."), Invoice has been completed.");
    }
  }

  function add_timeSheetItems($invoiceID,$timeSheetID) {
    $timeSheet = new timeSheet();
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();
    $currency = $timeSheet->get_value("currencyTypeID");
    $timeSheet->load_pay_info();
    $amount = $timeSheet->pay_info["total_customerBilledDollars"] or $amount = $timeSheet->pay_info["total_dollars"];

    $project = $timeSheet->get_foreign_object("project");
    $client = $project->get_foreign_object("client");

    $db = new db_alloc();
    $db->query(prepare("SELECT * FROM timeSheetItem WHERE timeSheetID = %d",$timeSheetID));
    while ($row = $db->row()) {

      if (imp($timeSheet->pay_info["customerBilledDollars"])) {
        $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
      } else {
        $iiUnitPrice = page::money($currency,$row["rate"],"%mo");
      }

      unset($str);
      if ($row["comment"] && !$row["commentPrivate"]) {
        $str = $row["comment"];
      }
      $ii = new invoiceItem();
      $ii->currency = $currency;
      $ii->set_value("invoiceID",$invoiceID);
      $ii->set_value("timeSheetID",$timeSheet->get_id());
      $ii->set_value("timeSheetItemID",$row["timeSheetItemID"]);
      $ii->set_value("iiMemo","Time Sheet for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName").", ".$row["description"]."\n".$str);
      $ii->set_value("iiQuantity",$row["timeSheetItemDuration"] * $row["multiplier"]);
      $ii->set_value("iiUnitPrice",$iiUnitPrice);
      $ii->set_value("iiAmount",$iiUnitPrice * $row["timeSheetItemDuration"] * $row["multiplier"]);
      $ii->set_value("iiDate",$row["dateTimeSheetItem"]);
      $ii->set_value("iiTax",config::get_config_item("taxPercent"));
      $ii->save();
    }
  }

  function add_expenseForm($invoiceID,$expenseFormID) {
    $expenseForm = new expenseForm();
    $expenseForm->set_id($expenseFormID);
    $expenseForm->select();
    $db = new db_alloc();
    $db->query("SELECT max(transactionDate) as maxDate
                  FROM transaction
                 WHERE expenseFormID = %d",$expenseFormID);
    $row = $db->row();
    $amount = $expenseForm->get_abs_sum_transactions();
    $this->set_value("invoiceID",$invoiceID);
    $this->set_value("expenseFormID",$expenseForm->get_id());
    $this->set_value("iiMemo","Expense Form #".$expenseForm->get_id()." for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")));
    $this->set_value("iiQuantity",1);
    $this->set_value("iiUnitPrice",$amount);
    $this->set_value("iiAmount",$amount);
    $this->set_value("iiDate",$row["maxDate"]);
    $this->set_value("iiTax",config::get_config_item("taxPercent"));
    $this->save();
  }

  function add_expenseFormItems($invoiceID,$expenseFormID) {
    $expenseForm = new expenseForm();
    $expenseForm->set_id($expenseFormID);
    $expenseForm->select();
    $db = new db_alloc();
    $db->query("SELECT * FROM transaction WHERE expenseFormID = %d",$expenseFormID);
    while ($row = $db->row()) {
      $amount = abs($row["amount"]);
      $ii = new invoiceItem();
      $ii->set_value("invoiceID",$invoiceID);
      $ii->set_value("expenseFormID",$expenseForm->get_id());
      $ii->set_value("transactionID",$row["transactionID"]);
      $ii->set_value("iiMemo","Expenses for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")).", ".$row["product"]);
      $ii->set_value("iiQuantity",$row["quantity"]);
      $ii->set_value("iiUnitPrice",$amount);
      $ii->set_value("iiAmount",$amount*$row["quantity"]);
      $ii->set_value("iiDate",$row["transactionDate"]);
      $ii->set_value("iiTax",config::get_config_item("taxPercent"));
      $ii->save();
    }
  }

  function add_productSale($invoiceID,$productSaleID) {
    $productSale = new productSale();
    $productSale->set_id($productSaleID);
    $productSale->select();
    $db = new db_alloc();
    $db->query("SELECT max(transactionDate) as maxDate
                  FROM transaction
                 WHERE productSaleID = %d",$productSaleID);
    $row = $db->row();
    $amounts = $productSale->get_amounts();
    $this->set_value("invoiceID",$invoiceID);
    $this->set_value("productSaleID",$productSale->get_id());
    $this->set_value("iiMemo","Sale #".$productSale->get_id()." for ".person::get_fullname($productSale->get_value("personID")));
    $this->set_value("iiQuantity",1);
    $this->set_value("iiUnitPrice",$amounts["total_sellPrice_value"]);
    $this->set_value("iiAmount",$amounts["total_sellPrice_value"]);
    $this->set_value("iiDate",$row["maxDate"]);
    //$this->set_value("iiTax",config::get_config_item("taxPercent"));
    $this->save();
  }

  function add_productSaleItems($invoiceID,$productSaleID) {
    $productSale = new productSale();
    $productSale->set_id($productSaleID);
    $productSale->select();
    $db = new db_alloc();
    $q = prepare("SELECT * FROM productSaleItem WHERE productSaleID = %d",$productSale->get_id());
    $db->query($q);
    while ($row = $db->row()) {
      $ii = new invoiceItem();
      $ii->currency = $row["sellPriceCurrencyTypeID"];
      $ii->set_value("invoiceID",$invoiceID);
      $ii->set_value("productSaleID",$productSale->get_id());
      $ii->set_value("productSaleItemID",$row["productSaleItemID"]);
      $ii->set_value("iiMemo","Sale (".$productSale->get_id().") item for ".person::get_fullname($productSale->get_value("personID")).", ".$row["description"]);
      $ii->set_value("iiQuantity",$row["quantity"]);
      $row["sellPrice"] = page::money($ii->currency,$row["sellPrice"]/$row["quantity"],"%mo");
      $ii->set_value("iiUnitPrice",$row["sellPrice"]);
      $ii->set_value("iiAmount",$row["sellPrice"]*$row["quantity"]);
      $d = $productSale->get_value("productSaleDate") or $d = $productSale->get_value("productSaleModifiedTime") or $d = $productSale->get_value("productSaleCreatedTime");
      $ii->set_value("iiDate",$d);
      //$ii->set_value("iiTax",config::get_config_item("taxPercent")); // product sale items are always excl GST
      $ii->save();
    }
  }

  function close_related_entity() {
    global $TPL;

    // It checks for approved transactions and only approves the timesheets
    // or expenseforms that are completely paid for by an invoice item.
    $db = new db_alloc();
    $q = prepare("SELECT amount, currencyTypeID, status 
                    FROM transaction 
                   WHERE invoiceItemID = %d 
                ORDER BY transactionCreatedTime DESC 
                   LIMIT 1
                 ",$this->get_id());
    $db->query($q);
    $row = $db->row();
    $total = $row["amount"];
    $currency = $row["currencyTypeID"];
    $status = $row["status"];

    $timeSheetID = $this->get_value("timeSheetID");
    $expenseFormID = $this->get_value("expenseFormID");

    if ($timeSheetID) {
      $timeSheet = new timeSheet();
      $timeSheet->set_id($timeSheetID);
      $timeSheet->select();
      
      $db = new db_alloc();

      if ($timeSheet->get_value("status") == "invoiced") {

        // If the time sheet doesn't have any transactions and it is in
        // status invoiced, then we'll simulate the "Create Default Transactions"
        // button being pressed.
        $q = prepare("SELECT count(*) as num_transactions 
                        FROM transaction 
                       WHERE timeSheetID = %d 
                         AND invoiceItemID IS NULL
                     ",$timeSheet->get_id());
        $db->query($q);
        $row = $db->row();
        if ($row["num_transactions"]==0) {
          $_POST["create_transactions_default"] = true;
          $timeSheet->createTransactions($status);
          $TPL["message_good"][] = "Automatically created time sheet transactions.";
        }

        // Get total of all time sheet transactions.
        $q = prepare("SELECT SUM(amount) AS total 
                        FROM transaction 
                       WHERE timeSheetID = %d 
                         AND status != 'rejected' 
                         AND invoiceItemID IS NULL
                     ",$timeSheet->get_id());
        $db->query($q);
        $row = $db->row();
        $total_timeSheet = $row["total"];

        if ($total >= $total_timeSheet) {
          $timeSheet->pending_transactions_to_approved();
          $timeSheet->change_status("forwards");
          $TPL["message_good"][] = "Closed Time Sheet #".$timeSheet->get_id()." and marked its Transactions: ".$status;
        } else {
          $TPL["message_help"][] = "Unable to close Time Sheet #".$timeSheet->get_id()." the sum of the Time Sheet's *Transactions* ("
                                   .page::money($timeSheet->get_value("currencyTypeID"),$total_timeSheet,"%s%mo %c")
                                   .") is greater than the Invoice Item Transaction ("
                                   .page::money($currency,$total,"%s%mo %c").")";
        }
      }
    

    } else if ($expenseFormID) {
      $expenseForm = new expenseForm();
      $expenseForm->set_id($expenseFormID);
      $expenseForm->select();
      $total_expenseForm = $expenseForm->get_abs_sum_transactions();
      
      if ($total == $total_expenseForm) {
        $expenseForm->set_status("approved");
        $TPL["message_good"][] = "Approved Expense Form #".$expenseForm->get_id().".";
      } else {
        $TPL["message_help"][] = "Unable to approve Expense Form #".$expenseForm->get_id()." the sum of Expense Form Transactions does not equal the Invoice Item Transaction.";
      }
    }

  }

  function create_transaction($amount,$tfID,$status) {
    $transaction = new transaction();
    $invoice = $this->get_foreign_object("invoice");
    $this->currency = $invoice->get_value("currencyTypeID");
    $db = new db_alloc();

    // If there already a transaction for this invoiceItem, use it instead of creating a new one
    $q = prepare("SELECT * FROM transaction WHERE invoiceItemID = %d ORDER BY transactionCreatedTime DESC LIMIT 1",$this->get_id());
    $db->query($q);
    if ($db->row()) {
      $transaction->set_id($db->f("transactionID"));
      $transaction->select();
    }

    // If there already a transaction for this timeSheet, use it instead of creating a new one
    if ($this->get_value("timeSheetID")) {
      $q = prepare("SELECT * 
                      FROM transaction 
                     WHERE timeSheetID = %d 
                       AND fromTfID = %d
                       AND tfID = %d
                       AND amount = %d
                  ORDER BY transactionCreatedTime DESC LIMIT 1
                         ",$this->get_value("timeSheetID"),config::get_config_item("inTfID")
                          ,$tfID,page::money($this->currency,$amount,"%mi"));
      $db->query($q);
      if ($db->row()) {
        $transaction->set_id($db->f("transactionID"));
        $transaction->select();
      }
    }

    $transaction->set_value("amount",$amount);  
    $transaction->set_value("currencyTypeID",$this->currency);  
    $transaction->set_value("fromTfID",config::get_config_item("inTfID")); 
    $transaction->set_value("tfID",$tfID);
    $transaction->set_value("status",$status);
    $transaction->set_value("invoiceID",$this->get_value("invoiceID"));
    $transaction->set_value("invoiceItemID",$this->get_id());
    $transaction->set_value("transactionDate",$this->get_value("iiDate"));
    $transaction->set_value("transactionType","invoice");
    $transaction->set_value("product",sprintf("%s",$this->get_value("iiMemo")));
    $this->get_value("timeSheetID") && $transaction->set_value("timeSheetID",$this->get_value("timeSheetID"));
    $transaction->save();
  }

  function get_list_filter($filter=array()) {
    // Filter on invoiceID
    if ($filter["invoiceID"] && is_array($filter["invoiceID"])) {
      $sql[] = prepare("(invoice.invoiceID in (%s))",$filter["invoiceID"]);
    } else if ($filter["invoiceID"]) {     
      $sql[] = prepare("(invoice.invoiceID = %d)", $filter["invoiceID"]);
    }
    return $sql;
  }

  function get_list($_FORM) {
    $filter = invoiceItem::get_list_filter($_FORM);
    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }
    $q = prepare("SELECT * FROM invoiceItem 
               LEFT JOIN invoice ON invoice.invoiceID = invoiceItem.invoiceID
               LEFT JOIN client ON client.clientID = invoice.clientID
               ".$f);
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $row["iiAmount"] = page::money($row["currencyTypeID"],$row["iiAmount"],"%mo");
      $row["iiUnitPrice"] = page::money($row["currencyTypeID"],$row["iiUnitPrice"],"%mo");
      $rows[$row["invoiceItemID"]] = $row;
    }
    return (array)$rows;
  }


}








?>
