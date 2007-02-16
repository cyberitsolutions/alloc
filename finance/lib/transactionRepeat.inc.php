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

class transactionRepeat extends db_entity {
  var $data_table = "transactionRepeat";
  var $display_field_name = "product";


  function transactionRepeat() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("transactionRepeatID");
    $this->data_fields = array("companyDetails"=>new db_field("companyDetails", "Company Details", "", array("empty_to_null"=>false))
                               , "payToName"=>new db_field("payToName", "Pay To Name", "", array("empty_to_null"=>false))
                               , "payToAccount"=>new db_field("payToAccount", "Pay To Account", "", array("empty_to_null"=>false))
                               , "tfID"=>new db_field("tfID")

                               , "emailOne"=>new db_field("emailOne")
                               , "emailTwo"=>new db_field("emailTwo")

                               , "transactionRepeatModifiedUser"=>new db_field("transactionRepeatModifiedUser")
                               , "reimbursementRequired"=>new db_field("reimbursementRequired", "Reimbursement Required", "", array("empty_to_null"=>false))
                               , "lastModified"=>new db_field("lastModified")
                               , "dateEntered"=>new db_field("dateEntered")
                               , "transactionStartDate"=>new db_field("transactionStartDate")
                               , "transactionFinishDate"=>new db_field("transactionFinishDate")

                               , "paymentBasis"=>new db_field("paymentBasis")
                               , "amount"=>new db_field("amount")
                               , "product"=>new db_field("product")
                               , "status"=>new db_field("status")

                               , "transactionType"=>new db_field("transactionType")


      );

  }

  function is_owner() {
    $tf = new tf;
    $tf->set_id($this->get_value("tfID"));
    $tf->select();
    return $tf->is_owner();
  }


  function insert() {
    $this->set_value("dateEntered", date("Y-m-d"));
    db_entity::insert();
  }

}



?>
