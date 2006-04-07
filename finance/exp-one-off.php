<?php
include("alloc.inc");

$current_user->check_employee();

$expenseForm = new expenseForm;
$transaction_to_edit = new transaction;

$db = new db_alloc;

if ($expenseFormID) {
  $expenseForm->read_globals();
  $expenseForm->set_id($expenseFormID);
  if (!$expenseForm->select()) {
    $TPL["message"][] = "Bad Expense Form ID";
    $expenseForm = new expenseForm;
  }
} 



if ($add) {

  $product        or $TPL["message"][] = "You must enter a Product.";
  $companyDetails or $TPL["message"][] = "You must enter the Company Details.";
  $tfID           or $TPL["message"][] = "You must enter the TF.";
  $quantity       or $quantity = 1;

  if (!ereg("^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$", $transactionDate)) {
    $TPL["message"][] = "You must enter the Date Incurred in the format yyyy-mm-dd.";
  }

  $amount = -$amount * $quantity;

  if ($amount == "") {
    $TPL["message"][] = "You must enter the Price.";
  } else if (!(is_float($amount) || is_int($amount))) {
    $TPL["message"][] = "You must enter a number for the Price.";
  } else if ($amount >= 0) {
    $TPL["message"][] = "You must enter a Price greater than 0.";
  }


  $transaction = new transaction;
  $transactionID && $transaction->set_id($transactionID);
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

if ($cancel) {
  if (is_object($expenseForm)) {
    $expenseForm->delete_transactions();
    $expenseForm->delete();
  
    header("location:".$TPL["url_alloc_expenseFormList"]);
  } else {
    $TPL["message"][] = "Unable to delete Expense Form";
  }

} else if ($pend) {
  $expenseForm->save();
  $expenseForm->set_status("pending");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($approve) {
  $expenseForm->save();
  $expenseForm->set_status("approved");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($reject) {
  $expenseForm->save();
  $expenseForm->set_status("rejected");
  page_close();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($save) {
  $expenseForm->read_globals();
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($finalise) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 1);
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();

} else if ($unfinalise) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 0);
  $expenseForm->save();
  header("Location: ".$TPL["url_alloc_expOneOff"]."&expenseFormID=".$expenseForm->get_id());
  exit();
}

if (is_object($expenseForm) && $expenseForm->get_value("expenseFormFinalised")) {
  $TPL["message_help"][] = "Step 4/4: Print out the Expense Form using the Printer Friendly Version link, attach receipts and hand in to office admin.";

} else if (check_optional_has_line_items() && !$expenseForm->get_value("expenseFormFinalised")) {  
  $TPL["message_help"][] = "Step 3/4: When finished adding Expense Form Line Items, click the To Admin button to finalise the Expense Form (it will no longer be editable).";

} else if (is_object($expenseForm) && $expenseForm->get_id() && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["message_help"][] = "Step 2/4: Add Expense Form Line Items by filling in the details and clicking the Add Expense Form Line Item button.";
} else {
  $TPL["message_help"][] = "Step 1/4: Begin an Expense Form by choosing the Payment Method and Reimbursement option, then clicking the Create Expense Form button.";
}

$paymentOptions = array("COD", "Cheque", "Company Amex Charge", "Company Amex Blue", "Other Credit Card", "Account", "Direct Deposit");
$paymentOptions = get_options_from_array($paymentOptions, $expenseForm->get_value("paymentMethod"), false);

$reimbursementRequired_checked = $expenseForm->get_value("reimbursementRequired") ? " checked" : "";
$reimbursementRequired_label   = $expenseForm->get_value("reimbursementRequired") ? "Yes" : "No";

$TPL["paymentMethodOptions"] = $expenseForm->get_value("paymentMethod");
$TPL["reimbursementRequiredOption"] = $reimbursementRequired_label;
$TPL["chequeNumberInput"] = $TPL["chequeNumber"];
$TPL["chequeDateInput"]   = $TPL["chequeDate"];

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

  $TPL["chequeNumberInput"] = "<input type=\"text\" size=\"15\" name=\"chequeNumber\" value=\"".$TPL["chequeNumber"]."\">";
  $TPL["chequeDateInput"]   = "<input type=\"text\" size=\"10\" name=\"chequeDate\" value=\"".$TPL["chequeDate"]."\">";
  $TPL["chequeDateInput"]  .= "<input type=\"button\" onClick=\"chequeDate.value='".$TPL["today"]."'\" value=\"Today\">";

} else if (is_object($expenseForm) && !$expenseForm->get_value("expenseFormFinalised")) {
  $TPL["expenseFormButtons"].= "&nbsp;<input type=\"submit\" name=\"save\" value=\"Create Expense Form\">";
  $TPL["paymentMethodOptions"] = "<select name=\"paymentMethod\">".$paymentOptions."</select>";
  $TPL["reimbursementRequiredOption"] = "<input type=\"checkbox\" name=\"reimbursementRequired\" value=\"1\"".$reimbursementRequired_checked.">";
}

