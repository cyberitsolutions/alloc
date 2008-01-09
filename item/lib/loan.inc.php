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

class loan extends db_entity {
  var $data_table = "loan";
  var $display_field_name = "itemID";


  function loan() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("loanID");
    $this->data_fields = array("itemID"=>new db_field("itemID")
                               , "personID"=>new db_field("personID")
                               , "loanModifiedUser"=>new db_field("loanModifiedUser")
                               , "loanModifiedTime"=>new db_field("loanModifiedTime")
                               , "dateBorrowed"=>new db_field("dateBorrowed")
                               , "dateToBeReturned"=>new db_field("dateToBeReturned")
                               , "dateReturned"=>new db_field("dateReturned")
      );
  }



}



?>
