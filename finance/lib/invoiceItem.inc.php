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

define("PERM_FINANCE_UPDATE_APPROVED", 256);

class invoiceItem extends db_entity
{
  var $data_table = "invoiceItem";
  var $display_field_name = "iiMemo";

  function invoiceItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("invoiceItemID");
    $this->data_fields = array("invoiceID"=>new db_field("invoiceID")
                               , "iiMemo"=>new db_field("iiMemo")
                               , "iiQuantity"=>new db_field("iiQuantity")
                               , "iiUnitPrice"=>new db_field("iiUnitPrice")
                               , "iiAmount"=>new db_field("iiAmount")
                               , "status"=>new db_field("status")
      );
    $this->permissions[PERM_FINANCE_UPDATE_APPROVED] = "Update approved transactions";
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $db->query("SELECT * FROM transaction WHERE invoiceItemID=".$this->get_id());
    while ($db->next_record()) {
      $transaction = new transaction();
      $transaction->read_db_record($db, false);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }
    return false;
  }
}


?>
