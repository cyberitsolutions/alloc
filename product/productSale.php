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

$productSaleID = $_GET["productSaleID"] or $productSaleID = $_POST["productSaleID"];

$productSale = new productSale;
if ($productSaleID) {
  $productSale->set_id($productSaleID);
  $productSale->select();

} else {
  $TPL["status"] = "create";
}

// Not really used, but needed to help with the template
$taxRate = config::get_config_item("taxPercent") / 100.0;
$TPL["taxRate"] = $taxRate;


$db = new db_alloc;
// {{{ Helper functions for the template
function get_productSale_costs() {
  global $db, $productSale, $taxRate;
  $pctRows = array();
  $staticRows = array();
  $transactions = array();

  $query = sprintf("SELECT *,productSaleTransaction.description as description  FROM productSaleTransaction INNER JOIN productSaleItem ON productSaleItem.productSaleItemID = productSaleTransaction.productSaleItemID WHERE productSaleID = %d", $productSale->get_id());
  $db->query($query);

  while ($row = $db->next_record()) {
    if ($row["isPercentage"]) {
      $pctRows[$row["productSaleItemID"]][] = $row;
    } else {
      $staticRows[$row["productSaleItemID"]][] = $row;
    }
  }

  $query = sprintf("SELECT * FROM transaction INNER JOIN productSaleItem ON productSaleItem.productSaleItemID = transaction.productSaleItemID WHERE productSaleID = %d", $productSale->get_id());
  $db->query($query);
  while ($row = $db->next_record()) {
    $transactions[$row["productSaleItemID"]][] = $row;
  }

  //this query actually returns 3 description rows, make sure the final one is 
  //the right one
  $query = sprintf("SELECT *, productSaleItem.description as description FROM productSaleItem INNER JOIN product ON productSaleItem.productID = product.productID WHERE productSaleID = %d", $productSale->get_id());
  $db->query($query);
  $psItems = array();
  while ($row = $db->next_record()) {
    $total = productSaleItem::checkTotals($row["productSaleItemID"]);
    if ($total["fixed"] > 0) {
      $row["fixedErr"] = $total["fixed"];
    }
    // Don't divide by zero
    if ($taxRate != 0)
      $taxValue =  sprintf("%0.2f", $row["sellPrice"] / (1 + 1/($taxRate)));
    else
      $taxValue = 0;
    $row["tax"] = $taxValue;
    $row["margin"] = -$total["fixed"] - $taxValue; // Fix this to remove tax
    if ($total["pct"] != 100) {
      $row["pctRemaining"] = abs(100 - $total["pct"]);
      $row["pctRemainingText"] = ($total["pct"] < 100 ? "To allocate: " : "Overallocated: ");
    }
    if ($total["transactions"] != 0) {
      $row["txRemaining"] = -$total["transactions"];
    }
    $row["commission"] = -$total["fixed"];

    $psItems[] = $row;

    //It's possible that there will be no transactions for this item. Don't let 
    //PHP error out.
    is_array($transactions[$row["productSaleItemID"]]) or $transactions[$row["productSaleItemID"]] = array();
    is_array($staticRows[$row["productSaleItemID"]]) or $staticRows[$row["productSaleItemID"]] = array();
    is_array($pctRows[$row["productSaleItemID"]]) or $pctRows[$row["productSaleItemID"]] = array();


  }


  return array($psItems, $staticRows, $pctRows, $transactions);
}

$query = "SELECT * FROM tf";
$db->query($query);
$tfList = array();
$tfList[0] = "";
while ($row = $db->next_record()) {
  $tfList[$row["tfID"]] = $row["tfName"];
}

function tf_list($selected) {
  global $tfList;
  echo get_select_options($tfList, $selected);
}

function tf_name($selected) {
  global $tfList;
  echo $tfList[$selected];
}


function transaction_status_list($status) {
  $statusList = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
  echo get_select_options($statusList, $status);
}

function get_project_list() {
  global $db;
  $projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];
  $db->query("SELECT * FROM project");
  echo get_options_from_db($db, "projectName", "projectID", $projectID,60);
}

// }}} 

