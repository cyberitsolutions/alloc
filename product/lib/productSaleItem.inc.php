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

class productSaleItem extends db_entity {
  var $classname = "productSaleItem";
  var $data_table = "productSaleItem";

  function productSaleItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("productSaleItemID");
    $this->data_fields = array("productID"=>new db_field("productID")
                        ,"productSaleID"=>new db_field("productSaleID")
                        ,"buyCost"=>new db_field("buyCost")
                        ,"sellPrice"=>new db_field("sellPrice")
                        ,"quantity"=>new db_field("quantity")
                        ,"description"=>new db_field("description")
      );
  }

  function transactionTotal($id = -1) {
    if ($id == -1)
      $id = $this->get_id();
    $db = new db_alloc;
    $row = $db->qr("SELECT SUM(amount) FROM transaction WHERE productSaleItemID = %d", $id);
    return $row["SUM(amount)"];
  }

  function checkTotals($id = -1) {
    if ($id == -1)
      $id = $this->get_id();

    $db = new db_alloc;
    // != 1 doesn't give the right results because some columns are NULL
    // The better test would be 'isPercentage IS NOT TRUE' but that's not supported in MySQL 3.23 and 4.0...
    $fixed = $db->qr("SELECT SUM(amount) FROM productSaleTransaction WHERE (isPercentage IS NULL OR isPercentage = 0)  AND productSaleItemID = %d", $id);
    $pct = $db->qr("SELECT SUM(amount) FROM productSaleTransaction WHERE isPercentage = 1 AND productSaleItemID = %d", $id);
    $transactions = $db->qr("SELECT SUM(amount) FROM transaction WHERE productSaleItemID = %d", $id);
    return array("fixed"=>$fixed["SUM(amount)"], "pct"=>$pct["SUM(amount)"], "transactions"=>$transactions["SUM(amount)"]);
  }

  function create_transaction($product, $amount, $tfID, $status = "pending", $type = 'product') {
    # Safety stuff goes here

    $transaction = new transaction;
    $transaction->set_value("product", $product);
    $transaction->set_value("amount", sprintf("%0.2f", $amount));
    $transaction->set_value("status", $status);
    $transaction->set_value("tfID", $tfID);
    $transaction->set_value("transactionDate", date("Y-m-d"));
    $transaction->set_value("transactionType", $type);
    $transaction->set_value("productSaleItemID", $this->get_id());
    $transaction->save();
    return 1;
  }


  function create_transactions() {
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM productSaleTransaction WHERE productSaleItemID = %d ORDER BY isPercentage, amount", $this->get_id());
    $db->query($query);

    $staticTotal = 0;
    
    $taxName = config::get_config_item("taxName");
    $tax_tfID = config::get_config_item("taxTfID");
    $taxRate = config::get_config_item("taxPercent") / 100.0;

    $taxDescription = "$taxName for product sale " . $this->get_id();
    /* Tax calculation is kind of a nuisance
     * Transactions are in ascending order, so the sale price and any other
     * incoming funds come first. When these run out, the value of staticTotal
     * is considered to be the taxable portion.
     */
    $taxDone = false;
    
    while ($row = $db->next_record()) {
      $description = sprintf("Product sale %d: %s", $this->get_value("productSaleID"), $row["description"]);
      if (!$row["isPercentage"]) {
        if (!$taxDone && $row["amount"] > 0) {
          //Done the last of the negative values, calculate tax
          $taxValue = -$staticTotal / (1 + 1/($taxRate));
          $this->create_transaction($taxDescription, $taxValue, $tax_tfID, 'pending', 'tax');
          $staticTotal += $taxValue;
          $taxDone = true;
        }
        $staticTotal += $row["amount"];
        $this->create_transaction($description, $row["amount"], $row["tfID"]);
      } else {
        // This needs to be here in case there are no other static items
        if (!$taxDone) {
          //Done the last of the negative values, calculate tax
          $taxValue = -$staticTotal / (1 + 1/($taxRate));
          $this->create_transaction($taxDescription, $taxValue, $tax_tfID, 'pending', 'tax');
          $staticTotal += $taxValue;
          $taxDone = true;
        }
        $this->create_transaction($description, -$staticTotal * $row["amount"] / 100.0, $row["tfID"]);
      }
    }
  }

  function delete_transactions() {
    # Do it this way to make sure permissions get checked
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM transaction WHERE productSaleItemID = %d", $this->get_id());
    $db->query($query);

    while ($row = $db->next_record()) {
      $transaction = new transaction;
      $transaction->read_row_record($row);
      $transaction->delete();
    }
  }

  function delete() {
    $db = new db_alloc;
    $db->qr("DELETE FROM transaction WHERE productSaleItemID = %d", $this->get_id());
    parent::delete();
  }

}
?>
