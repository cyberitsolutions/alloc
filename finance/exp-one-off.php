<?php
include("alloc.inc");

$current_user->check_employee();

global $reimbursementRequired, $expenseFormID;

$expenseForm = new expenseForm;
$transaction_to_edit = new transaction;


if ($expenseFormID) {
  $expenseForm->set_id($expenseFormID);
  $expenseForm->select();
}

if (!isset($reimbursementRequired)) {
  $reimbursementRequired = 0;
}

$expenseForm->read_globals();

$TPL["reimbursementRequired_checked"] = $expenseForm->get_value("reimbursementRequired") ? " checked" : "";

if ($approve) {
  $expenseForm->save();
  $expenseForm->set_status("approved");
  page_close();
  header("Location: ".$TPL["url_alloc_expenseFormList"]);
  exit();
}


if ($reject) {
  $expenseForm->save();
  $expenseForm->set_status("rejected");
  page_close();
  header("Location: ".$TPL["url_alloc_expenseFormList"]);
  exit();
}


if ($add) {

  $error = "";
  $product        or $error.= "You must enter a product.<br>";
  $companyDetails or $error.= "You must enter the company details.<br>";
  $tfID           or $error.= "You must enter the TF.<br>";
  $quantity       or $quantity = 1;

  if (!ereg("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $transactionDate)) {
    $error.= "You must enter the date incurred in the format yyyy-mm-dd (date entered '$transactionDate').<br>";
  }


  $amount = -$amount * $quantity;

  if ($amount == "") {
    $error.= "You must enter the price.<br>";
  } else if (!(is_float($amount) || is_int($amount))) {
    $error.= "You must enter a number for the price.<br>";
  } else if ($amount >= 0) {
    $error.= "You must enter a price greater than 0.<br>";
  }


  if (!$error) {
    $transaction = new transaction;
    $transaction->read_globals();
    $transaction->set_value("transactionType", "expense");

    if (!$expenseFormID) {
      $expenseForm->save();
      $transaction->set_value("expenseFormID", $expenseForm->get_id());
    } else {
      $expenseForm->set_id($expenseFormID);
    }

    $transaction->save();

  } else {
    $TPL["error"] = $error;
    $transaction_to_edit = $transaction;
  }
}

if ($edit) {
  $editFlag = true;
  $transaction_to_edit->set_id($transactionID);
  $transaction_to_edit->select();
}

$transaction_to_edit->set_tpl_values();

if ($transaction_to_edit->get_value("quantity")) {
  $TPL["amount"] = -$transaction_to_edit->get_value("amount") / $transaction_to_edit->get_value("quantity");
}

$db = new db_alloc;

if ($delete || $editFlag) {
  $query = sprintf("DELETE FROM transaction where transactionID=%d", $transactionID);
  $db->query($query);
  $editFlag = false;
  $expenseForm->set_id($expenseFormID);
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
$TPL["projectOptions"].= get_options_from_db($db, "projectName", "projectID", $selectProject);

$expenseForm->set_tpl_values();

$TPL["expenseFormID"] = $expenseForm->get_id();
$TPL["reimbursementRequired_checked"] = $expenseForm->get_value("reimbursementRequired") == 1 ? " checked" : "uhoh";
$TPL["paymentOptions"] = get_options_from_array(array("COD", "Cheque", "Company Amex Charge", "Company Amex Blue", "Other Credit Card", "Account", "Direct Deposit"), $expenseForm->get_value("paymentMethod"), false);


if ($expenseForm->get_value("expenseFormModifiedUser")) {
$db->query("select username from person where personID=".$expenseForm->get_value("expenseFormModifiedUser"));
$db->next_record();
$TPL["user"] = $db->f("username");
}


$formTotal = 0;


if ($save) {
  $expenseForm->read_globals();
  $expenseForm->set_value("expenseFormFinalised", 1);
  $expenseForm->save();
  header("location:".$TPL["url_alloc_expOneOff"]."&expenseFormID=$expenseFormID&edit=true");
  exit();
}


$TPL["error"] = $error;


if ($cancel) {
  if ($expenseFormID) {
    $query = sprintf("DELETE FROM expenseForm where expenseFormID=%d", $expenseFormID);
    $db->query($query);

    $query = sprintf("DELETE FROM transaction where expenseFormID=%d AND status='pending'", $expenseFormID);
    $db->query($query);

  }
  header("location:".$TPL["url_alloc_tfList"]);
}


if ($printVersion) {
  include_template("templates/exp-one-off-printableM.tpl");
} else {
  include_template("templates/exp-one-offM.tpl");
}

function show_all_exp($template) {

  global $TPL, $expenseForm, $db, $transactionID, $formTotal;

  if ($expenseForm->get_id()) {

    $transaction = new transaction;
    $tf = new tf;

    if ($transactionID && !$delete) {   // if edit is clicked
      $query = sprintf("SELECT * from transaction where expenseFormID=%d"." and transactionID<>%d"." order by transactionID desc", $expenseForm->get_id()
                       , $transactionID);
    } else {
      $query = sprintf("SELECT * from transaction where expenseFormID=%d"." order by transactionID desc", $expenseForm->get_id());
    }

    $db->query($query);

    while ($db->next_record()) {

      $transaction->read_db_record($db);
      $transaction->set_tpl_values();

      $TPL["amount"] = -$transaction->get_value("amount") / $transaction->get_value("quantity");
      $TPL["amount"] = number_format($TPL["amount"], 2);

      $TPL["formTotal"] -= $transaction->get_value("amount");

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
  global $auth, $db, $expenseForm, $allow_edit;

  $permissions = explode(",", $auth->auth["perm"]);

  if (!in_array("admin", $permissions) && !in_array("manage", $permissions)
      && isset($expenseForm) && $expenseForm->get_value("expenseFormFinalised") == 1) {
    $allow_edit = false;
  } else {
    $allow_edit = true;
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


function show_admin_buttons() {
  global $current_user, $expenseForm;

  if (have_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION)) {
    include_template("templates/expAdminButtonsS.tpl");
  }
}


page_close();




?>
