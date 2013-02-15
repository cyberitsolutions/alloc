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


function show_all_exp($template) {

  global $TPL;
  global $expenseForm;
  global $db;
  global $transaction_to_edit;

  if ($expenseForm->get_id()) {


    if ($_POST["transactionID"] && ($_POST["edit"] || ( is_object($transaction_to_edit) && $transaction_to_edit->get_id() ) )) {   // if edit is clicked OR if we've rejected changes made to something so are still editing it
      $query = prepare("SELECT * FROM transaction WHERE expenseFormID=%d AND transactionID<>%d ORDER BY transactionID DESC", $expenseForm->get_id()
                       , $_POST["transactionID"]);
    } else {
      $query = prepare("SELECT * FROM transaction WHERE expenseFormID=%d ORDER BY transactionID DESC", $expenseForm->get_id());
    }

    $db->query($query);

    while ($db->next_record()) {

      $transaction = new transaction();
      $transaction->read_db_record($db);
      $transaction->set_values();

      $transaction->get_value("quantity") and $TPL["amount"] = $transaction->get_value("amount") / $transaction->get_value("quantity");

      $TPL["lineTotal"] = $TPL["amount"] * $transaction->get_value("quantity");

      $tf = new tf();
      $tf->set_id($transaction->get_value("fromTfID"));
      $tf->select();
      $TPL["fromTfIDLink"] = $tf->get_link();

      $tf = new tf();
      $tf->set_id($transaction->get_value("tfID"));
      $tf->select();
      $TPL["tfIDLink"] = $tf->get_link();
  
      $projectID = $transaction->get_value("projectID");
      if($projectID) {
        $project = new project();
        $project->set_id($transaction->get_value("projectID"));
        $project->select();
        $TPL["projectName"] = $project->get_value("projectName");
      }

      if($transaction->get_value("fromTfID") == config::get_config_item("expenseFormTfID")) {
        $TPL['expense_class'] = "loud";
      } else {
        $TPL['expense_class'] = "";
      }

      include_template($template);
    }
  }
}

function check_optional_allow_edit() {
  global $db;
  global $expenseForm;

  if (is_object($expenseForm) && !$expenseForm->get_id()) { // New Expense Form
    $allow_edit = true;

  } else if (is_object($expenseForm) && $expenseForm->get_value("expenseFormFinalised") != 1 && $expenseForm->get_id() && $expenseForm->is_owner()) {
    $allow_edit = true;

  } else {
    $allow_edit = false;
  }

  return $allow_edit;
}

function check_optional_no_edit() {
  $allow_edit = check_optional_allow_edit();
  return !$allow_edit;
}

