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

$current_user->check_employee();

$expenseForm = new expenseForm;
$transaction_to_edit = new transaction;

$db = new db_alloc;

$expenseFormID = $_POST["expenseFormID"] or $expenseFormID = $_GET["expenseFormID"];

if ($expenseFormID) {
  $expenseForm->read_globals();
  $expenseForm->set_id($expenseFormID);
  if (!$expenseForm->select()) {
    $TPL["message"][] = "Bad Expense Form ID";
    $expenseForm = new expenseForm;
  }
} 



if ($_POST["add"]) {

  $_POST["product"]        or $TPL["message"][] = "You must enter a Product.";
  $_POST["companyDetails"] or $TPL["message"][] = "You must enter the Company Details.";
  $_POST["tfID"]           or $TPL["message"][] = "You must enter the TF.";
  $_POST["quantity"]       or $_POST["quantity"] = 1;

  if (!ereg("^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$", $_POST["transactionDate"])) {
    $TPL["message"][] = "You must enter the Date Incurred in the format yyyy-mm-dd.";
  }
  
  if ($_POST["amount"] === "") {
    $TPL["message"][] = "You must enter the Price.";
  }
  $_POST["amount"] = sprintf("%0.2f",$_POST["amount"]);
  $_POST["amount"] = -$_POST["amount"] * $_POST["quantity"];
  $_POST["amount"] = sprintf("%0.2f",$_POST["amount"]);


  $transaction = new transaction;
  $transactionID && $transaction->set_id($_POST["transactionID"]);
  $transaction->read_globals();

  if (!count($TPL["message"])) {
    $transaction->set_value("transactionType", "expense");
    $transaction->set_value("expenseFormID", $expenseForm->get_id());
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

$transaction_to_edit->set_tpl_values();

if ($transaction_to_edit->get_value("quantity")) {
  $TPL["amount"] = -$transaction_to_edit->get_value("amount") / $transaction_to_edit->get_value("quantity");
}

if ($_POST["delete"] && $_POST["expenseFormID"] && $_POST["transactionID"]) {
  $expenseForm->delete_transactions($_POST["transactionID"]);
  $expenseForm->set_id($_POST["expenseFormID"]);
  $expenseForm->select();
}

if ($transaction_to_edit->get_value("tfID")) {
  $selectedTfID = $transaction_to_edit->get_value("tfID");
  $selectedProjectID = $transaction_to_edit->get_value("projectID");

} else {
  $query = "SELECT tfID FROM tfPerson WHERE personID=".$current_user->get_id()." LIMIT 1";
  $db->query($query);

  if ($db->next_record()) {
    $selectedTfID = $db->f("tfID");
  } else {
    $selectedTfID = 0;
  }
  $selectedProject = 0;
}

$db->query("SELECT * FROM tf ORDER BY tfName");
$TPL["tfOptions"] = get_option("", "0", false)."\n";
$TPL["tfOptions"].= get_options_from_db($db, "tfName", "tfID", $selectedTfID);

$db->query("SELECT projectName, projectID FROM project WHERE projectStatus = 'current' ORDER BY projectName");
$TPL["projectOptions"] = get_option("", "0", false)."\n";
$TPL["projectOptions"].= get_options_from_db($db, "projectName", "projectID", $selectedProjectID);

if (is_object($expenseForm)) { 
  $expenseForm->set_tpl_values();
  $TPL["expenseFormID"] = $expenseForm->get_id();
}

if (is_object($expenseForm) && $expenseForm->get_value("expenseFormModifiedUser")) {
  $p = new person;
  $p->set_id($expenseForm->get_value("expenseFormModifiedUser"));
  $p->select();
  $TPL["user"] = $p->get_username(1);
}

if ($_POST["cancel"]) {
  if (is_object($expenseForm)) {
    $expenseForm->delete_transactions();
    $expenseForm->delete();
  
    header("location:".$TPL["url_alloc_expenseFormList"]);
  } else {
    $TPL["message"][] = "Unable to delete Expense Form";
  }

} else if ($_POST["pend"]) {
  $expenseForm->save();
  $expenseForm->set_status("pending");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["approve"]) {
  $expenseForm->save();
  $expenseForm->set_status("approved");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["reject"]) {
  $expenseForm->save();
  $expenseForm->set_status("rejected");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["save"]) {
  $expenseForm->read_globals();
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["finalise"]) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 1);
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($_POST["unfinalise"]) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 0);
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."expenseFormID=".$expenseForm->get_id());
  exit();
}

if (is_object($expenseForm) && $expenseForm->get_value("expenseFormFinalised") && $current_user->get_id() == $expenseForm->get_value("enteredBy")) {
  $TPL["message_help"][] = "Step 4/4: Print out the Expense Form using the Printer Friendly Version link, attach receipts and hand in to office admin.";

} else if (check_optional_has_line_items() && !$expenseForm->get_value("expenseFormFinalised")) {  
  $TPL["message_help"][] = "Step 3/4: When finished adding Expense Form Line Items, click the To Admin button to finalise the Expense Form.";

} else if (is_object($expenseForm) && $expenseForm->get_id() && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["message_help"][] = "Step 2/4: Add Expense Form Line Items by filling in the details and clicking the Add Expense Form Line Item button.";
} else if (!is_object($expenseForm) || !$expenseForm->get_id()) {
  $TPL["message_help"][] = "Step 1/4: Begin an Expense Form by choosing the Payment Method and Reimbursement option, then clicking the Create Expense Form button.";
}

