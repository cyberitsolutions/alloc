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

function show_productCost_list($productID, $template, $percent = false) {
  global $TPL;
  unset($TPL["display"],$TPL["taxOptions"]); // otherwise the commissions don't display.
  if ($productID) {

    $t = new meta("currencyType");
    $currency_array = $t->get_assoc_array("currencyTypeID","currencyTypeID");

    $db = new db_alloc();
    $query = prepare("SELECT * 
                        FROM productCost 
                       WHERE productID = %d 
                         AND isPercentage = %d
                         AND productCostActive = true
                    ORDER BY productCostID"
                    ,$productID, $percent);
    $db->query($query);
    while ($db->next_record()) {
      $productCost = new productCost();
      $productCost->read_db_record($db);
      $productCost->set_tpl_values();
      $TPL["currencyOptions"] = page::select_options($currency_array,$productCost->get_value("currencyTypeID"));
      $TPL["taxOptions"] = page::select_options(array(""=>"Exempt",1=>"Included",0=>"Excluded"),$productCost->get_value("tax"));

      // Hardcoded AUD because productCost table uses percent and dollars in same field
      $percent and $TPL["amount"] = page::money("AUD",$productCost->get_value("amount"),"%mo");
      include_template($template);
    }
  }
}

function show_productCost_new($template, $percent = false) {
  global $TPL;
  $t = new meta("currencyType");
  $currency_array = $t->get_assoc_array("currencyTypeID","currencyTypeID");
  $productCost = new productCost();
  $productCost->set_values(); // wipe clean
  $TPL["currencyOptions"] = page::select_options($currency_array,$productCost->get_value("currencyTypeID"));
  $TPL["taxOptions"] = page::select_options(array(""=>"Exempt",1=>"Included",0=>"Excluded"),"1");
  $TPL["display"] = "display:none";
  include_template($template);
}

function tf_list($selected="",$remove_these=array()) {
  global $tflist;
  $temp = $tflist;
  foreach ($remove_these as $dud) {
    unset($temp[$dud]);
  }
  echo page::select_options($temp, $selected);
  return;
}

$productID = $_GET["productID"] or $productID = $_POST["productID"];
$product = new product();

if ($productID) {
  $product->set_id($productID);
  $product->select();
}

$tf = new tf();
$tflist = $tf->get_assoc_array("tfID","tfName");
$extra_options = array(
                       //"-3"=>"META: Sale TF"
                      "-1"=>"META: Project TF"
                      ,"-2"=>"META: Salesperson TF"
                      ,config::get_config_item("mainTfID") => "Main Finance TF (".tf::get_name(config::get_config_item("mainTfID")).")"
                      ,config::get_config_item("outTfID") => "Outgoing Funds TF (".tf::get_name(config::get_config_item("outTfID")).")"
                      ,config::get_config_item("inTfID") => "Incoming Funds TF (".tf::get_name(config::get_config_item("inTfID")).")"
                      );
// Prepend the META options to the tflist.
$tflist = $extra_options + $tflist; 

$TPL["companyTF"] = $tflist[config::get_config_item("mainTfID")];
$TPL["taxTF"] = $tflist[config::get_config_item("taxTfID")];
$taxRate = config::get_config_item("taxPercent") / 100.0;
$TPL["taxRate"] = $taxRate;


if ($_POST["save"]) {
  $product->read_globals();
  $product->set_value("productActive", isset($_POST["productActive"]) ? 1 : 0);
  !$product->get_value("productName") and alloc_error("Please enter a Product Name.");
  !$product->get_value("sellPrice")   and alloc_error("Please enter a Sell Price.");

  if (!$TPL["message"]) {
    $product->save();
    $productID = $product->get_id();

    // If they were in the middle of creating a sale, return them back there
    if ($_REQUEST["productSaleID"]) {
      alloc_redirect($TPL["url_alloc_productSale"]."productSaleID=".$_REQUEST["productSaleID"]);
    } else {
      alloc_redirect($TPL["url_alloc_product"]."productID=".$productID);
    }
  }
  $product->set_values();

} else if ($_POST["delete"]) {
  $product->read_globals();
  $product->delete();
  alloc_redirect($TPL["url_alloc_productList"]);
}


# Fixed costs
if ($_POST["save_costs"] || $_POST["save_commissions"]) {

  foreach ((array)$_POST["productCostID"] as $k => $productCostID) {
    // Delete
    if (in_array($productCostID, (array)$_POST["deleteCost"])) {
      $productCost = new productCost();
      $productCost->set_id($productCostID);
      $productCost->select();
      $productCost->delete();

    // Save
    } else if (imp($_POST["amount"][$k])) {

      $a = array("productCostID"=>$productCostID
                ,"productID"=>$productID
                ,"tfID"=>$_POST["tfID"][$k]
                ,"amount"=>$_POST["amount"][$k]
                ,"isPercentage"=>$_POST["save_commissions"] ? 1 : 0
                ,"description"=>$_POST["description"][$k]
                ,"currencyTypeID"=>$_POST["currencyTypeID"][$k] ? $_POST["currencyTypeID"][$k] : config::get_config_item("currency")
                ,"tax"=>$_POST["tax"][$k]
                ,"productCostActive"=>1
                );

      // Hardcode AUD for commissions because productCost table uses percent and dollars in same field
      $_POST["save_commissions"] and $a["currencyTypeID"] = "AUD";

      $productCost = new productCost();
      $productCost->read_array($a);
      //$errs = $productCost->validate();
      if (!$errs) {
        $productCost->save();
      }
    }
  }
  alloc_redirect($TPL["url_alloc_product"]."productID=".$product->get_id());
}

$m = new meta("currencyType");
$ops = $m->get_assoc_array("currencyTypeID","currencyTypeID");
$TPL["sellPriceCurrencyOptions"] = page::select_options($ops,$product->get_value("sellPriceCurrencyTypeID"));

$TPL["main_alloc_title"] = "Product: ".$product->get_value("productName")." - ".APPLICATION_NAME;
$product->set_values();
$product->set_tpl_values();

if (!$productID) {
  $TPL["main_alloc_title"] = "New Product - ".APPLICATION_NAME;
  $TPL["message_help"][] = "To create a new Product enter its Name and Sell Price."; 
} else {
  $TPL["message_help"][] = "Every sale of this Product can result in customised Cost and Commission transactions being automatically generated. 
                            <br><br>Click the 'New' link in the Costs/Commissions boxes below to add fixed Costs and percentage Commissions."; 
}


$TPL["taxName"] = config::get_config_item("taxName");
$TPL["taxPercent"] = config::get_config_item("taxPercent");

include_template("templates/productM.tpl");

?>
