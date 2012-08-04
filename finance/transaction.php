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

require_once("../alloc.php");
$current_user->check_employee();

$current_user = &singleton("current_user");
global $TPL;
global $db;
global $save;
global $saveAndNew;
global $saveGoTf;

function add_tf($tfID, $options, $warningKey, $warningValue) {
  // add a tf to the array of options, if it's not already there
  global $TPL;
  if($tfID && !array_key_exists($tfID, $options)) {
    $tf = new tf();
    $tf->set_id($tfID);
    $tf->select();
    $options[$tfID] = $tf->get_value("tfName");
    $TPL[$warningKey] = sprintf($warningValue, $tf->get_value("tfName"));
  }
  return $options;
}

$db = new db_alloc();
$transaction = new transaction();
$transaction->read_globals();
$transactionID = $_POST["transactionID"] or $transactionID = $_GET["transactionID"];

if ($transactionID && !$_GET["new"]) {
  $transaction->set_id($transactionID);
  $transaction->select();
}

$invoice_item = $transaction->get_foreign_object("invoiceItem");
$invoice_item->set_values();
$invoice = $invoice_item->get_foreign_object("invoice");
if (!$invoice->get_id()) {
  $invoice = $transaction->get_foreign_object("invoice");
}
$invoice->set_values();
if ($invoice->get_id()) {
  $TPL["invoice_link"] = "<a href=\"".$TPL["url_alloc_invoice"]."invoiceID=".$invoice->get_id()."\">#".$invoice->get_value("invoiceNum");
  $TPL["invoice_link"].= " ".$invoice->get_value("invoiceDateFrom")." to ". $invoice->get_value("invoiceDateTo")."</a>";
}

$expenseForm = $transaction->get_foreign_object("expenseForm");
if ($expenseForm->get_id()) {
  $TPL["expenseForm_link"] = "<a href=\"".$TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id()."\">#".$expenseForm->get_id()."</a>";
}

$timeSheet = $transaction->get_foreign_object("timeSheet");
if ($timeSheet->get_id()) {
  $TPL["timeSheet_link"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\">#".$timeSheet->get_id()."</a>";
}

$transaction->set_values();




if ($_POST["save"] || $_POST["saveAndNew"] || $_POST["saveGoTf"]) {
/*
  if ($transaction->get_value("status") != "pending") {
    alloc_error("This transaction is no longer editable.");
  }
*/
  $transaction->read_globals();

  // Tweaked validation to allow reporting of multiple errors
  $transaction->get_value("amount")          or alloc_error("You must enter a valid amount");
  $transaction->get_value("transactionDate") or alloc_error("You must enter a date for the transaction");
  $transaction->get_value("product")         or alloc_error("You must enter a product");
  $transaction->get_value("status")          or alloc_error("You must set the status of the transaction");
  $transaction->get_value("fromTfID")        or alloc_error("You must select a Source Tagged Fund to take this transaction from");
  $transaction->get_value("tfID")            or alloc_error("You must select a Destination Tagged Fund to add this transaction against");
  $transaction->get_value("transactionType") or alloc_error("You must set a transaction type");
  $transaction->get_value("currencyTypeID")  or alloc_error("You must set a transaction currency");
  #$transaction->get_value("projectID")       or alloc_error("You must select a project");
  #$transaction->get_value("companyDetails")  or alloc_error("You must enter the company details");

  if (!count($TPL["message"]))  {
    $transaction->set_value("amount",str_replace(array("$",","),"",$transaction->get_value("amount")));
    if ($transaction->save()) { // need to check this again as transaction->save might have triggered an error
      $TPL["message_good"][] = "Transaction Saved";

      if ($_POST["saveAndNew"]) {
        alloc_redirect($TPL["url_alloc_transaction"]."new=true");
      }

      if ($_POST["saveGoTf"]) {
        alloc_redirect($TPL["url_alloc_transactionList"]."tfID=".$transaction->get_value("tfID"));
      }

      alloc_redirect($TPL["url_alloc_transaction"]."transactionID=".$transaction->get_id());
    }
  }
    
} else if ($_POST["delete"]) {
  $transaction->delete();
  alloc_redirect($TPL["url_alloc_transactionList"]."tfID=".$transaction->get_value("tfID"));
}

$transaction->set_tpl_values();

$t = new meta("currencyType");
$currency_array = $t->get_assoc_array("currencyTypeID","currencyTypeID");
$TPL["currencyOptions"] = page::select_options($currency_array,$transaction->get_value("currencyTypeID"));
$TPL["product"] = page::htmlentities($transaction->get_value("product"));
$TPL["statusOptions"] = page::select_options(array("pending"=>"Pending", "rejected"=>"Rejected", "approved"=>"Approved"), $transaction->get_value("status"));
$transactionTypes = transaction::get_transactionTypes();
$TPL["transactionTypeOptions"] = page::select_options($transactionTypes, $transaction->get_value("transactionType"));

is_object($transaction) and $TPL["transactionTypeLink"] = $transaction->get_transaction_type_link();

$db = new db_alloc();

$tf = new tf();
$options = $tf->get_assoc_array("tfID","tfName");
// Special cases for the current tfID and fromTfID
$options = add_tf($transaction->get_value("tfID"), $options, "tfIDWarning", " (warning: the TF <b>%s</b> is currently inactive)");
$options = add_tf($transaction->get_value("fromTfID"), $options, "fromTfIDWarning", " (warning: the TF <b>%s</b> is currently inactive)");


$TPL["tfIDOptions"] = page::select_options($options, $transaction->get_value("tfID"));
$TPL["fromTfIDOptions"] = page::select_options($options, $transaction->get_value("fromTfID"));

$q = "SELECT projectID as value, projectName as label FROM project WHERE projectStatus = 'Current' ORDER BY projectName";
$TPL["projectIDOptions"] = page::select_options($q, $transaction->get_value("projectID"));

$TPL["transactionModifiedUser"] = page::htmlentities(person::get_fullname($TPL["transactionModifiedUser"]));
$TPL["transactionCreatedUser"] = page::htmlentities(person::get_fullname($TPL["transactionCreatedUser"]));

$tf1 = new tf();
$tf1->set_id($TPL["tfID"]);
$tf1->select();
$TPL["tf_link"] = $tf1->get_link();

$tf2 = new tf();
$tf2->set_id($TPL["fromTfID"]);
$tf2->select();
$TPL["from_tf_link"] = $tf2->get_link();

$p = $transaction->get_foreign_object("project");
$TPL["project_link"] = $p->get_link();

$TPL["taxName"] = config::get_config_item("taxName");

if (is_object($current_user) && !$current_user->have_role("admin") && is_object($transaction) && in_array($transaction->get_value("status"),array("approved","rejected"))) {
  $TPL["main_alloc_title"] = "View Transaction - ".APPLICATION_NAME;
  include_template("templates/viewTransactionM.tpl");
} else {
  $TPL["main_alloc_title"] = "Create Transaction - ".APPLICATION_NAME;
  include_template("templates/editTransactionM.tpl");
}


?>