/// {{{ Code to respond to form buttons 
if ($_POST["create"]) {
  $productSale->set_value("projectID", $_POST["projectID"]);
  $productSale->set_value("status", "edit");
  $productSale->save(); // to generate an ID
  if (is_array($_POST["new_productID"])) {
    array_pop($_POST["new_productID"]);
    foreach ($_POST["new_productID"] as $idx => $prodID) {
      if (!$_POST["new_productID"][$idx]) {
        continue;
      }
      $psItem = new productSaleItem;
      $psItem->set_value("buyCost", $_POST["new_buycost"][$idx]);
      $psItem->set_value("sellPrice", $_POST["new_sellprice"][$idx]);
      $psItem->set_value("productID", $prodID);
      $psItem->set_value("quantity", $_POST["new_quantity"][$idx]);
      $psItem->set_value("description", $_POST["new_description"][$idx]);
      $psItem->set_value("productSaleID", $productSale->get_id());
      $psItem->save();
    }
  }
  $productSale->create_product_transactions();
  $TPL["status"] = "edit";
} else if ($_POST["save_transactions"] || $_POST["move_to_admin"] || $_POST["back_to_edit"] || $_POST["finish"] || $_POST["create_default_transactions"]) {
  $fail = 0;

  // Update psTransactions
  is_array($_POST["tfID"]) or $_POST["tfID"] = array();

  $staticSums = array();
  $pctSums = array();
  is_array($_POST["delete_cost"]) or $_POST["delete_cost"] = array();

  foreach ($_POST["tfID"] as $idx => $tfID) {
    // Don't bother saving if it's going to be deleted
    // More importantly, don't include in the amount totals
    if (array_key_exists($idx, $_POST["delete_cost"])) {
      continue;
    }
    $psTransaction = new productSaleTransaction;
    $psTransaction->set_id($idx);
    $psTransaction->select();
    $psTransaction->set_value("amount", $_POST["amount"][$idx]);
    $psTransaction->set_value("tfID", $tfID);
    $psTransaction->set_value("description", $_POST["description"][$idx]);
    // check productSale status first
    
    // Use $fail as a bitfield to prevent duplicate errors
    if (!$tfID && !($fail & 1)){
      $error[] = "All TFs must be set (not blank).";
      $fail |= 1;
    }

    if ($psTransaction->get_value("isPercentage")) {
      $pctSums[$psTransaction->get_value("productSaleItemID")] += $psTransaction->get_value("amount");
    } else {
       $staticSums[$psTransaction->get_value("productSaleItemID")] += $psTransaction->get_value("amount");
    }
    $psTransaction->save();
  }

  foreach ($_POST["delete_cost"] as $idx => $dummy) {
    $psTransaction = new productSaleTransaction;
    $psTransaction->set_id($idx);
    $psTransaction->select();
    $psTransaction->delete();
  }

  // Create new psTransactions
  is_array($_POST["new_transaction_itemID"]) or $_POST["new_transaction_itemID"] = array();
  foreach ($_POST["new_transaction_itemID"] as $idx => $psItemID) {
    if ($_POST["new_amount"][$idx] == "") {
      continue;
    }

    $productSale->create_psTransaction($psItemID, $_POST["new_tfID"][$idx], $_POST["new_amount"][$idx], $_POST["new_isPercent"][$idx], $_POST["new_description"][$idx]);

    if ($_POST["new_isPercent"][$idx]) {
      $pctSums[$psItemID] += $_POST["new_amount"][$idx];
    } else {
      $staticSums[$psItemID] += $_POST["new_amount"][$idx];
    }
  }

  if ($productSale->get_value("status") == "admin") {
    // Handle real transactions
    is_array($_POST["tx_delete"]) or $_POST["tx_delete"] = array();
    $txSums = array();

    if (is_array($_POST["tx_tfID"])) {
      foreach ($_POST["tx_tfID"] as $idx => $tfID) {
        if (array_key_exists($idx, $_POST["tx_delete"]))
          continue;
        $transaction = new transaction;
        $transaction->set_id($idx);
        $transaction->select();
        $transaction->set_value("tfID", $tfID);
        $transaction->set_value("amount", $_POST["tx_amount"][$idx]);
        $transaction->set_value("product", $_POST["tx_description"][$idx]);
        $transaction->set_value("status", $_POST["tx_status"][$idx]);
        $transaction->save();
        $txSums[$transaction->get_value("productSaleItemID")] += $_POST["tx_amount"][$idx];
      }
    }

    foreach ($_POST["tx_delete"] as $idx => $dummy) {
      $transaction = new transaction;
      $transaction->set_id($idx);
      $transaction->select();
      $transaction->delete();
    }

    if (is_array($_POST["new_tx_itemID"])) {
      foreach ($_POST["new_tx_itemID"] as $idx => $psItemID) {
        if ($_POST["new_tx_amount"][$idx] == 0)
          continue; //Skip over the blank new transaction 
        $transaction = new transaction;
        $transaction->set_value("productSaleItemID", $psItemID);
        $transaction->set_value("amount", $_POST["new_tx_amount"][$idx]);
        $transaction->set_value("status", $_POST["new_tx_status"][$idx]);
        $transaction->set_value("tfID", $_POST["new_tx_tfID"][$idx]);
        $transaction->set_value("transactionType", "product");
        $transaction->set_value("transactionDate", date("Y-m-d"));
        $transaction->set_value("product", $_POST["new_tx_description"][$idx]);
        $transaction->save();
        $txSums[$psItemID] += $_POST["new_tx_amount"][$idx];
      }
    }

    // damn float math
    if (abs(array_sum($txSums)) > 1.0e-4) {
      $fail |= 8;
      $error[] = "Transactions for each item must sum to 0.";
    }
  }

  foreach ($pctSums as $sum) {
    if ($sum != 100) {
      $fail |= 2;
      $error[] = "Percentages for each item must sum to 100%.";
      break;
     }
  }
  foreach ($staticSums as $sum) {
    if ($sum > 0) {
      $fail |= 4;
      $error[] = "Static costs for at least one item have been over allocated.";
      break;
    }
  }

  if ($_POST["move_to_admin"]) {
    if ($fail) {
      $TPL["message"] = $error;
    } else {
      $productSale->move_forward();
      $productSale->save();
    }
  }
  if ($_POST["back_to_edit"]) {
    $productSale->move_forward();
    $productSale->save();
  }
  if ($_POST["finish"]) {
    if ($fail) {
      $TPL["message"] = $error;
    } else {
      $productSale->move_forward();
      $productSale->save();
    }
  }
  if (is_array($_POST["create_default_transactions"])) {
    //Array should have only one element, but there doesn't seem to be a more straightforward way to retrieve the psItemID
    foreach ($_POST["create_default_transactions"] as $psItemID => $dummy) {
      $psItem = new productSaleItem;
      $psItem->set_id($psItemID);
      $psItem->select();
      $psItem->create_transactions();
    }
  }
} else if (is_array($_POST["delete_all_transactions"])) {
  foreach ($_POST["delete_all_transactions"] as $psItemID => $dummy) {
    $psItem = new productSaleItem;
    $psItem->set_id($psItemID);
    $psItem->select();
    $psItem->delete_transactions();
  }
} else if ($_POST["delete_productSale"]) {
  $projectID = $productSale->get_value("projectID");
  $productSale->delete();
  header("Location: ".$TPL["url_alloc_project"]."projectID=".$projectID);
} else if ($_POST["back_to_admin"]) {
  $productSale->set_value("status", "admin");
  $productSale->save();
}
// }}} 

$query = "SELECT * FROM product";
$db->query($query);

if ($productSaleID) {
  $TPL["status"] = $productSale->get_value("status");
}

$statuses = array("create"=>"Create", "edit"=>"Edit", "admin"=>"Administrator", "finished"=>"Finished");

// no circumfix operator
$statuses[$TPL["status"]] = "<b>".$statuses[$TPL["status"]]."</b>";
$TPL["statusText"] = implode(" | ", $statuses);

$TPL["productList_dropdown"] = get_options_from_db($db, "productName", "productID", 0);
$TPL["productSaleID"] = $productSale->get_id();
$TPL["taxName"] = config::get_config_item("taxName");
$TPL["tax_tfID"] = config::get_config_item("taxTfID");


$showCosts = $_POST["showCosts"] or $_showCosts = $_GET["showCosts"];

if ($productSale->get_value("status") == "admin") {
  if ($showCosts)
    $TPL["showCosts"] = true;
  $tx = new transaction;
}
if ($current_user->have_role("admin")) {
  $TPL["editTransactions"] = true;
} else {
  $TPL["editTransactions"] = false;
}


$TPL["main_alloc_title"] = "Product Sale";
include_template("templates/productSaleM.tpl");
page_close();


