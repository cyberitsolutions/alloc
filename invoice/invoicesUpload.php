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

$field_map = array(""=>0, "type"=>1, "date"=>2, "num"=>3, "name"=>4, "memo"=>5, "quantity"=>6, "sales_price"=>7, "amount"=>8, ""=>9,);

if ($_POST["upload"]) {
  $db = new db_alloc;
  is_uploaded_file($_FILES["invoices_file"]["tmp_name"]) || die("File referred to was not an uploaded file"); // Prevent attacks by setting $invoices_file in URL
  $lines = file($_FILES["invoices_file"]["tmp_name"]);

  reset($lines);
  while (list(, $line) = each($lines)) {
    // Read field values from the line
    if (preg_match("/\t/",$line)) { 
      $fields = explode("\t", $line);
    } else if (preg_match("/,/",$line)) { 
      $fields = explode(",", $line);
    } 

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

    // Line number
    $line_number++;
    $msg[] = "<hr><b>Line ".$line_number.":</b> <pre style=\"display:inline\">".$line."</pre>";


    // If not enough fields
    if (count($fields) < 5 || ($type == "Type" || $type == "" || !$date)) {
      $msg[] = "Skipping Row.";
      continue;
    }  

    // It was skipping "credit memo" transactions
    strtolower($type) == "credit memo" and $type = "Invoice";

    // More coercion
    $quantity == 0 and $quantity = 1;

    // Show a warning and skip rows that have $type != invoice
    if ($type != "Invoice") {
      $msg[] = "<b class=\"transaction-rejected\">Skipping Row: Bad type: $type (invoice number=$num, memo=$memo).</b>";
      continue;
    }
    // Convert the date to yyyy-mm-dd
    if (!eregi("^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})$", $date, $matches)) {
      $msg[] = "<b class=\"transaction-rejected\">Skipping Row: Could not convert date '$date'.</b>";
      continue;
    }
    $date = sprintf("%04d-%02d-%02d", $matches[3], $matches[2], $matches[1]);


    // Strip $ and , from amount
    $amount = ereg_replace("[\$,]", "", $amount);
    if (!ereg("^-?[0-9]+(\\.[0-9]+)?$", $amount)) {
      $msg[] = "<b class=\"transaction-rejected\">Skipping Row: Could not convert amount '$amount'.</b>";
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
        $msg[] = "<b class=\"transaction-rejected\">Skipping Row: Client: '".$name."' &lt;-- Couldn't find a matching Client</b>";
        continue;

      }
      $invoice->save();
      $invoiceID = $invoice->get_id();
      $msg[] = "<b class=\"transaction-approved\">Invoice $num saved</b>";
    }

    // Check for an existing invoice item
    $query = sprintf("SELECT invoiceItemID
                        FROM invoiceItem
                        WHERE invoiceID=%d AND iiMemo='%s'
						AND iiAmount=%f AND iiDate='%s'", $invoiceID, db_esc($memo), $amount, $date);

    #$msg[] = $query;
    $db->query($query);

    if ($db->next_record()) {
      $msg[] = "<b>Skipping Row: Invoice item '$memo' on invoice number $num already exixsts.</b>";
      continue;
    }
    // Create a invoice_item object and then save it
    $invoice_item = new invoiceItem;
    $invoice_item->set_value("invoiceID", $invoiceID);
    $invoice_item->set_value("iiMemo", $memo);
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
    $transactionNew->set_value("invoiceID", $invoice_item->get_value("invoiceID"));
    $transactionNew->set_value("invoiceItemID", $invoice_item->get_id());
    $transactionNew->set_value("transactionDate", $date);
    $transactionNew->set_value("product", $memo);
    $transactionNew->set_value("companyDetails", $invoiceName);
    $transactionNew->set_value("tfID", $config->get_config_item("cybersourceTfID"));
    $transactionNew->set_value("transactionType", "invoice");
    $transactionNew->save();

    $msg[] = "<b class=\"transaction-approved\">Invoice item '$memo' on invoice number $num saved.</b>";
    $msg[] = "<b class=\"transaction-approved\">Transaction with ID ".$transactionNew->get_id()." saved.</b>";
  }
  $TPL["msg"] = implode("<br>", $msg);
}
$TPL["main_alloc_title"] = "Upload Invoices - ".APPLICATION_NAME;
include_template("templates/invoicesUploadM.tpl");

page_close();



?>
