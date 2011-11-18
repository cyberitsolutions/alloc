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

function tf_list($selected) {
  global $tfList;
  echo page::select_options($tfList, $selected);
}

function tf_name($selected) {
  global $tfList;
  echo $tfList[$selected];
}

function transaction_status_list($status) {
  $statusList = transaction::get_transactionStatii();
  echo page::select_options($statusList, $status);
}

function show_productSale_list($productSaleID, $template) {
  global $TPL, $productSaleItemsDoExist;

  $productSale = new productSale;
  $productSale->set_id($productSaleID);
  $productSale->select();
  $productSale->set_tpl_values();

  $taxName = config::get_config_item("taxName");

  $product = new product;
  $ops = $product->get_assoc_array("productID","productName");
  $query = sprintf("SELECT *
                      FROM productSaleItem 
                     WHERE productSaleID = %d"
                  , $productSaleID);
  $db = new db_alloc();
  $db->query($query);
  while ($db->next_record()) {
    $productSaleItemsDoExist = true;
    $productSaleItem = new productSaleItem;
    $productSaleItem->read_db_record($db);
    $productSaleItem->set_tpl_values();

    $TPL["itemSellPrice"] = $productSaleItem->get_value("sellPrice");
    $TPL["itemMargin"] = $productSaleItem->get_amount_margin();
    $TPL["itemSpent"] = $productSaleItem->get_amount_spent();
    $TPL["itemEarnt"] = $productSaleItem->get_amount_earnt();
    $TPL["itemOther"] = $productSaleItem->get_amount_other();
    $TPL["itemCosts"] = page::money(config::get_config_item("currency"),product::get_buy_cost($productSaleItem->get_value("productID")) * $productSaleItem->get_value("quantity"),"%s%mo %c");
    $TPL["itemTotalUnallocated"] = $productSaleItem->get_amount_unallocated();

    $TPL["productList_dropdown"] = page::select_options($ops, $productSaleItem->get_value("productID"));
    $TPL["productLink"] = "<a href=\"".$TPL["url_alloc_product"]."productID=".$productSaleItem->get_value("productID")."\">".page::htmlentities($ops[$productSaleItem->get_value("productID")])."</a>";
    $TPL["transactions"] = $productSale->get_transactions($productSaleItem->get_id());
 
    if ($taxName) {
      $TPL["sellPriceTax_check"] = sprintf(" <input type='checkbox' name='sellPriceIncTax[]' value='%d'%s> inc %s"
                                      ,$productSaleItem->get_id(),$productSaleItem->get_value("sellPriceIncTax") ? ' checked':'',$taxName);
      $TPL["sellPriceTax_label"] = $productSaleItem->get_value("sellPriceIncTax") ? " inc ".$taxName : " ex ".$taxName;
    }
   include_template($template);
  }
}

function show_productSale_new($template) {
  global $TPL, $productSaleItemsDoExist, $productSaleID;
  $taxName = config::get_config_item("taxName");
  $productSaleItem = new productSaleItem;
  $productSaleItem->set_values(); // wipe clean
  $product = new product;
  $ops = $product->get_assoc_array("productID","productName");
  $TPL["productList_dropdown"] = page::select_options($ops, $productSaleItem->get_value("productID"));
  $productSaleItemsDoExist and $TPL["display"] = "display:none";
  if ($taxName) {
    $TPL["sellPriceTax_check"] = sprintf(" <input type='checkbox' name='sellPriceIncTax[]' value='1'%s> inc %s"
                                      ,$productSaleItem->get_value("sellPriceIncTax") ? ' checked':'',$taxName);
  }
  $TPL["psid"] = $productSaleID; // poorly named template variable to prevent clobbering
  include_template($template);
}

