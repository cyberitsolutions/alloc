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

function show_timeSheets($template) {

  global $db, $TPL, $invoice_item;

  $query = "SELECT * FROM timeSheet WHERE invoiceItemID = ".$invoice_item->get_id();

  $db->query($query);

  while ($db->next_record()) {
    $timeSheet = new timeSheet;
    $timeSheet->read_db_record($db);
    $timeSheet->set_tpl_values();

    $person = new person;
    $person->set_id($TPL["personID"]);
    $person->select();
    $TPL["username"] = $person->get_username();

    $timeSheet->load_pay_info();
    $TPL["total"] = $timeSheet->pay_info["total_dollars"];

    include_template($template);
  }

}


function show_transaction_list($template) {
  global $db, $TPL, $invoice_item, $status_options, $previousTransactionDate, $percent_array;

  $TPL["transaction_buttons"] = "
    <input type=\"submit\" name=\"transaction_save\" value=\"Save\">
    <input type=\"submit\" name=\"transaction_delete\" value=\"Delete\">";
  $invoiceItemID = $invoice_item->get_id();
  if ($invoiceItemID) {
    $query = sprintf("SELECT * from transaction WHERE invoiceItemID=%d", $invoiceItemID);
    $db->query($query);
    while ($db->next_record()) {
      $transaction = new transaction;
      $transaction->read_db_record($db);
      $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");
      $tf = $transaction->get_foreign_object("tf");
      $TPL["transaction_tfName"] = $tf->get_value("tfName");
      $previousTransactionDate = $transaction->get_value("transactionDate");
      $TPL["status_options"] = get_options_from_array($status_options, $transaction->get_value("status"));
      $TPL["all_approved"] = $TPL["all_approved"] && ($transaction->get_value("status") == "approved");
      $TPL["percent_dropdown"] = get_options_from_array($percent_array, $empty);

      include_template($template);
    }
  }
}

function show_new_transaction($template) {
  global $TPL, $status_options, $previousTransactionDate;
  $TPL["transaction_buttons"] = "
    <input type=\"submit\" name=\"transaction_save\" value=\"Add\">";
  $transaction = new transaction;
  $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");

  if ($previousTransactionDate) {
    $TPL["transaction_transactionDate"] = $previousTransactionDate;
  }

  $TPL["status_options"] = get_options_from_array($status_options, "pending");
  include_template($template);
}


function show_tf_options() {
  global $tf_array, $TPL;
  echo get_options_from_array($tf_array, $TPL["transaction_tfID"]);
}


function get_next_item_id($mode) {
  global $invoice_item;

  $mode == "allocate" and $status = "pending";
  $mode == "approve" and $status = "allocated";

  $db = new db_alloc;
  $query = sprintf("SELECT min(invoiceItemID) AS next_item_id 
                    FROM invoiceItem  
                    WHERE status = '%s' 
                    AND invoiceItemID > %d", $status, $invoice_item->get_id());

  $db->query($query);
  $db->next_record();
  return $db->f("next_item_id");
}




// END FUNCTIONS


/* 
   $TPL["mode"] = $mode; if ($mode == "allocate") { $invoice_status_filter = "status = 'pending'"; } else if ($mode == "approve") { $invoice_status_filter = "status = 'allocated'"; } else { $invoice_status_filter = "1=1"; } */

if ($transaction_save || $transaction_delete) {
  $transaction = new transaction;
  $transaction->read_globals();
  $transaction->read_globals("transaction_");
  if ($transaction_save) {
    $transaction->set_value("transactionType", "invoice");
    if (is_numeric($percent_dropdown)) {
      $transaction->set_value("amount", $percent_dropdown);
    }

    $transaction->save();
  } else if ($transaction_delete) {
    $transaction->delete();
  }
}


$invoiceItemID = $_POST["invoiceItemID"] or $invoiceItemID = $_GET["invoiceItemID"];

$db = new db_alloc;
$query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName 
          FROM invoiceItem, invoice 
          WHERE invoiceItem.invoiceID = invoice.invoiceID 
          AND invoiceItem.invoiceItemID=".sprintf("%d", $invoiceItemID);

$db->query($query);
$db->next_record() || die("Record not found.");

$invoice = new invoice;
$invoice->read_db_record($db);
$invoice->set_tpl_values();

$invoice_item = new invoiceItem;
$invoice_item->read_db_record($db);
$invoice_item->set_tpl_values();
$next_item_id = get_next_item_id($mode);
     $next_item_id and $TPL["next_link"] = "<a href=\"".$TPL["url_alloc_invoiceItem"]."invoiceItemID=".$next_item_id."&mode=".$mode."\">Next Invoice Item</a>&nbsp;";

if ($mark_allocated || $mark_paid && is_Object($invoice_item)) {
$mark_allocated and $invoice_item->set_value("status", "allocated");
$mark_paid and $invoice_item->set_value("status", "paid");
$invoice_item->save();
header("Location: ".$TPL["url_alloc_invoiceItem"]."invoiceItemID=".$invoice_item->get_id()."&mode=$mode");
exit;
}



$percent_array = array(""=>"Percent",
                       "A"=>"Standard",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.715)=>"71.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.665)=>"66.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.615)=>"61.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.285)=>"28.5%",
                       "B"=>"Agency Special",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.765)=>"76.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.715)=>"71.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.665)=>"66.5%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.235)=>"23.5%",
                       "C"=>"Commission/Mentoring",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.050)=>"5.0%",
                       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.025)=>"2.5%",
                       "D"=>"Other", 
		       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.5)=>"50%", 
		       sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.3575)=>"35.75%");

$TPL["722_percent"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.722);
$TPL["5_percent"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.05);
$TPL["772_percent"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount") * 0.772);
$TPL["allocated_amount"] = "0.00";
$TPL["unallocated_amount"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount"));

$TPL["next_item_id"] = $next_item_id;
$TPL["mode"] = $mode;
$TPL["all_approved"] = true;
$TPL["a_button"] = "<input type=\"submit\" name=\"approved_button\" value=\"A\">";
$TPL["p_button"] = "<input type=\"submit\" name=\"pending_button\" value=\"P\">";
$TPL["r_button"] = "<input type=\"submit\" name=\"rejected_button\" value=\"R\">";

if ($approved_button || $pending_button || $rejected_button) {
  if ($approved_button) {
    $status = "approved";
  } else if ($pending_button) {
    $status = "pending";
  } else if ($rejected_button) {
    $status = "rejected";
  }
  $db->query("update transaction set status = '".$status."' where invoiceItemID = ".$invoice_item->get_id());
  $db->next_record();
}


$db->query("SELECT * FROM tf ORDER BY tfName");
$tf_array = get_array_from_db($db, "tfID", "tfName");

$status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");


if (is_object($invoice_item) && $invoice_item->get_id()) {
  $query = sprintf("SELECT sum(amount) as allocated_amount from transaction WHERE invoiceItemID=%d", $invoice_item->get_id());
  $db = new db_alloc;
  $db->query($query);
  $db->next_record();
  $TPL["iiAmount"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount"));
  $TPL["allocated_amount"] = sprintf("%0.2f", $db->f("allocated_amount"));
  $TPL["unallocated_amount"] = sprintf("%0.2f", $invoice_item->get_value("iiAmount") - $TPL["allocated_amount"]);
#$TPL[""];
}


include_template("templates/invoiceItemM.tpl");

page_close();



?>
