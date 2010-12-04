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

class productCost extends db_entity {
  public $classname = "productCost";
  public $data_table = "productCost";
  public $key_field = "productCostID";
  public $data_fields = array("tfID"
                             ,"fromTfID"
                             ,"productID"
                             ,"amount" => array("type"=>"money")
                             ,"isPercentage"=> array("empty_to_null"=>false)
                             ,"description"
                             );

  function validate() {
    $this->get_value("productID")    or $err[] = "Missing a Product.";
    $this->get_value("fromTfID")     or $err[] = "Missing a Source TF.";
    $this->get_value("tfID")         or $err[] = "Missing a Destination TF.";
    $this->get_value("amount")       or $err[] = "Missing an amount.";
    #$this->get_value("isPercentage") or $err[] = "Missing the isPercentage field.";
    $this->get_value("tfID") == $this->get_value("fromTfID") and $err[] = "Can't have identical Source and Destination TF's.";
    return parent::validate($err);
  }






}

?>