function show_transaction_list($transactions=array(), $template) {
  global $TPL, $tflist;
  foreach ($transactions as $row) {
    $transaction = new transaction;
    $transaction->read_array($row);
    $transaction->set_values();
    $TPL["display"] = "";


    $m = new meta("currencyType");
    $currencyOptions = $m->get_assoc_array("currencyTypeID", "currencyTypeID");
    $TPL["currencyOptions"] = page::select_options($currencyOptions, $transaction->get_value("currencyTypeID"));
    $TPL["tfList_dropdown"] = page::select_options($tflist, $transaction->get_value("tfID"));
    $TPL["fromTfList_dropdown"] = page::select_options($tflist, $transaction->get_value("fromTfID"));
    $TPL["tfID_label"] = $tflist[$transaction->get_value("tfID")];
    $TPL["fromTfID_label"] = $tflist[$transaction->get_value("fromTfID")];
    if (CAN_APPROVE_TRANSACTIONS && $TPL["productSale_status"] == "admin") {
      $TPL["status"] = "<select name='status[]' class='txStatus'>";
      $TPL["status"].= page::select_options(transaction::get_transactionStatii(),$transaction->get_value("status"))."</select>";
    }

    $TPL["pc_productCostID"] = $row["pc_productCostID"];
    $TPL["pc_amount"]        = $row["pc_amount"];   
    $TPL["pc_isPercentage"]  = $row["pc_isPercentage"];
    $TPL["pc_currency"]      = $row["pc_currency"];
    $TPL["amountClass"]      = $row["saleTransactionType"];
    include_template($template);
  }
}

function show_transaction_new($template) {
  global $TPL, $tflist;
  $transaction = new transaction;
  $transaction->set_values(); // wipe clean
  $TPL["display"] = "display:none";
  $m = new meta("currencyType");
  $currencyOptions = $m->get_assoc_array("currencyTypeID", "currencyTypeID");
  $TPL["currencyOptions"] = page::select_options($currencyOptions);
  $TPL["tfList_dropdown"] = page::select_options($tflist);
  $TPL["fromTfList_dropdown"] = page::select_options($tflist);
  if (CAN_APPROVE_TRANSACTIONS && $TPL["productSale_status"] == "admin") {
    $TPL["status"] = "<select name='status[]' class='txStatus'>";
    $TPL["status"].= page::select_options(transaction::get_transactionStatii())."</select>";
  }
  $TPL["pc_productCostID"] = '';
  $TPL["pc_amount"]        = '';
  $TPL["pc_isPercentage"]  = '';
  $TPL["pc_currency"]      = '';
  $TPL["amountClass"]      = '';
  include_template($template);
}


$productID = $_GET["productID"] or $productID = $_POST["productID"];
$productSaleID = $_GET["productSaleID"] or $productSaleID = $_POST["productSaleID"];
$projectID = $_GET["projectID"] or $projectID = $_POST["projectID"];
$clientID = $_GET["clientID"] or $clientID = $_POST["clientID"];
$TPL["projectID"] = $projectID;

$productSale = new productSale;
$productSale->read_globals();
if ($productSaleID) {
  $productSale->set_id($productSaleID);
  $productSale->select();
  $productSale->set_values();
  $clientID = $productSale->get_value("clientID");
  $projectID = $productSale->get_value("projectID");
  $productSaleID = $productSale->get_id();

} else {
  $TPL["status"] = "create";
}


$db = new db_alloc;
$tf = new tf();
$tflist = $tf->get_assoc_array("tfID","tfName");


if ($_POST["move_forwards"]) {
  $productSale->move_forwards();
  $_POST["save"] = true;

} else if ($_POST["move_backwards"]) {
  $productSale->move_backwards();
  $_POST["save"] = true;
}


