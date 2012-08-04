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

function show_transaction_list($template) {
  global $TPL;
  global $tflist;
  global $transactionGroupID;

  $q = prepare("SELECT *, amount * pow(10,-currencyType.numberToBasic) as amount
                  FROM transaction
             LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                 WHERE transactionGroupID = %d
              ORDER BY transactionID
               ",$transactionGroupID);
  $db = new db_alloc();
  $db->query($q);

  while ($row = $db->row()) {
    $transaction = new transaction();
    $transaction->read_array($row);
    $transaction->set_values();

    $tflist = add_inactive_tf($transaction->get_value("tfID"), $tflist);
    $tflist = add_inactive_tf($transaction->get_value("fromTfID"), $tflist);

    $TPL["display"] = "";
    $TPL["tfList_dropdown"] = page::select_options($tflist, $transaction->get_value("tfID"),500);
    $TPL["fromTfList_dropdown"] = page::select_options($tflist, $transaction->get_value("fromTfID"),500);
    $TPL["transactionType_dropdown"] = page::select_options(transaction::get_transactionTypes(), $transaction->get_value("transactionType"));
    $TPL["status_dropdown"] = page::select_options(transaction::get_transactionStatii(),$transaction->get_value("status"));
    $TPL["link"] = $transaction->get_link("transactionID");
    include_template($template);
  }
}

function show_transaction_new($template) {
  global $TPL;
  global $tflist;
  $transaction = new transaction();
  $transaction->set_values(); // wipe clean
  $TPL["display"] = "display:none";
  $TPL["tfList_dropdown"] = page::select_options($tflist,NULL,500);
  $TPL["fromTfList_dropdown"] = page::select_options($tflist,NULL,500);
  $TPL["transactionType_dropdown"] = page::select_options(transaction::get_transactionTypes());
  $TPL["status_dropdown"] = page::select_options(transaction::get_transactionStatii());
  $TPL["link"] = "";
  include_template($template);
}

function add_inactive_tf($tfID, $options) {
  // add a tf to the array of options, if it's not already there
  global $TPL;
  if($tfID && !array_key_exists($tfID, $options)) {
    $tf = new tf();
    $tf->set_id($tfID);
    $tf->select();
    $options[$tfID] = $tf->get_value("tfName");
  }
  return $options;
}

$tf = new tf();
$tflist = $tf->get_assoc_array("tfID","tfName");
$transactionGroupID = $_POST["transactionGroupID"] or $transactionGroupID = $_GET["transactionGroupID"];
if (!$transactionGroupID) {
  $transactionGroupID = transaction::get_next_transactionGroupID();
}
$TPL["transactionGroupID"] = $transactionGroupID;


if ($_POST["save_transactions"]) {
  is_array($_POST["deleteTransaction"]) or $_POST["deleteTransaction"] = array();

  if (is_array($_POST["transactionID"]) && count($_POST["transactionID"])) {

    foreach ($_POST["transactionID"] as $k => $transactionID) {
      // Delete
      if (in_array($transactionID, $_POST["deleteTransaction"])) {
        $transaction = new transaction();
        $transaction->set_id($transactionID);
        $transaction->select();
        $transaction->delete();
        $deleted.= $commar1.$transactionID;
        $commar1 = ", ";

      // Save
      } else if ($_POST["amount"][$k]) {
        $a = array("amount"             => $_POST["amount"][$k]
                  ,"tfID"               => $_POST["tfID"][$k]
                  ,"fromTfID"           => $_POST["fromTfID"][$k]
                  ,"product"            => $_POST["product"][$k]
                  ,"description"        => $_POST["description"][$k]
                  ,"transactionType"    => $_POST["transactionType"][$k]
                  ,"transactionDate"    => $_POST["transactionDate"][$k]
                  ,"status"             => $_POST["status"][$k]
                  ,"transactionGroupID" => $transactionGroupID
                  ,"transactionID"      => $_POST["transactionID"][$k]);

        $transaction = new transaction();
        if ($_POST["transactionID"][$k]) {
          $transaction->set_id($_POST["transactionID"][$k]);
          $transaction->select();
        }
        $transaction->read_array($a);
        $v = $transaction->validate();
        if ($v == "") {
          $transaction->save();
          $saved.= $commar2.$transaction->get_id();
          $commar2 = ", ";
        } else {
          alloc_error(implode("<br>",$v));
        }
      }
    }
  }
  $saved and $TPL["message_good"][] = "Transaction ".$saved." saved.";
  $deleted and $TPL["message_good"][] = "Transaction ".$deleted." deleted.";
  alloc_redirect($TPL["url_alloc_transactionGroup"]."transactionGroupID=".$transactionGroupID);
}


$TPL["main_alloc_title"] = "Edit Transactions - ".APPLICATION_NAME;
include_template("templates/transactionGroupM.tpl");
?>