if (is_object($expenseForm) && $expenseForm->get_id()) {
  $db = new db_alloc;
  $db->query(sprintf("SELECT SUM(amount) AS sum FROM transaction WHERE expenseFormID = %d",$expenseForm->get_id()));
  $db->next_record();
  $TPL["formTotal"] = abs($db->f("sum"));
}


if ($printVersion) {
  include_template("templates/exp-one-off-printableM.tpl");
} else {
  include_template("templates/exp-one-offM.tpl");
}

function show_all_exp($template) {

  global $TPL, $expenseForm, $db, $transactionID, $edit;

  if ($expenseForm->get_id()) {

    $transaction = new transaction;
    $tf = new tf;

    if ($transactionID && $edit) {   // if edit is clicked
      $query = sprintf("SELECT * FROM transaction WHERE expenseFormID=%d AND transactionID<>%d ORDER BY transactionID DESC", $expenseForm->get_id()
                       , $transactionID);
    } else {
      $query = sprintf("SELECT * FROM transaction WHERE expenseFormID=%d ORDER BY transactionID DESC", $expenseForm->get_id());
    }

    $db->query($query);

    while ($db->next_record()) {

      $transaction->read_db_record($db,false);
      $transaction->set_tpl_values();

      $transaction->get_value("quantity") and $TPL["amount"] = -$transaction->get_value("amount") / $transaction->get_value("quantity");
      $TPL["amount"] = number_format($TPL["amount"], 2);


      $tf->set_id($transaction->get_value("tfID"));
      $tf->select();
      $TPL["tfID"] = $tf->get_value("tfName");

      $projectID = $transaction->get_value("projectID");
      if (isset($projectID) && $projectID != "") {
        $project = new project;
        $project->set_id($transaction->get_value("projectID"));
        $project->select();
        $TPL["projectID"] = $project->get_value("projectName");
      }

      include_template($template);
    }
  }
}

function check_editable() {
  global $db, $expenseForm, $allow_edit, $current_user;

  $permissions = explode(",", $current_user->get_value("perms"));

  if (is_object($expenseForm) && !$expenseForm->get_id()) { // New Expense Form
    $allow_edit = true;

  } else if (is_object($expenseForm) && $expenseForm->get_value("expenseFormFinalised") != 1 && $expenseForm->get_id() && $expenseForm->is_owner()) {
    $allow_edit = true;

  } else {
    $allow_edit = false;
  }

  return $allow_edit;
}

function check_optional_allow_edit() {
  global $allow_edit;

  if (!isset($allow_edit)) {
    $allow_edit = check_editable();
  }
  return $allow_edit;
}

function check_optional_no_edit() {
  global $allow_edit;

  if (!isset($allow_edit)) {
    $allow_edit = check_editable();
  }
  return ($allow_edit == true) ? false : true;
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