// Code to respond to form buttons 
if ($_POST["save"]) {
  !$productSaleID && $productSale->set_value("status", "edit");
  $productSale->read_globals();
  $productSale->save(); 
  $productSaleID = $productSale->get_id();
  alloc_redirect($TPL["url_alloc_productSale"]."productSaleID=".$productSaleID);

} else if ($_POST["save_items"] && $productSaleID) {
  
  is_array($_POST["deleteProductSaleItem"]) or $_POST["deleteProductSaleItem"] = array();

  if (is_array($_POST["productSaleItemID"]) && count($_POST["productSaleItemID"])) {
    is_array($_POST["sellPriceIncTax"]) or $_POST["sellPriceIncTax"] = array();

    foreach ($_POST["productSaleItemID"] as $k => $productSaleItemID) {
      // Delete
      if (in_array($productSaleItemID, $_POST["deleteProductSaleItem"])) {
        $productSaleItem = new productSaleItem;
        $productSaleItem->set_id($productSaleItemID);
        $productSaleItem->delete();

      // Save
      } else {
        $a = array("productID"=>$_POST["productID"][$k]
                  ,"sellPrice"=>$_POST["sellPrice"][$k]
                  ,"sellPriceCurrencyTypeID"=>$_POST["sellPriceCurrencyTypeID"][$k]
                  ,"quantity"=>$_POST["quantity"][$k]
                  ,"description"=>$_POST["description"][$k]
                  ,"productSaleID"=>$productSaleID);

        if ($productSaleItemID) {
          $a["sellPriceIncTax"] = sprintf("%d",in_array($productSaleItemID, $_POST["sellPriceIncTax"]));
        } else {
          $a["sellPriceIncTax"] = sprintf("%d",isset($_POST["sellPriceIncTax"][$k]));
        }

        if(substr($productSaleItemID, 0, 3) == "new") {
          $productSaleItemID = "";
        }
        $a["productSaleItemID"] = $productSaleItemID;

        $productSaleItem = new productSaleItem;
        $productSaleItem->read_array($a);
        if ($productSaleItem->validate() == "") {
          $productSaleItem->save();
        }
      }
    }
  } 
  alloc_redirect($TPL["url_alloc_productSale"]."productSaleID=".$productSaleID);


} else if ($_POST["save_transactions"]) {
   
  is_array($_POST["deleteTransaction"]) or $_POST["deleteTransaction"] = array();

  if (is_array($_POST["transactionID"]) && count($_POST["transactionID"])) {

    foreach ($_POST["transactionID"] as $k => $transactionID) {
      // Delete
      if (in_array($transactionID, $_POST["deleteTransaction"])) {
        $transaction = new transaction;
        $transaction->set_id($transactionID);
        $transaction->select();
        $transaction->delete();

      // Save
      } else if (imp($_POST["amount"][$k])) {

        $type = $_POST["transactionType"][$k] or $type = 'sale';

        $a = array("amount"            => $_POST["amount"][$k]
                  ,"tfID"              => $_POST["tfID"][$k]
                  ,"fromTfID"          => $_POST["fromTfID"][$k]
                  ,"product"           => $_POST["product"][$k]
                  ,"description"       => $_POST["description"][$k]
                  ,"productSaleID"     => $productSaleID
                  ,"productSaleItemID" => $_POST["productSaleItemID"]
                  ,"productCostID"     => $_POST["productCostID"][$k]
                  ,"transactionType"   => $type
                  ,"transactionDate"   => date("Y-m-d")
                  ,"status"            => 'pending'
                  ,"currencyTypeID"    => $_POST["currencyTypeID"][$k]
                  ,"transactionID"     => $transactionID);
  
        if (CAN_APPROVE_TRANSACTIONS && $_POST["status"][$k]) {
          $a["status"] = $_POST["status"][$k];
        }

        $transaction = new transaction;
        $transaction->read_array($a);
        if ($transaction->validate() == "") {
          $transaction->save();
        }
      }
    }
  } 
  alloc_redirect($TPL["url_alloc_productSale"]."productSaleID=".$productSaleID);


} else if ($_POST["create_default_transactions"] && $_POST["productSaleItemID"]) {
  $productSaleItem = new productSaleItem();
  $productSaleItem->set_id($_POST["productSaleItemID"]);
  $productSaleItem->select();
  $productSaleItem->create_transactions();

} else if ($_POST["delete_transactions"] && $_POST["productSaleItemID"]) {
  $productSaleItem = new productSaleItem();
  $productSaleItem->set_id($_POST["productSaleItemID"]);
  $productSaleItem->select();
  $productSaleItem->delete_transactions();

} else if ($_POST["delete_productSale"]) {
  $productSale->delete();
  alloc_redirect($TPL["url_alloc_productSaleList"]);
}


if ($productSale->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
  define("CAN_APPROVE_TRANSACTIONS",1);
} else {
  define("CAN_APPROVE_TRANSACTIONS",0);
}

