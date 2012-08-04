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

class invoiceRepeat extends db_entity {
  public $classname = "invoiceRepeat";
  public $data_table = "invoiceRepeat";
  public $display_field_name = "invoiceRepeatID";
  public $key_field = "invoiceRepeatID";
  public $data_fields = array("invoiceID"
                             ,"personID"
                             ,"message"
                             ,"active"
                             );
  function save($dates="") {
    $rtn = parent::save();
    if ($rtn) {
      $dates = str_replace(","," ",$dates);
      $dates = preg_replace("/\s+/"," ",trim($dates));
      $dates = explode(" ",$dates);
      $db = new db_alloc();
      $db->query("DELETE FROM invoiceRepeatDate WHERE invoiceRepeatID = %d",$this->get_id());
      foreach ($dates as $date) {
        $db->query("INSERT INTO invoiceRepeatDate (invoiceRepeatID,invoiceDate) VALUES (%d,'%s')",$this->get_id(),$date);
      }
    }
  }

  function set_values($prefix) {
    global $TPL;
    $db = new db_alloc();
    $db->query("SELECT * FROM invoiceRepeatDate WHERE invoiceRepeatID = %d",$this->get_id());
    while ($row = $db->row()) {
      $rows[] = $row["invoiceDate"];
    }
    $TPL[$prefix."frequency"] = implode(" ",(array)$rows);
    return parent::set_values($prefix);
  }

  function get_all_parties($invoiceID) {
    if ($invoiceID) {
      $invoice = new invoice();
      $invoice->set_id($invoiceID);
      $invoice->select();
      $interestedPartyOptions = $invoice->get_all_partieS($invoice->get_value("projectID"),$invoice->get_value("clientID"));
    }

    if (is_object($this) && $this->get_id()) {
      $interestedPartyOptions = interestedParty::get_interested_parties("invoiceRepeat",$this->get_id(),$interestedPartyOptions);
    }
    return $interestedPartyOptions;
  }
}
?>