$paymentOptions = array("COD", "Cheque", "Company Amex Charge", "Company Amex Blue", "Company Virgin MasterCard", "Other Credit Card", "Account", "Direct Deposit");
$paymentOptions = get_options_from_array($paymentOptions, $expenseForm->get_value("paymentMethod"), false);

$reimbursementRequired_checked = $expenseForm->get_value("reimbursementRequired") ? " checked" : "";
$reimbursementRequired_label   = $expenseForm->get_value("reimbursementRequired") ? "Yes" : "No";

$TPL["paymentMethodOptions"] = $expenseForm->get_value("paymentMethod");
$TPL["reimbursementRequiredOption"] = $reimbursementRequired_label;

if (is_object($expenseForm) && $expenseForm->get_id() && check_optional_allow_edit()) {

  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"save\" value=\"Save Expense Form\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"cancel\" value=\"Delete\" onClick=\"return confirm('Delete this Expense Form?')\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"finalise\" value=\"To Admin -&gt;\">";

  $TPL["paymentMethodOptions"] = "<select name=\"paymentMethod\">".$paymentOptions."</select>";
  $TPL["reimbursementRequiredOption"] = "<input type=\"checkbox\" name=\"reimbursementRequired\" value=\"1\"".$reimbursementRequired_checked.">";


} else if (is_object($expenseForm) && $expenseForm->get_id() && have_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION)) {
  
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"unfinalise\" value=\"&lt;- Edit\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"save\" value=\"Save\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"pend\" value=\"Pending\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"approve\" value=\"Approve\">";
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"reject\" value=\"Reject\">";
  $TPL["expenseFormButtons"].= "&nbsp;<a href=\"".$TPL["url_alloc_expenseFormList"]."\">Return To Pending Expense Forms</a>";

} else if (is_object($expenseForm) && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"save\" value=\"Create Expense Form\">";
  $TPL["paymentMethodOptions"] = "<select name=\"paymentMethod\">".$paymentOptions."</select>";
  $TPL["reimbursementRequiredOption"] = "<input type=\"checkbox\" name=\"reimbursementRequired\" value=\"1\"".$reimbursementRequired_checked.">";
}

if (is_object($expenseForm) && $expenseForm->get_id()) {
  $db = new db_alloc;
  $db->query(sprintf("SELECT SUM(amount) AS sum FROM transaction WHERE expenseFormID = %d",$expenseForm->get_id()));
  $db->next_record();
  $TPL["formTotal"] = sprintf("%0.2f",abs($db->f("sum")));
}


if ($_GET["printVersion"]) {
  include_template("templates/exp-one-off-printableM.tpl");
} else {
  include_template("templates/exp-one-offM.tpl");
}

function show_all_exp($template) {

  global $TPL, $expenseForm, $db;

  if ($expenseForm->get_id()) {

    $transaction = new transaction;
    $tf = new tf;

    if ($_POST["transactionID"] && $_POST["edit"]) {   // if edit is clicked
      $query = sprintf("SELECT * FROM transaction WHERE expenseFormID=%d AND transactionID<>%d ORDER BY transactionID DESC", $expenseForm->get_id()
                       , $_POST["transactionID"]);
    } else {
      $query = sprintf("SELECT * FROM transaction WHERE expenseFormID=%d ORDER BY transactionID DESC", $expenseForm->get_id());
    }

    $db->query($query);

    while ($db->next_record()) {

      $transaction->read_db_record($db,false);
      $transaction->set_tpl_values();

      $transaction->get_value("quantity") and $TPL["amount"] = -$transaction->get_value("amount") / $transaction->get_value("quantity");
      $TPL["amount"] = sprintf("%0.2f",$TPL["amount"]);

      $TPL["lineTotal"] = sprintf("%0.2f",$TPL["amount"] * $transaction->get_value("quantity"));
      $tf->set_id($transaction->get_value("tfID"));
      $tf->select();
      $TPL["tfID"] = $tf->get_value("tfName");

      $projectID = $transaction->get_value("projectID");
      if (isset($_POST["projectID"]) && $_POST["projectID"] != "") {
        $project = new project;
        $project->set_id($transaction->get_value("projectID"));
        $project->select();
        $TPL["projectID"] = $project->get_value("projectName");
      }

      include_template($template);
    }
  }
}

function check_optional_allow_edit() {
  global $db, $expenseForm;

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
    $db = new db_alloc;
    $q = sprintf("SELECT COUNT(*) as tally FROM transaction WHERE expenseFormID = %d",$expenseForm->get_id());
    $db->query($q);
    $db->next_record();
    return $db->f("tally");
  }
}

function check_optional_show_line_item_add() {
  global $expenseForm;
  return (is_object($expenseForm) && $expenseForm->get_id() && $expenseForm->get_value("expenseFormFinalised")!=1);
}


page_close();
?>