$statuses = productSale::get_statii();
$statuses[$TPL["status"]] = "<b>".$statuses[$TPL["status"]]."</b>";
$TPL["statusText"] = implode("&nbsp;&nbsp;|&nbsp;&nbsp;", $statuses);
$TPL["productSaleID"] = $productSale->get_id();

$showCosts = $_POST["showCosts"] or $_showCosts = $_GET["showCosts"];

$productSale->set_values();


list($client_select, $client_link, $project_select, $project_link) 
  = client::get_client_and_project_dropdowns_and_links($clientID, $projectID);

$TPL["show_client_options"] = $client_link;
$TPL["show_project_options"] = $project_link;
$TPL["show_person_options"] = person::get_fullname($productSale->get_value("personID"));
$TPL["show_date"] = $productSale->get_value("productSaleDate");

if (!$productSale->get_id() || $productSale->get_value("status") != "finished" && !($productSale->get_value("status") == "admin" && !CAN_APPROVE_TRANSACTIONS)) {
  $TPL["show_client_options"] = $client_select;
  $TPL["show_project_options"] = $project_select;

  $personID = $productSale->get_value("personID") or $personID = $current_user->get_id();
  $TPL["show_person_options"] = "<select name='personID'>".page::select_options(person::get_username_list($personID), $personID)."</select>";

  $TPL["show_date"] = page::calendar("productSaleDate", $productSale->get_value("productSaleDate"));
}


$TPL["productSale_status"] = $productSale->get_value("status");

$amounts = $productSale->get_amounts();
$TPL = array_merge($TPL,$amounts);

define("DISPLAY_PRODUCT_SALE_ITEM_EDIT",1);
define("DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_EDIT",2);
define("DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_VIEW",3);
define("DISPLAY_PRODUCT_SALE_EDIT",4);

// Show line item edit
$productSaleID = $productSale->get_id();
$status = $productSale->get_value("status");
if ($productSaleID && $status == "edit") {
  define("DISPLAY",DISPLAY_PRODUCT_SALE_ITEM_EDIT);

// Show line item + transaction + edit
} else if ($productSaleID && ($status == "allocate" || ($status == "admin" && $productSale->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)))) {
  define("DISPLAY",DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_EDIT);

// Show line item + transaction + view
} else if ($productSaleID && ($status == "finished" || !$productSale->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS))) {
  define("DISPLAY",DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_VIEW);

} else {
  define("DISPLAY",0);
}

if (!$productSale->get_id()) {
  $TPL["message_help"][] = "To create a new Sale, optionally select a Client and/or Project and click the Create Sale button.";
} else if ($productSale->get_value("status") == "edit") {
  $TPL["message_help"][] = "Add as many Sale Items as you like by clicking the 'New' link multiple times, and then clicking the
                            Save Items button.<br><br>When you are done adding Sale Items click the 'Allocate -->' button
                            to setup the resulting transactions from this Sale.";

} else if ($productSale->get_value("status") == "allocate") {
  $TPL["message_help"][] = "If necessary, adjust the transactions below. When you're done, submit this Sale
                            to the Adminstrator, you will no longer be able to edit this Sale.

                            <br><br>Generally, the transactions for the Sale Items will add up correctly if the
                            <b>Sell Price</b> is equal to <b>Transactions Incoming</b>, and the <b>Margin</b> is
                            equal to <b>Transactions Other</b>.";


} else if ($productSale->get_value("status") == "admin" && $productSale->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
  $TPL["message_help"][] = "Please review the Sale Transactions carefully. If accurate <b>approve</b> and save the transactions,
                            then move this Sale to status 'Completed'.";
} else if ($productSale->get_value("status") == "admin") {
  $TPL["message_help"][] = "This Sale is awaiting approval from the Administrator.";
}


if ($productSale->get_value("projectID")) {
  $project = new project();
  $project->set_id($productSale->get_value("projectID"));
  $project->select();
  $ptf = $project->get_value("cost_centre_tfID");
  $ptf and $TPL["project_tfID"] = " (TF: ".tf::get_name($ptf).")";
  $ptf or $TPL["project_tfID"] = " (No project TF)";
}


$TPL["main_alloc_title"] = "Sale";
include_template("templates/productSaleM.tpl");

?>