function check_optional_has_line_items() {
  global $expenseForm;
  if (is_object($expenseForm) && $expenseForm->get_id()) {
    $db = new db_alloc();
    $q = prepare("SELECT COUNT(*) as tally FROM transaction WHERE expenseFormID = %d",$expenseForm->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f("tally");
  }
}

function check_optional_show_line_item_add() {
  global $expenseForm;
  return (is_object($expenseForm) && $expenseForm->get_id() && $expenseForm->get_value("expenseFormFinalised")!=1);
}

if (!config::get_config_item("mainTfID")) {
  alloc_error("This functionality will not work until you set a Finance TF on the Setup -> Finance screen.");
}

$current_user->check_employee();

$expenseForm = new expenseForm();
$transaction_to_edit = new transaction();

$db = new db_alloc();

$expenseFormID = $_POST["expenseFormID"] or $expenseFormID = $_GET["expenseFormID"];

if ($expenseFormID) {
  $expenseForm->read_globals();
  $expenseForm->set_id($expenseFormID);
  if (!$expenseForm->select()) {
    alloc_error("Bad Expense Form ID");
    $expenseForm = new expenseForm();
  }
} 



if ($_POST["add"]) {

  $_POST["product"]        or alloc_error("You must enter a Product.");
  $_POST["companyDetails"] or alloc_error("You must enter the Company Details.");
  $_POST["fromTfID"]       or alloc_error("You must enter the Source TF.");
  $_POST["quantity"]       or $_POST["quantity"] = 1;
  config::get_config_item("mainTfID") or alloc_error("You must configure the Finance Tagged Fund on the Setup -> Finance screen.");

  if ($_POST["amount"] === "") {
    alloc_error("You must enter the Price.");
  }
  $_POST["amount"] = $_POST["amount"] * $_POST["quantity"];


  $transaction = new transaction();
  $transactionID && $transaction->set_id($_POST["transactionID"]);
  $transaction->read_globals();

  // check we have permission to make the transaction
  if (!$transaction->have_perm(PERM_CREATE)) {
    alloc_error("You do not have permission to create transactions for that Source TF.");
  }

  if (!count($TPL["message"])) {
    $transaction->set_value("transactionType", "expense");
    $transaction->set_value("expenseFormID", $expenseForm->get_id());
    $transaction->set_value("tfID",config::get_config_item("mainTfID"));
    $transaction->save();

  } else {
    $transaction_to_edit = $transaction;
  }
}

if ($_POST["edit"] && $_POST["expenseFormID"] && $_POST["transactionID"]) {
  $transaction_to_edit->set_id($_POST["transactionID"]);
  $transaction_to_edit->select();
  $TPL["transactionID"] = $_POST["transactionID"];
}

$transaction_to_edit->set_values();

if ($transaction_to_edit->get_value("quantity")) {
  $TPL["amount"] = $transaction_to_edit->get_value("amount",DST_HTML_DISPLAY) / $transaction_to_edit->get_value("quantity");
}

if ($_POST["delete"] && $_POST["expenseFormID"] && $_POST["transactionID"]) {
  $expenseForm->delete_transactions($_POST["transactionID"]);
  $expenseForm->set_id($_POST["expenseFormID"]);
  $expenseForm->select();
}

if ($transaction_to_edit->get_value("fromTfID")) {
  $selectedTfID = $transaction_to_edit->get_value("fromTfID");
  $selectedProjectID = $transaction_to_edit->get_value("projectID");

} else {
  $query = prepare("SELECT tfID FROM tfPerson WHERE personID=%d LIMIT 1",$current_user->get_id());
  $db->query($query);

  if ($db->next_record()) {
    $selectedTfID = $db->f("tfID");
  } else {
    $selectedTfID = 0;
  }
  $selectedProject = 0;
}

$tf = new tf();
$options = $tf->get_assoc_array("tfID","tfName");
$TPL["fromTfOptions"] = page::select_options($options, $selectedTfID);

$m = new meta("currencyType");
$currencyOps = $m->get_assoc_array("currencyTypeID","currencyTypeID");
$TPL["currencyTypeOptions"] = page::select_options($currencyOps,$transaction_to_edit->get_value("currencyTypeID"));


if (is_object($expenseForm) && $expenseForm->get_value("clientID")) { 
  $clientID_sql = prepare(" AND clientID = %d",$expenseForm->get_value("clientID"));
}

$q = "SELECT projectID AS value, projectName AS label 
        FROM project 
       WHERE projectStatus = 'Current' 
             ".$clientID_sql." 
    ORDER BY projectName";
$TPL["projectOptions"] = page::select_options($q, $selectedProjectID);

if (is_object($expenseForm)) { 
  $expenseForm->set_values();
  $TPL["expenseFormID"] = $expenseForm->get_id();
}

if (is_object($expenseForm) && $expenseForm->get_value("expenseFormCreatedUser")) {
  $p = new person();
  $p->set_id($expenseForm->get_value("expenseFormCreatedUser"));
  $p->select();
  $TPL["user"] = $p->get_name();
}

if ($_POST["cancel"]) {
  if (is_object($expenseForm)) {
    $expenseForm->delete_transactions();
    $expenseForm->delete();

    alloc_redirect($TPL["url_alloc_expenseFormList"]);
  } else {
    alloc_error("Unable to delete Expense Form");
  }

} else if ($_POST["changeTransactionStatus"] == "pending") {
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  $expenseForm->set_status("pending");
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["changeTransactionStatus"] == "approved") {
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  $expenseForm->set_status("approved");
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["changeTransactionStatus"] == "rejected") {
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  $expenseForm->set_status("rejected");
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["save"]) {
  $expenseForm->read_globals();
  if ($expenseForm->get_value("reimbursementRequired") == 0 || $expenseForm->get_value("reimbursementRequired") == 1) {
    $expenseForm->set_value("paymentMethod", "");
  }
  $expenseForm->set_value("seekClientReimbursement", $_POST["seekClientReimbursement"] ? 1 : 0);
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["finalise"]) {

  $db = new db_alloc();
  $hasItems = $db->qr("SELECT * FROM transaction WHERE expenseFormID = %d",$expenseForm->get_id());
  if (!$hasItems) {
    alloc_error("Unable to submit expense form, no items have been added.");
    alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
    exit();
  }

  $expenseForm->read_globals();
  if ($expenseForm->get_value("reimbursementRequired") == 0 || $expenseForm->get_value("reimbursementRequired") == 1) {
    $expenseForm->set_value("paymentMethod", "");
  }
  $expenseForm->set_value("seekClientReimbursement", $_POST["seekClientReimbursement"] ? 1 : 0);
  $expenseForm->set_value("expenseFormFinalised", 1);
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["unfinalise"]) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 0);
  $expenseForm->set_value("expenseFormComment",rtrim($expenseForm->get_value("expenseFormComment")));
  $expenseForm->save();
  alloc_redirect($TPL["url_alloc_expenseForm"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["attach_transactions_to_invoice"] && $current_user->have_role("admin")) {
  $expenseForm->save_to_invoice($_POST["attach_to_invoiceID"]);
}


if (is_object($expenseForm) && $expenseForm->get_value("expenseFormFinalised") && $current_user->get_id() == $expenseForm->get_value("expenseFormCreatedUser")) {
  $TPL["message_help"][] = "Step 4/4: Print out the Expense Form using the Printer Friendly Version link, attach receipts and hand in to office admin.";

} else if (check_optional_has_line_items() && !$expenseForm->get_value("expenseFormFinalised")) {  
  $TPL["message_help"][] = "Step 3/4: When finished adding Expense Form Line Items, click the To Admin button to finalise the Expense Form.";

} else if (is_object($expenseForm) && $expenseForm->get_id() && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["message_help"][] = "Step 2/4: Add Expense Form Line Items by filling in the details and clicking the Add Expense Form Line Item button.";

} else if (!is_object($expenseForm) || !$expenseForm->get_id()) {
  $TPL["message_help"][] = "Step 1/4: Begin an Expense Form by choosing the Payment Method and then clicking the Create Expense Form button.";
}

$paymentOptionNames = array("", "COD", "Cheque", "Company Amex Charge", "Company Amex Blue", "Company Virgin MasterCard", "Other Credit Card", "Account", "Direct Deposit");
$paymentOptions = page::select_options($paymentOptionNames, $expenseForm->get_value("paymentMethod"));


function get_reimbursementRequired_array() {
  return array("0"=>"Unpaid"
              ,"1"=>"Paid by me"
              ,"2"=>"Paid by company"
              );
}


$rr_options = $expenseForm->get_reimbursementRequired_array();
$rr_checked[sprintf("%d",$expenseForm->get_value("reimbursementRequired"))] = " checked";
$expenseForm->get_value("paymentMethod") and $extra = " (".$paymentOptionNames[$expenseForm->get_value("paymentMethod")].")";
$rr_label = $rr_options[$expenseForm->get_value("reimbursementRequired")].$extra;
$TPL["rr_label"] = $rr_options[$expenseForm->get_value("reimbursementRequired")].$extra;

foreach ($rr_options as $value => $label) {
  unset($extra);
  $value == 2 and $extra = " <select name=\"paymentMethod\">".$paymentOptions."</select>";
  $reimbursementRequiredRadios.= $br."<input type=\"radio\" name=\"reimbursementRequired\" value=\"".$value."\"".$rr_checked[$value].">".$label.$extra;
  $br = "<br>";
}


$TPL["paymentMethodOptions"] = $expenseForm->get_value("paymentMethod");
$TPL["reimbursementRequiredOption"] = $rr_label;

$scr_label = "No";
if ($expenseForm->get_value("seekClientReimbursement")) {
  $scr_sel = " checked";
  $scr_label = "Yes";
}

$TPL["seekClientReimbursementLabel"] = $scr_label;
$seekClientReimbursementOption = "<input type=\"checkbox\" value=\"1\" name=\"seekClientReimbursement\"".$scr_sel.">";
$scr_hidden = "<input type=\"hidden\" name=\"seekClientReimbursement\" value=\"".$expenseForm->get_value("seekClientReimbursement")."\">";
$TPL["seekClientReimbursementOption"] = $scr_label.$scr_hidden;

$c = new client();
$c->set_id($expenseForm->get_value("clientID"));
$c->select();
$clientName = page::htmlentities($c->get_name());
$clientName and $TPL["printer_clientID"] = $clientName;
$TPL["field_expenseFormComment"] = $expenseForm->get_value("expenseFormComment",DST_HTML_DISPLAY);

if (is_object($expenseForm) && $expenseForm->get_id() && check_optional_allow_edit()) {
  $TPL["expenseFormButtons"].= '
  <button type="submit" name="cancel" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
  <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
  <button type="submit" name="finalise" value="1" class="save_button">To Admin<i class="icon-arrow-right"></i></button>
  ';

  $TPL["paymentMethodOptions"] = "<select name=\"paymentMethod\">".$paymentOptions."</select>";
  $TPL["reimbursementRequiredOption"] = $reimbursementRequiredRadios; 
  $TPL["seekClientReimbursementOption"] = $seekClientReimbursementOption;
  $options["clientStatus"] = "Current";
  $ops = client::get_list($options);
  $ops = array_kv($ops,"clientID","clientName");
  $TPL["field_clientID"] = "<select name=\"clientID\"><option value=\"\">".page::select_options($ops,$expenseForm->get_value("clientID"))."</select>";
  $TPL["field_expenseFormComment"] = page::textarea("expenseFormComment",$expenseForm->get_value("expenseFormComment",DST_HTML_DISPLAY));

} else if (is_object($expenseForm) && $expenseForm->get_id() && $current_user->have_role("admin")) {

  $TPL["expenseFormButtons"].= '
  <button type="submit" name="unfinalise" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px;"></i>Edit</button>
  <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
  <select name="changeTransactionStatus"><option value="">Transaction Status<option value="approved">Approve<option value="rejected">Reject<option value="pending">Pending</select>
  ';


  $TPL["field_clientID"] = $clientName;
  $TPL["field_expenseFormComment"] = page::textarea("expenseFormComment",$expenseForm->get_value("expenseFormComment",DST_HTML_DISPLAY));

} else if (is_object($expenseForm) && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["expenseFormButtons"].= '&nbsp;
         <button type="submit" name="save" value="1" class="save_button">Create Expense Form<i class="icon-ok-sign"></i></button>';
  $TPL["paymentMethodOptions"] = "<select name=\"paymentMethod\">".$paymentOptions."</select>";
  $TPL["reimbursementRequiredOption"] = $reimbursementRequiredRadios;
  $TPL["seekClientReimbursementOption"] = $seekClientReimbursementOption;
  $options["clientStatus"] = "Current";
  $ops = client::get_list($options);
  $ops = array_kv($ops,"clientID","clientName");
  $TPL["field_clientID"] = "<select name=\"clientID\"><option value=\"\">".page::select_options($ops,$expenseForm->get_value("clientID"))."</select>";
  $TPL["field_expenseFormComment"] = page::textarea("expenseFormComment",$expenseForm->get_value("expenseFormComment",DST_HTML_DISPLAY));
}

if (is_object($expenseForm) && $expenseForm->get_id()) {
  $db = new db_alloc();
  $db->query(prepare("SELECT SUM(amount * pow(10,-currencyType.numberToBasic)) AS amount, 
                             transaction.currencyTypeID as currency
                        FROM transaction
                   LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                       WHERE expenseFormID = %d
                    GROUP BY transaction.currencyTypeID
                  ",$expenseForm->get_id()));
  while ($row = $db->row()) {
    $rows[] = $row;
  }
  $TPL["formTotal"] = page::money_print($rows);
}

if (is_object($expenseForm) && $current_user->have_role("admin")
&& !$expenseForm->get_invoice_link() && $expenseForm->get_value("expenseFormFinalised") && $expenseForm->get_value("seekClientReimbursement")) {

  $ops["invoiceStatus"] = "edit";
  $ops["clientID"] = $expenseForm->get_value("clientID");
  $invoice_list = invoice::get_list($ops);
  $invoice_list = array_kv($invoice_list,"invoiceID",array("invoiceNum","invoiceName"));
  $q = prepare("SELECT * FROM invoiceItem WHERE expenseFormID = %d",$expenseForm->get_id());
  $db = new db_alloc();
  $db->query($q);
  $row = $db->row();
  $sel_invoice = $row["invoiceID"];
  $TPL["attach_to_invoice_button"] = "<select name=\"attach_to_invoiceID\">";
  $TPL["attach_to_invoice_button"].= "<option value=\"create_new\">Create New Invoice</option>";
  $TPL["attach_to_invoice_button"].= page::select_options($invoice_list,$sel_invoice)."</select>";
  $TPL["attach_to_invoice_button"].= "<input type=\"submit\" name=\"attach_transactions_to_invoice\" value=\"Add to Invoice\"> ";
  $TPL["invoice_label"] = "Invoice:";
}

if (is_object($expenseForm)) {
  $invoice_link = $expenseForm->get_invoice_link();
  if ($invoice_link) {
    $TPL["invoice_label"] = "Invoice:";
    $TPL["invoice_link"] = $invoice_link;
  }
}

$TPL["taxName"] = config::get_config_item("taxName");

$TPL["main_alloc_title"] = "Expense Form - ".APPLICATION_NAME;
if ($_GET["printVersion"]) {
  include_template("templates/expenseFormPrintableM.tpl");
} else {
  include_template("templates/expenseFormM.tpl");
}

?>
