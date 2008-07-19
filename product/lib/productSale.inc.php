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

class productSale extends db_entity {
  var $classname = "productSale";
  var $data_table = "productSale";

  function productSale() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("productSaleID");
    $this->data_fields = array("projectID"=>new db_field("projectID")
                              ,"status"=>new db_field("status")
                              ,"productSaleCreatedTime"=>new db_field("productSaleCreatedTime")
                              ,"productSaleCreatedUser"=>new db_field("productSaleCreatedUser")
                              ,"productSaleModifiedTime"=>new db_field("productSaleModifiedTime")
                              ,"productSaleModifiedUser"=>new db_field("productSaleModifiedUser")
                              );
  }

  function delete() {
    $db = new db_alloc;

    $query = sprintf("SELECT * FROM productSaleItem WHERE productSaleID = %d", $this->get_id());
    $db->query($query);
    while ($row = $db->next_record()) {
      $psItem = new productSaleItem;
      $psItem->set_id($row["productSaleItemID"]);
      $psItem->select();
      $psItem->delete();
    }
    parent::delete();
    return;
  }
  
  function create_psTransaction($productSaleItemID, $tfID, $amount, $isPercentage, $description) {
    $psTransaction = new productSaleTransaction;
    $psTransaction->set_value("productSaleItemID", $productSaleItemID);
    $psTransaction->set_value("tfID", $tfID);
    $psTransaction->set_value("amount", $amount);
    $psTransaction->set_value("isPercentage", $isPercentage);
    $psTransaction->set_value("description", $description);
    $psTransaction->save();

  }

  function create_product_transactions() {
    $db = new db_alloc();
    $project = $this->get_foreign_object("project");

    $query = sprintf("SELECT * FROM productSaleItem WHERE productSaleID = %d", $this->get_id());
    $db->query($query);
    $products = array();
    while ($row = $db->next_record()) {
      $products[] = $row;
    }
    
    foreach ($products as $saleItem) {
      $query = sprintf("SELECT * FROM productCost WHERE productID = %d ORDER BY isPercentage ASC",
          $saleItem["productID"]);
      $staticSum = 0;
      //this code relies on the static values coming up first
      $db->query($query);
      while ($row = $db->next_record()) {
        if (!$row["isPercentage"]) {
          $amount = $row["amount"] * $saleItem["quantity"];
        } else {
          $amount = $row["amount"];
        }
          
        $this->create_psTransaction($saleItem["productSaleItemID"], $row["tfID"], $amount, $row["isPercentage"], $row["description"]);

      }
      // Other transactions: Sell price to project cost centre, buy price to cyber TF
      $this->create_psTransaction($saleItem["productSaleItemID"], $project->get_value("cost_centre_tfID"), -$saleItem["sellPrice"], 0, "Price for product sale");
      $this->create_psTransaction($saleItem["productSaleItemID"], config::get_config_item("cybersourceTfID"), $saleItem["buyCost"], 0, "Cost for product sale");
    }
  }
 
  function move_forward() {
    global $current_user;
    $status = $this->get_value("status");
    if ($status == "edit") {
      //Manager permissions required, send emails 
      if ($current_user->have_role("manage") || $current_user->have_role("admin")) {
        $this->set_value("status", "admin");
      } else {
        return 1;
      }
    }
    if ($status == "admin") {
      if ($current_user->have_role("admin")) {
        $this->set_value("status", "finished");
      } else {
        return 1;
      }
    }
    return 0;
  }

  function move_backward() {
    global $current_user;
    if ($current_user->have_role("admin")) {
      if ($this->get_value("status") == "finished")
        $this->set_value("status", "admin");
      else if ($this->get_value("status") == "admin")
        $this->set_value("status", "edit");
    } else {
      return 1;
    }
    return 0;
  }

}


?>
