<?php

/*
 *
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions Pty. Ltd.
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

class productSaleItem extends db_entity {
  public $classname = "productSaleItem";
  public $data_table = "productSaleItem";
  public $key_field = "productSaleItemID";
  public $data_fields = array("productID"
                             ,"productSaleID"
                             ,"sellPrice" => array("type"=>"money", "currency"=>"sellPriceCurrencyTypeID")
                             ,"sellPriceCurrencyTypeID"
                             ,"sellPriceIncTax" => array("empty_to_null"=>false)
                             ,"quantity"
                             ,"description"
                             );
  function is_owner() {
    $productSale = $this->get_foreign_object("productSale");
    return $productSale->is_owner();
  }

  function validate() {
    $this->get_value("productID")     or $err[] = "Please select a Product.";
    $this->get_value("productSaleID") or $err[] = "Please select a Product Sale.";
    $this->get_value("sellPrice")     or $err[] = "Please enter a Sell Price.";
    $this->get_value("quantity")      or $err[] = "Please enter a Quantity.";
    return parent::validate($err);
  }

  function get_amount_spent() {
    $db = new db_alloc;
    $q = sprintf("SELECT fromTfID, tfID,
                         (amount * pow(10,-currencyType.numberToBasic) * exchangeRate) as amount
                    FROM transaction 
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
                   WHERE tfID = %d
                     AND productSaleID = %d
                     AND status != 'rejected'
                     AND productSaleItemID = %d
                ",config::get_config_item("outTfID")
                 ,$this->get_value("productSaleID")
                 ,$this->get_id());
    $db->query($q);
    $rows = array();
    while ($row = $db->row()) {
      $rows[] = $row;
    }
    return transaction::get_actual_amount_used($rows);
  }

  function get_amount_earnt() {
    $db = new db_alloc;
    $q = sprintf("SELECT fromTfID, tfID,
                         (amount * pow(10,-currencyType.numberToBasic) * exchangeRate) as amount
                    FROM transaction 
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
                   WHERE fromTfID = %d
                     AND productSaleID = %d
                     AND status != 'rejected'
                     AND productSaleItemID = %d
                ",config::get_config_item("inTfID")
                 ,$this->get_value("productSaleID")
                 ,$this->get_id());
    $db->query($q);
    $rows = array();
    while ($row = $db->row()) {
      $rows[] = $row;
    } 
    return transaction::get_actual_amount_used($rows);
  }

  function get_amount_other() {
    $db = new db_alloc;
    // Don't need to do numberToBasic conversion here
    $q = sprintf("SELECT fromTfID, tfID,
                         (amount * exchangeRate) as amount
                    FROM transaction 
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
                   WHERE fromTfID != %d
                     AND tfID != %d
                     AND tfID != %d
                     AND productSaleID = %d
                     AND status != 'rejected'
                     AND productSaleItemID = %d
                ",config::get_config_item("inTfID")
                 ,config::get_config_item("outTfID")
                 ,config::get_config_item("taxTfID")
                 ,$this->get_value("productSaleID")
                 ,$this->get_id());
    $db->query($q);
    $rows = array();
    while ($row = $db->row()) {
      $rows[] = $row;
    }
    return transaction::get_actual_amount_used($rows);
  }

  function get_amount_margin() {

    $taxPercent = config::get_config_item("taxPercent");
    $taxPercentDivisor = ($taxPercent/100) + 1;

    $p = new product();
    $p->set_id($this->get_value("productID"));
    
    $buyCost = $p->get_buy_cost() * $this->get_value("quantity");
    $sellPrice = $this->get_value("sellPrice");

    $this->get_value("sellPriceIncTax") and $sellPrice = $sellPrice / $taxPercentDivisor;

    return exchangeRate::convert($this->get_value("sellPriceCurrencyTypeID"),$sellPrice) - 
           exchangeRate::convert(config::get_config_item("currency"),$buyCost);
  }

  function get_amount_unallocated() {
    return $this->get_amount_margin() - $this->get_amount_other();
  }

  function create_transaction($fromTfID, $tfID, $amount, $description, $currency=false, $productCostID=false,$transactionType='sale') {
    global $TPL;
    $currency or $currency = config::get_config_item("currency");
    $productSale = $this->get_foreign_object("productSale");
    $tfID = $productSale->translate_meta_tfID($tfID);
    $fromTfID = $productSale->translate_meta_tfID($fromTfID);
    $transaction = new transaction;
    $transaction->set_value("productSaleID", $this->get_value("productSaleID"));
    $transaction->set_value("productSaleItemID", $this->get_id());
    $transaction->set_value("productCostID",$productCostID);
    $transaction->set_value("fromTfID", $fromTfID);
    $transaction->set_value("tfID", $tfID);
    $transaction->set_value("amount", $amount);
    $transaction->set_value("currencyTypeID", $currency);
    $transaction->set_value("status", 'pending');
    $transaction->set_value("transactionDate", date("Y-m-d"));
    $transaction->set_value("transactionType", $transactionType);
    $transaction->set_value("product", $description);
    $transaction->save();
  }

  function create_transactions() {
    $db = new db_alloc();
    $db2 = new db_alloc();

    $product = $this->get_foreign_object("product");
    $productName = $product->get_value("productName");

    $taxName = config::get_config_item("taxName");
    $taxPercent = config::get_config_item("taxPercent");
    $taxTfID = config::get_config_item("taxTfID");
    $taxPercentDivisor = ($taxPercent/100) + 1;

    // If this price includes tax, then perform a tax transfer from the
    // outgoing tf to the tax tf.
    if ($this->get_value("sellPriceIncTax")) {
      $amount_minus_tax = $this->get_value("sellPrice") / $taxPercentDivisor;
      $amount_of_tax = $this->get_value("sellPrice") - $amount_minus_tax;
      $amount_of_tax = exchangeRate::convert($this->get_value("sellPriceCurrencyTypeID"),$amount_of_tax,null,null,"%mo");
      $this->create_transaction(-1, $taxTfID, $amount_of_tax, "Product Sale ".$taxName.": ".$productName, false, false, 'tax');
    }

    // Next transaction represents the amount that someone has paid the
    // sellPrice amount for the product. This money is transferred from 
    // the Incoming transactions TF, to the Project's TF (META: -1).
    $this->create_transaction(config::get_config_item("inTfID"), -1 ,page::money($this->get_value("sellPriceCurrencyTypeID"),$this->get_value("sellPrice"),"%mo"), "Product Sale: ".$productName,$this->get_value("sellPriceCurrencyTypeID"));

    // Now loop through all the productCosts for the sale items product.
    $query = sprintf("SELECT productCost.*, product.productName
                        FROM productCost 
                   LEFT JOIN product ON product.productID = productCost.productID
                       WHERE productCost.productID = %d 
                         AND isPercentage != 1
                    ORDER BY productCostID"
                    , $this->get_value("productID"));

    $db2->query($query);
    while ($productCost_row = $db2->next_record()) {
      $amount = page::money($productCost_row["currencyTypeID"],$productCost_row["amount"] * $this->get_value("quantity"),"%mo");
      $description = "Product Cost: ".$productCost_row["productName"]." ".$productCost_row["description"];
      $this->create_transaction(-1, $productCost_row["tfID"], $amount, $description, $productCost_row["currencyTypeID"],$productCost_row["productCostID"]);
    }

    // Need to do the percentages separately because they rely on the $totalUnallocated figure
    $totalUnallocated = $this->get_amount_unallocated();

    #die("here: ".$totalUnallocated);

    // Now loop through all the productCosts % COMMISSIONS for the sale items product.
    $query = sprintf("SELECT productCost.*, product.productName
                        FROM productCost 
                   LEFT JOIN product ON product.productID = productCost.productID
                       WHERE productCost.productID = %d 
                         AND isPercentage = 1
                    ORDER BY productCostID"
                    , $this->get_value("productID"));

    $db2->query($query);
    while ($productCommission_row = $db2->next_record()) {
      $amount = page::money($productCommission_row["currencyTypeID"],page::money(config::get_config_item("currency"),$totalUnallocated,"%mo") * $productCommission_row["amount"]/100,"%mo");
      $description = "Product Commission: ".$productCommission_row["productName"]." ".$productCommission_row["description"];
      $this->create_transaction(-1, $productCommission_row["tfID"], $amount, $description, config::get_config_item("currency"),$productCommission_row["productCostID"]);
    }
  }

  function delete_transactions() {
    $q = sprintf("SELECT * FROM transaction WHERE productSaleItemID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($db->row()) {
      $transaction = new transaction;
      $transaction->read_db_record($db);
      $transaction->delete();
    }
  }


}
?>
