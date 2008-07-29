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

$prodID = $_GET["productID"] or $prodID = $_POST["productID"];
$product = new product;

if ($prodID) {
  $product->set_id($prodID);
  $product->select();


}

function get_costs() {
  global $product, $db;
  $fixed = $pct = array();
  $query = "SELECT * FROM productCost WHERE productID = ". $product->get_id() . ";";
  $db->query($query);
  while ($row = $db->next_record()) {
    if ($row["isPercentage"]) {
      $pct[] = $row;
    } else {
      $fixed[] = $row;
    }
  }
  return array($fixed, $pct);
}

// build list of TFs
$query = "SELECT * from tf";
$db = new db_alloc;
$db->query($query);
while ($row = $db->next_record()) {
  $tflist[$row["tfID"]] = $row["tfName"];
}

function tf_list($selected) {
  global $tflist;
  echo get_select_options($tflist, $selected);
  return;
}
$TPL["companyTF"] = $tflist[config::get_config_item("cybersourceTfID")];
$TPL["taxTF"] = $tflist[config::get_config_item("taxTfID")];

if ($_POST["delete"]) {
  $product->read_globals();
  $product->delete();
  header("Location: ".$TPL["url_alloc_productList"]);
}

$taxRate = config::get_config_item("taxPercent") / 100.0;
$TPL["taxRate"] = $taxRate;

if ($_POST["save"]) {
  $product->read_globals();
  $product->save();
  $product->set_tpl_values(DST_HTML_ATTRIBUTE, "product_");
  $prodID = $product->get_id();
}

if ($_POST["save_commissions"] || $_POST["commission_delete"]) {
  is_array($_POST["deleteCost"]) or $_POST["deleteCost"] = array();
  foreach ($_POST["deleteCost"] as $id => $dummy) {
    $cost = new productCost;
    $cost->set_id($id);
    $cost->select();
    $cost->delete();
  }
  $costSum = 0;
  $pctSum = 0;
  # Handle update entries
  if (is_array($_POST["costID"])) {
    foreach ($_POST["costID"] as $id => $dummy) {
      if (array_key_exists($id, $_POST["deleteCost"])) {
        continue;
      }
      $tf = $_POST["tfID"][$id];
      $amount = $_POST["amount"][$id];
      $description = $_POST["description"][$id];

      $cost = new productCost;
      $cost->set_id($id);
      $cost->select();
      $cost->set_value("tfID", $tf);
      $cost->set_value("amount", $amount);
      $cost->set_value("description", $description);
      $cost->save();
      if ($cost->get_value("isPercentage")) {
        $pctSum += $amount;
      } else {
        $costSum += $amount;
      }
    }
  }


  # Insert new entries
  if (is_array($_POST["new_fixedCostID"])) {
    foreach ($_POST["new_fixedCostID"] as $idx => $dummy) {
      $tfID = $_POST["new_fixed_tfID"][$idx];
      $amount = $_POST["new_fixed_amount"][$idx];
      $desc = $_POST["new_fixed_description"][$idx];

      if (!$amount) {
        continue;
      }
      $costSum += $amount;
      $cost = new productCost;
      $cost->set_value("tfID", $tfID);
      $cost->set_value("amount", $amount);
      $cost->set_value("description", $desc);
      $cost->set_value("isPercentage", 0);
      $cost->set_value("productID", $product->get_id());
      $cost->save();
    }
  }
  //spot the difference
  if (is_array($_POST["new_pctCostID"])) {
    foreach ($_POST["new_pctCostID"] as $idx => $dummy) {
      $tfID = $_POST["new_pct_tfID"][$idx];
      $amount = $_POST["new_pct_amount"][$idx];
      $desc = $_POST["new_pct_description"][$idx];

      if (!$amount) {
        continue;
      }
      $pctSum += $amount;
      $cost = new productCost;
      $cost->set_value("tfID", $tfID);
      $cost->set_value("amount", $amount);
      $cost->set_value("description", $desc);
      $cost->set_value("isPercentage", 1);
      $cost->set_value("productID", $product->get_id());
      $cost->save();
    }
  }

  // Ensure all funds are accounted for. We can spit out an error here, but can't actually stop the user from going
  // away

  $sellPriceMinusTax = $product->get_value("sellPrice") / (1 + $taxRate);
  $margin = $sellPriceMinusTax - $product->get_value("buyCost") - $costSum;
 
  if ($margin < 0) {
    $TPL["message"][] = "Costs exceed sale price. Profit is negative.";
  }
  if ($pctSum != 100) {
    $TPL["message_help"][] = sprintf("Total percentage commissions sum to %0.0f%%, which is %s by %0.0f percentage points. Commissions will have to be adjusted to sum to 100%% when a product sale is made.", $pctSum, ($pctSum < 100 ? "underallocated" : "overallocated"), abs(100.0 - $pctSum));
  }
}

if ($prodID) {
  $TPL["main_alloc_title"] = "Product: ".$product->get_value("productName")." - ".APPLICATION_NAME;
  $product->set_tpl_values(DST_HTML_ATTRIBUTE, "product_");

  # Calculate the tax
  # Divide by (reciprocal of tax rate + 1)
  if ($taxRate) {
    $TPL["product_tax"] = sprintf("%0.2f", $product->get_value("sellPrice") / (1 + 1/(config::get_config_item("taxPercent") / 100.0)));
    $TPL["taxName"] = config::get_config_item("taxName");
  }
} else {
   $TPL["main_alloc_title"] = "New Product - ".APPLICATION_NAME;
}

include_template("templates/productM.tpl");
page_close();

