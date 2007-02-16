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

class invoice extends db_entity {
  var $data_table = "invoice";
  var $display_field_name = "invoiceName";
  function invoice() {
    $this->db_entity();
    $this->key_field = new db_field("invoiceID");
    $this->data_fields = array("invoiceName"=>new db_field("invoiceName")
                               , "invoiceDate"=>new db_field("invoiceDate")
                               , "invoiceNum"=>new db_field("invoiceNum")
                               , "invoiceName"=>new db_field("invoiceName")
      );
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $db->query("SELECT * FROM invoiceItem WHERE invoiceID=".$this->get_id());
    while ($db->next_record()) {
      $invoice_item = new invoiceItem();
      if ($invoice_item->read_db_record($db, false)) {
        if ($invoice_item->is_owner($person)) {
          return true;
        }
      }
    }
    return false;
  }

}



?>
