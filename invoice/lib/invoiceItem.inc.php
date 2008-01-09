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

define("PERM_FINANCE_UPDATE_APPROVED", 256);

class invoiceItem extends db_entity
{
  var $data_table = "invoiceItem";
  var $display_field_name = "iiMemo";

  function invoiceItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("invoiceItemID");
    $this->data_fields = array("invoiceID"=>new db_field("invoiceID")
                               , "timeSheetID"=>new db_field("timeSheetID")
                               , "timeSheetItemID"=>new db_field("timeSheetItemID")
                               , "expenseFormID"=>new db_field("expenseFormID")
                               , "transactionID"=>new db_field("transactionID")
                               , "iiMemo"=>new db_field("iiMemo")
                               , "iiQuantity"=>new db_field("iiQuantity")
                               , "iiUnitPrice"=>new db_field("iiUnitPrice")
                               , "iiAmount"=>new db_field("iiAmount")
                               , "iiDate"=>new db_field("iiDate")
      );
    $this->permissions[PERM_FINANCE_UPDATE_APPROVED] = "Update approved transactions";
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $q = sprintf("SELECT * FROM transaction WHERE invoiceItemID = %d OR transactionID = %d",$this->get_id(),$this->get_value("transactionID"));
    $db->query($q);
    while ($db->next_record()) {
      $transaction = new transaction();
      $transaction->read_db_record($db, false);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }

    if ($this->get_value("timeSheetID")) {
      $q = sprintf("SELECT * FROM timeSheet WHERE timeSheetID = %d",$this->get_value("timeSheetID"));
      $db->query($q);
      while ($db->next_record()) {
        $timeSheet = new timeSheet();
        $timeSheet->read_db_record($db, false);
        if ($timeSheet->is_owner($person)) {
          return true;
        }
      }
    }

    if ($this->get_value("expenseFormID")) {
      $q = sprintf("SELECT * FROM expenseForm WHERE expenseFormID = %d",$this->get_value("expenseFormID"));
      $db->query($q);
      while ($db->next_record()) {
        $expenseForm = new expenseForm();
        $expenseForm->read_db_record($db, false);
        if ($expenseForm->is_owner($person)) {
          return true;
        }
      }
    }

