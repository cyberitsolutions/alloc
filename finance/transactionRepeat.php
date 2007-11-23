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

$current_user->check_employee();




$transactionRepeat = new transactionRepeat;
$db = new db_alloc;

global $TPL;
global $john, $transactionRepeatID;

$TPL["john"] = $john;

$transactionRepeatID = $_POST["transactionRepeatID"] or $transactionRepeatID = $_GET["transactionRepeatID"];

if ($transactionRepeatID) {
  $transactionRepeat->set_id($transactionRepeatID);
  $transactionRepeat->select();
  $transactionRepeat->set_tpl_values();
  $TPL["john"] = $tfID;
} else {
  $transactionRepeat = new transactionRepeat;
  $TPL["message_help"][] = "Complete all the details and click the Save button to create an automatically Repeating Expense";
}


if (!isset($_POST["reimbursementRequired"])) {
  $_POST["reimbursementRequired"] = 0;
}


if ($_POST["save"] || $_POST["delete"] || $_POST["pending"] || $_POST["approved"] || $_POST["rejected"]) {

  $transactionRepeat->read_globals();

  if (have_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION)) { 
    if ($_POST["pending"]) {
      $transactionRepeat->set_value("status","pending");
      $TPL["message_good"][] = "Repeating Expense form Pending.";
    } else if ($_POST["approved"]) {
      $transactionRepeat->set_value("status","approved");
      $TPL["message_good"][] = "Repeating Expense form Approved!";
    } else if ($_POST["rejected"]) {
      $transactionRepeat->set_value("status","rejected");
      $TPL["message_good"][] = "Repeating Expense form  Rejected.";
    }

  } else {
    $extra_get = "tfID=".$_POST["tfID"];
  }

  if ($_POST["delete"]) {

    if ($transactionRepeatID) {
      $transactionRepeat->set_id($transactionRepeatID);
      $transactionRepeat->delete();
      header("Location: ".$TPL["url_alloc_transactionRepeatList"].$extra_get);

    } else {
      header("Location: ".$TPL["url_alloc_tfList"]."tfID=".$extra_get);
    }
  }

  $_POST["product"] or $TPL["message"][].= "Please enter a Product";
  $_POST["amount"]  or $TPL["message"][].= "Please enter an Amount";
  $_POST["tfID"]    or $TPL["message"][].= "Please select a TF";
  $_POST["companyDetails"]  or $TPL["message"][].= "Please provide Company Details";
  $_POST["transactionType"] or $TPL["message"][].= "Please select a Transaction Type";

  if (!ereg("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $_POST["transactionStartDate"])) {
    $TPL["message"][].= "You must enter the Start date in the format yyyy-mm-dd ";
  }
  if (!ereg("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $_POST["transactionFinishDate"])) {
    $TPL["message"][].= "You must enter the Finish date in the format yyyy-mm-dd ";
  }

  if (!$TPL["message"]) {
    !$transactionRepeat->get_value("status") && $transactionRepeat->set_value("status","pending"); 
    $transactionRepeat->save();

    if ($_POST["save"]) {
      header("Location: ".$TPL["url_alloc_transactionRepeatList"].$extra_get);
    } 
  }
  $transactionRepeat->set_tpl_values();
}                       





$TPL["reimbursementRequired_checked"] = $transactionRepeat->get_value("reimbursementRequired") ? " checked" : "";

if ($transactionRepeat->get_value("transactionRepeatModifiedUser")) {
  $db->query("select username from person where personID=".$transactionRepeat->get_value("transactionRepeatModifiedUser"));
  $db->next_record();
  $TPL["user"] = $db->f("username");
}


if (have_entity_perm("tf", PERM_READ, $current_user, false)) {
  // Person can access all TF records
  $db->query("SELECT * FROM tf ORDER BY tfName");
} else if (have_entity_perm("tf", PERM_READ, $current_user, true)) {
  // Person can only read TF records that they own
  $db->query("select  * from tf,tfPerson where tfPerson.personID=".$current_user->get_id()." and tf.tfID=tfPerson.tfID order by tfName");
} else {
  die("No permissions to generate TF list");
}

$TPL["tfOptions"] = get_option("", "0", false)."\n";
$TPL["tfOptions"].= get_options_from_db($db, "tfName", "tfID", $transactionRepeat->get_value("tfID"));
$TPL["basisOptions"] = get_options_from_array(array("weekly", "fortnightly", "monthly", "quarterly", "yearly"), $transactionRepeat->get_value("paymentBasis"), false);
$TPL["transactionTypeOptions"] = get_select_options(transaction::get_transactionTypes(), $transactionRepeat->get_value("transactionType"));

if (is_object($transactionRepeat) && $transactionRepeat->get_id() && have_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION)) {
  $TPL["adminButtons"].= "&nbsp;<input type=\"submit\" name=\"pending\" value=\"Pending\">";
  $TPL["adminButtons"].= "&nbsp;<input type=\"submit\" name=\"approved\" value=\"Approve\">";
  $TPL["adminButtons"].= "&nbsp;<input type=\"submit\" name=\"rejected\" value=\"Reject\">";
}

if (is_object($transactionRepeat) && $transactionRepeat->get_id() && $transactionRepeat->get_value("status") == "pending") {
  $TPL["message_help"][] = "This Repeating Expense will only create Transactions once its status is Approved.";
}

$transactionRepeat->get_value("status") and $TPL["statusLabel"] = " - ".ucwords($transactionRepeat->get_value("status"));

$TPL["taxName"] = config::get_config_item("taxName");

$TPL["main_alloc_title"] = "Create Repeating Expense - ".APPLICATION_NAME;
include_template("templates/transactionRepeatM.tpl");

page_close();



?>
