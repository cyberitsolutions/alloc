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

require_once("../alloc.php");

$field_map = array(""=>0, "type"=>1, "date"=>2, "num"=>3, "name"=>4, "memo"=>5, "quantity"=>6, "sales_price"=>7, "amount"=>8, ""=>9,);

if ($_POST["upload"]) {
  $db = new db_alloc;
  is_uploaded_file($_FILES["invoices_file"]["tmp_name"]) || die("File referred to was not an uploaded file"); // Prevent attacks by setting $invoices_file in URL
  $lines = file($_FILES["invoices_file"]["tmp_name"]);

  reset($lines);
  while (list(, $line) = each($lines)) {
    // Read field values from the line
    $fields = explode("\t", $line);
    $type = trim($fields[$field_map["type"]]);
    $date = trim($fields[$field_map["date"]]);
    $num = trim($fields[$field_map["num"]]);
    $name = trim($fields[$field_map["name"]]);
    $memo = trim($fields[$field_map["memo"]]);
    $quantity = trim($fields[$field_map["quantity"]]);
    $sales_price = trim($fields[$field_map["sales_price"]]);
    $amount = trim($fields[$field_map["amount"]]);


    // Newer versions of Quick Books use different labels
    $type == "Tax Invoice" and $type = "Invoice";
    $type == "Adjustment Note" and $type = "Credit Memo";


    // Lline number
    $line_number++;
    $msg[] = "<br><b>Line: ".$line_number."</b>";

    // Ignore heading row and total rows
    if ($type == "Type" || $type == "") {
      continue;

      // If not enough fields
    } else if (count($fields) < 5) {
      $msg[] = "Skipping Row: Not enough fields in row.";
      continue;
    }
    // It was skipping "credit memo" transactions
    if (strtolower($type) == "credit memo") {
      $type = "Invoice";
    }
    // More coercion
    if ($quantity == 0) {
      $quantity = 1;
    }
    // Show a warning and skip rows that have $type != invoice
    if ($type != "Invoice") {
      $msg[] = "Skipping Row: type $type (invoice number=$num, memo=$memo).";
      continue;
    }
    // Convert the date to yyyy-mm-dd
    if (!eregi("^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})$", $date, $matches)) {
      $msg[] = "Skipping Row: Could not convert date '$date'.";
      continue;
    }
    $date = sprintf("%04d-%02d-%02d", $matches[3], $matches[2], $matches[1]);


    // Strip $ and , from amount
    $amount = ereg_replace("[\$,]", "", $amount);
    if (!ereg("^-?[0-9]+(\\.[0-9]+)?$", $amount)) {
      $msg[] = "Skipping Row: Could not convert amount '$amount'.";
      continue;
    }
    // If invoice record doesn't already exist create an invoice object and save it
    $query = sprintf("SELECT * FROM invoice WHERE invoiceNum=%d", $num);
    $db->query($query);
    if ($db->next_record()) {
      $invoiceID = $db->f("invoiceID");
    } else {
      $invoice = new invoice;
      $invoice->set_value("invoiceDateFrom", $date);
      $invoice->set_value("invoiceDateTo", $date);
      $invoice->set_value("invoiceNum", $num);
      $invoice->set_value("invoiceName", $name);
      $invoice->set_value("invoiceStatus", "reconcile");
      list($clientID,$percent) = get_clientID_from_name($name);
      if ($clientID && $percent > 75) {
        $c = new client;
        $c->set_id($clientID);
        $c->select();
        $msg[] = "Found client: '".$c->get_value("clientName")."' for uploaded client name: '".$name."'";
        $invoice->set_value("clientID", $clientID);

      } else {
        $msg[] = "Skipping Row: Client: '".$name."' &lt;-- Couldn't find a matching Client";
        continue;

      }
      $invoice->save();
      $invoiceID = $invoice->get_id();
      $msg[] = "Invoice $num saved";
    }

    // Check for an existing invoice item
    $query = sprintf("SELECT invoiceItemID
                        FROM invoiceItem
                        WHERE invoiceID=%d AND iiMemo='%s'
						AND iiAmount=%f AND iiDate='%s'", $invoiceID, addslashes(mysql_escape_string($memo)), $amount, $date);

    #$msg[] = $query;
    $db->query($query);

    if ($db->next_record()) {
      $msg[] = "Skipping Row: Invoice item '$memo' on invoice number $num already exixsts.";
      continue;
    }
    // Create a invoice_item object and then save it
    $invoice_item = new invoiceItem;
    $invoice_item->set_value("invoiceID", $invoiceID);
    $invoice_item->set_value("iiMemo", addslashes($memo));
    $invoice_item->set_value("iiQuantity", $quantity);
    $invoice_item->set_value("iiUnitPrice", $sales_price);
    $invoice_item->set_value("iiAmount", $amount);
    $invoice_item->set_value("iiDate", $date);
    $invoice_item->save();

    $config = new config;
    #$transactionAmount = $amount * .285;
    #$transactionAmount = $amount / 1.1;
    $transactionAmount = $amount;

    $transactionNew = new transaction;
    $transactionNew->set_value("status", "pending");
    $transactionNew->set_value("amount", sprintf("%f", $transactionAmount));
    $transactionNew->set_value("quantity", $quantity);
    $transactionNew->set_value("invoiceItemID", $invoice_item->get_id());
    $transactionNew->set_value("transactionDate", $date);
    $transactionNew->set_value("product", $memo);
    $transactionNew->set_value("companyDetails", $invoiceName);
    $transactionNew->set_value("tfID", $config->get_config_item("cybersourceTfID"));
    $transactionNew->set_value("transactionType", "invoice");
    $transactionNew->save();

    $msg[] = "Invoice item '$memo' on invoice number $num saved.";
  }
  $TPL["msg"] = implode("<br>", $msg);
}
$TPL["main_alloc_title"] = "Upload Invoices - ".APPLICATION_NAME;
include_template("templates/invoicesUploadM.tpl");

page_close();



?>
