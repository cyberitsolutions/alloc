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

function show_productCost_list($productID, $template, $percent = false) {
  global $TPL;
  unset($TPL["display"]); // otherwise the commissions don't display.
  if ($productID) {
    $db = new db_alloc();
    $query = sprintf("SELECT * 
                        FROM productCost 
                       WHERE productID = %d 
                         AND isPercentage = %d
                    ORDER BY productCostID"
                    ,$productID, $percent);
    $db->query($query);
    while ($db->next_record()) {
      $productCost = new productCost;
      $productCost->read_db_record($db);
      $productCost->set_values();
      include_template($template);
    }
  }
}

function show_productCost_new($template) {
  global $TPL;
  $productCost = new productCost;
  $productCost->set_values(); // wipe clean
  $TPL["display"] = "display:none";
  include_template($template);
}

function tf_list($selected="") {
  global $tflist;
  echo page::select_options($tflist, $selected);
  return;
}

$productID = $_GET["productID"] or $productID = $_POST["productID"];
$product = new product;

if ($productID) {
  $product->set_id($productID);
  $product->select();
}

$tf = new tf();
$tflist = $tf->get_assoc_array("tfID","tfName");
$extra_options = array("-1"=>"META: Project TF"
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
  !$product->get_value("productName") and $TPL["message"][] = "Please enter a Product Name.";
  !$product->get_value("buyCost")     and $TPL["message"][] = "Please enter a Buy Cost.";
  !$product->get_value("sellPrice")   and $TPL["message"][] = "Please enter a Sell Price.";

  if (!$TPL["message"]) {
    $product->save();
    $productID = $product->get_id();

    // If there are no costs set up for this product, create the default costs 
    $q = sprintf("SELECT count(*) AS total FROM productCost WHERE productID = %d",$productID);
    $db = new db_alloc();
    $db->query($q);
    $row = $db->row();

    if ($row["total"] == 0) {

      // First transaction/productCost represents the transfer of money
      // that is the amount paid by Cyber for the product. We model this 
      // by transferring the buyCost from the Projects TF to the Outgoing TF.
      #$productCost = new productCost;
      #$productCost->set_value("productID", $product->get_id());
      #$productCost->set_value("fromTfID", -1);
      #$productCost->set_value("tfID", config::get_config_item("outTfID"));
      #$productCost->set_value("amount", $product->get_value("buyCost"));
      #$productCost->set_value("description", "Product acquisition: ".$product->get_value("productName"));
      #$productCost->save();

      // Next transaction represents the amount that someone has paid the
      // sellPrice amount for the product. This money is transferred from 
      // the Incoming transactions TF, to the Projects TF.
      #$productCost = new productCost;
      #$productCost->set_value("productID", $product->get_id());
      #$productCost->set_value("fromTfID", config::get_config_item("inTfID"));
      #$productCost->set_value("tfID", -1);
      #$productCost->set_value("amount", $product->get_value("sellPrice"));
      #$productCost->set_value("description", "Product sale: ".$product->get_value("productName"));
      #$productCost->save();
    }
    alloc_redirect($TPL["url_alloc_product"]."productID=".$productID);
  }
  $product->set_values();

} else if ($_POST["delete"]) {
  $product->read_globals();
  $product->delete();
  alloc_redirect($TPL["url_alloc_productList"]);
}


# Fixed costs
if ($_POST["save_costs"] || $_POST["save_commissions"]) {

  is_array($_POST["deleteCost"]) or $_POST["deleteCost"] = array();

  if (is_array($_POST["productCostID"])) {

    foreach ($_POST["productCostID"] as $k => $productCostID) {
      // Save
      if (in_array($productCostID, $_POST["deleteCost"])) {
        $productCost = new productCost;
        $productCost->set_id($productCostID);
        $productCost->delete();

      } else {
    
        $a = array("productCostID"=>$productCostID
                  ,"productID"=>$productID
                  ,"fromTfID"=>$_POST["fromTfID"][$k]
                  ,"tfID"=>$_POST["tfID"][$k]
                  ,"amount"=>$_POST["amount"][$k]
                  ,"isPercentage"=>$_POST["save_commissions"] ? 1 : 0
                  ,"description"=>$_POST["description"][$k]
                  );

        $productCost = new productCost;
        $productCost->read_array($a);
        $errs = $productCost->validate();
        if (!$errs) {
          $productCost->save();
        }
      }
    }
  }
  alloc_redirect($TPL["url_alloc_product"]."productID=".$product->get_id());
}


$TPL["main_alloc_title"] = "Product: ".$product->get_value("productName")." - ".APPLICATION_NAME;
$product->set_values();

if (!$productID) {
  $TPL["main_alloc_title"] = "New Product - ".APPLICATION_NAME;
  $TPL["message_help"][] = "To create a new Product enter its Name, Buy Cost and Sell Price."; 
} else {
  $TPL["message_help"][] = "Every sale of this Product can result in customised Cost and Commission transactions being automatically generated. 
                            <br><br>Click the 'New' link in the Costs/Commissions boxes below to add fixed Costs and percentage Commissions."; 
}


$TPL["taxName"] = config::get_config_item("taxName");

include_template("templates/productM.tpl");

?>
