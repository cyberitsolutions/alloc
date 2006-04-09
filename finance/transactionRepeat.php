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




$transactionRepeat = new transactionRepeat;
$db = new db_alloc;

global $TPL, $transactionRepeat, $reimbursementRequired, $transactionStartDate;
global $transactionFinishDate, $amount, $product, $tfID, $companyDetails, $transactionRepeatModifiedUser;
global $john, $transactionRepeatID;

$TPL["john"] = $john;

if ($transactionRepeatID) {
  $transactionRepeat->set_id($transactionRepeatID);
  $transactionRepeat->select();
  $transactionRepeat->set_tpl_values();
  $TPL["john"] = $tfID;
}



if (!isset($reimbursementRequired)) {
  $reimbursementRequired = 0;
}


if ($save) {

  $transactionRepeat = new transactionRepeat;
  $transactionRepeat->read_globals();

  $error = '';


  // have lots of error checking between here=============================================

  if ($product == "") {
    $error.= "You must enter a 'Product' <br>";
  }
  if ($amount == "") {
    $error.= "You must enter a 'Amount'. <br>";
  }
  if ($tfID == 0) {
    $error.= "You must select a 'TF'. <br>";
  }
  if (!ereg("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $transactionStartDate)) {
    $error.= "You must enter the Start date in the format yyyy-mm-dd ";
    $error.= "(date entered '$transactionStartDate').<br>";
  }
  if (!ereg("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $transactionFinishDate)) {
    $error.= "You must enter the Finish date in the format yyyy-mm-dd ";
    $error.= "(date entered '$transactionFinishDate').<br>";
  }
  if ($companyDetails == "") {
    $error.= "You must provide 'Company Details'. <br>";
  }
  if ($dateEntered == "") {
    $error.= "You must enter a 'Date Incurred'. <br>";
  }
  // And here...===========================================================================


  if (!$error) {
    $transactionRepeat->set_value("transactionType", "expense");
    $transactionRepeat->save();
  }



  $TPL["error"] = $error;
  $transactionRepeat->set_tpl_values();
}                               // END OF IF-SAVE

if ($delete) {

  if ($transactionRepeatID) {

    $transactionRepeat->set_id($transactionRepeatID);
    $transactionRepeat->delete();
    header("Location: ".$TPL["url_alloc_transactionRepeatList"]."tfID=$tfID");

  } else {
    header("Location: ".$TPL["url_alloc_tfList"]."tfID=$tfID");
  }
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


include_template("templates/transactionRepeatM.tpl");

page_close();



?>
