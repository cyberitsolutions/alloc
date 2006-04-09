<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");
$current_user->check_employee();

global $current_user, $TPL, $db, $save, $saveAndNew, $saveGoTf;

$db = new db_alloc;
$transaction = new transaction;

if ($transactionID && !$new) {
  $transaction->set_id($transactionID);
  $transaction->select();
}

$tf = $transaction->get_foreign_object("tf");
$tf->check_perm();

$invoice_item = $transaction->get_foreign_object("invoiceItem");
$invoice_item->set_tpl_values();
$invoice = $invoice_item->get_foreign_object("invoice");
$invoice->set_tpl_values();

$transaction->set_tpl_values();



if ($save || $saveAndNew || $saveGoTf) {

  $transaction->read_globals();

  // Tweaked validation to allow reporting of multiple errors
  $transaction->get_value("amount")          or $TPL["message"][] = "You must enter a valid amount";
  $transaction->get_value("transactionDate") or $TPL["message"][] = "You must enter a date for the transaction";
  $transaction->get_value("product")         or $TPL["message"][] = "You must enter a product"; 
  $transaction->get_value("status")          or $TPL["message"][] = "You must set the status of the transaction";
  $transaction->get_value("tfID")            or $TPL["message"][] = "You must select a TF";
  $transaction->get_value("transactionType") or $TPL["message"][] = "You must set a transaction type";
  #$transaction->get_value("projectID")       or $TPL["message"][] = "You must select a project";
  #$transaction->get_value("companyDetails")  or $TPL["message"][] = "You must enter the company details";

  if (!count($TPL["message"]))  {
    $transaction->check_perm(PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION);
    $transaction->set_value("lastModified", date("YmdHis"));
    $transaction->set_value("amount",str_replace(array("$",","),"",$transaction->get_value("amount")));
    $transaction->save();
    $TPL["message_good"][] = "Transaction Saved";

    if ($saveAndNew) {
      header("Location: ".$TPL["url_alloc_transaction"]."new=true");
    }

    if ($saveGoTf) {
      header("Location: ".$TPL["url_alloc_transactionList"]."tfID=".$transaction->get_value("tfID"));
    }
    $transaction->set_tpl_values();

  }
    
} else if ($delete) {
  $transaction->delete();
  header("location:".$TPL["url_alloc_transactionList"]."tfID=".$transaction->get_value("tfID"));
}

$transaction->set_tpl_values();

$TPL["lastModified"] = get_display_date($transaction->get_value("lastModified"));
$TPL["product"] = htmlentities($transaction->get_value("product"));
$TPL["statusOptions"] = get_options_from_array(array("pending", "rejected", "approved"), $transaction->get_value("status"), false);
$TPL["transactionTypeOptions"] = get_options_from_array(array("expense", "invoice", "salary", "commission", "timesheet", "adjustment", "insurance"), $transaction->get_value("transactionType"), false);

$db = new db_alloc;
$db->query("SELECT tfID, tfName FROM tf ORDER BY tfName");
$TPL["tfIDOptions"] = get_options_from_db($db, "tfName", "tfID", $transaction->get_value("tfID"));

$db->query("SELECT projectName, projectID FROM project WHERE projectStatus = 'current' ORDER BY projectName");
$TPL["projectIDOptions"] = get_options_from_db($db, "projectName", "projectID", $transaction->get_value("projectID"));

if ($TPL["transactionModifiedUser"]) {
  $db->query("select username from person where personID = ".$TPL["transactionModifiedUser"]);
  $db->next_record();
  $TPL["transactionModifiedUser"] = $db->f("username");
}

if ($transaction->have_perm(PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION)) {
  include_template("templates/editTransactionM.tpl");
} else {
  include_template("templates/viewTransactionM.tpl");
}

page_close();




?>