    return false;
  }

  function delete() {

    $db = new db_alloc();
    $q = sprintf("DELETE FROM transaction WHERE invoiceItemID = %d",$this->get_id());
    $db->query($q);

    $invoiceID = $this->get_value("invoiceID");
    $status = parent::delete();
    $status2 = invoice::update_invoice_dates($invoiceID);
    return $status && $status2;
  }

  function save() {

    if (!$this->get_value("iiAmount")) {
      $this->set_value("iiAmount",$this->get_value("iiQuantity") * $this->get_value("iiUnitPrice"));
    }

    $status = parent::save();
    $status2 = invoice::update_invoice_dates($this->get_value("invoiceID"));
    return $status && $status2;
  }

  function add_timeSheet($invoiceID,$timeSheetID) {
    $timeSheet = new timeSheet;
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();
    $timeSheet->load_pay_info();
    $amount = $timeSheet->pay_info["total_customerBilledDollars"] or $amount = $timeSheet->pay_info["total_dollars"];

    $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
    $iiUnitPrice >0 or $iiUnitPrice = $timeSheet->pay_info["timeSheetItem_rate"];

    $iiQuantity = $timeSheet->pay_info["total_duration"] or $iiQuantity = "0";
    $project = $timeSheet->get_foreign_object("project");

    $this->set_value("invoiceID",$invoiceID);
    $this->set_value("timeSheetID",$timeSheet->get_id());
    $this->set_value("iiMemo","Time Sheet #".$timeSheet->get_id()." for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName"));
    $this->set_value("iiQuantity",$iiQuantity);
    $this->set_value("iiUnitPrice",$iiUnitPrice);
    $this->set_value("iiAmount",$amount);
    $this->set_value("iiDate",$timeSheet->get_value("dateFrom"));
    $this->save();
  }

  function add_timeSheetItems($invoiceID,$timeSheetID) {
    $timeSheet = new timeSheet;
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();
    $timeSheet->load_pay_info();
    $amount = $timeSheet->pay_info["total_customerBilledDollars"] or $amount = $timeSheet->pay_info["total_dollars"];

    $project = $timeSheet->get_foreign_object("project");
    $client = $project->get_foreign_object("client");

    $db = new db_alloc();
    $db->query(sprintf("SELECT * FROM timeSheetItem WHERE timeSheetID = %d",$timeSheetID));
    while ($row = $db->row()) {
      $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
      $iiUnitPrice >0 or $iiUnitPrice = $row["rate"];
      unset($str);
      if ($row["comment"] && !$row["commentPrivate"]) {
        $str = $row["comment"];
      }
      $ii = new invoiceItem;
      $ii->set_value("invoiceID",$invoiceID);
      $ii->set_value("timeSheetID",$timeSheet->get_id());
      $ii->set_value("timeSheetItemID",$row["timeSheetItemID"]);
      $ii->set_value("iiMemo","Time Sheet for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName").", ".$row["description"]."\n".$str);
      $ii->set_value("iiQuantity",$row["timeSheetItemDuration"]);
      $ii->set_value("iiUnitPrice",$iiUnitPrice);
      $ii->set_value("iiAmount",$iiUnitPrice*$row["timeSheetItemDuration"]);
      $ii->set_value("iiDate",$row["dateTimeSheetItem"]);
      $ii->save();
    }
  }

  function add_expenseForm($invoiceID,$expenseFormID) {
    $expenseForm = new expenseForm;
    $expenseForm->set_id($expenseFormID);
    $expenseForm->select();
    $db = new db_alloc();
    $db->query("SELECT sum(amount) as sum_amount, max(transactionDate) as maxDate
                  FROM transaction WHERE expenseFormID = %s",$expenseFormID);
    $row = $db->row();
    $amount = abs($row["sum_amount"]);

    $this->set_value("invoiceID",$invoiceID);
    $this->set_value("expenseFormID",$expenseForm->get_id());
    $this->set_value("iiMemo","Expenses for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")));
    $this->set_value("iiQuantity",1);
    $this->set_value("iiUnitPrice",$amount);
    $this->set_value("iiAmount",$amount);
    $this->set_value("iiDate",$row["maxDate"]);
    $this->save();
  }

  function add_expenseFormItems($invoiceID,$expenseFormID) {
    $expenseForm = new expenseForm;
    $expenseForm->set_id($expenseFormID);
    $expenseForm->select();
    $db = new db_alloc();
    $db->query(sprintf("SELECT * FROM transaction WHERE expenseFormID = %d",$expenseFormID));
    while ($row = $db->row()) {
      $amount = abs($row["amount"]);
      $ii = new invoiceItem;
      $ii->set_value("invoiceID",$invoiceID);
      $ii->set_value("expenseFormID",$expenseForm->get_id());
      $ii->set_value("transactionID",$row["transactionID"]);
      $ii->set_value("iiMemo","Expenses for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")).", ".$row["product"]);
      $ii->set_value("iiQuantity",$row["quantity"]);
      $ii->set_value("iiUnitPrice",$amount);
      $ii->set_value("iiAmount",$amount*$row["quantity"]);
      $ii->set_value("iiDate",$row["transactionDate"]);
      $ii->save();
    }
  }

  function close_related_entity() {
  

    // Really should do this properly so that it checks for APPROVED transactions and 
    // only approves the timesheets or expenseforms that are completely paid for by an 
    // invoice item.
    #$db = new db_alloc();
    #$q = sprintf("SELECT * FROM transaction WHERE invoiceItemID = %d",$this->get_id());
    #$db->query($q);

    if ($this->get_value("timeSheetItemID")) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->set_id($this->get_value("timeSheetItemID"));
      $timeSheetItem->select();
      $timeSheetID = $timeSheetItem->get_value("timeSheetID");
    }

    $timeSheetID or $timeSheetID = $this->get_value("timeSheetID");

    if ($timeSheetID) {
      $timeSheet = new timeSheet;
      $timeSheet->set_id($timeSheetID);
      $timeSheet->select();
      if ($timeSheet->get_value("status") == "invoiced") {
        $timeSheet->pending_transactions_to_approved();
        $timeSheet->change_status("forwards");
      }
    

    } else if ($expenseFormID) {
      $expenseForm = new expenseForm;
      $expenseForm->set_id($expenseFormID);
      $expenseForm->select();
      $expenseForm->set_status("approved");
    }

  }





}








?>
