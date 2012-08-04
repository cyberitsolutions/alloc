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

class transactionRepeat extends db_entity {
  public $data_table = "transactionRepeat";
  public $display_field_name = "product";
  public $key_field = "transactionRepeatID";
  public $data_fields = array("companyDetails" => array("empty_to_null"=>false)
                             ,"payToName" => array("empty_to_null"=>false)
                             ,"payToAccount" => array("empty_to_null"=>false)
                             ,"tfID"
                             ,"fromTfID"
                             ,"emailOne"
                             ,"emailTwo"
                             ,"transactionStartDate"
                             ,"transactionFinishDate"
                             ,"transactionRepeatModifiedUser"
                             ,"reimbursementRequired" => array("empty_to_null"=>false)
                             ,"transactionRepeatModifiedTime"
                             ,"transactionRepeatCreatedTime"
                             ,"transactionRepeatCreatedUser"
                             ,"paymentBasis"
                             ,"amount" => array("type"=>"money")
                             ,"currencyTypeID"
                             ,"product"
                             ,"status"
                             ,"transactionType"
                             );


  function is_owner() {
    $tf = new tf();
    $tf->set_id($this->get_value("tfID"));
    $tf->select();
    return $tf->is_owner();
  }

}



?>
