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

class tf extends db_entity {
  var $data_table = "tf";
  var $display_field_name = "tfName";

  function tf() {
    $this->db_entity();
    $this->key_field = new db_field("tfID");
    $this->data_fields = array("tfName"=>new db_field("tfName")
                               , "tfComments"=>new db_field("tfComments")
                               , "tfModifiedUser"=>new db_field("tfModifiedUser")
                               , "tfModifiedTime"=>new db_field("tfModifiedTime")
                               , "qpEmployeeNum"=>new db_field("qpEmployeeNum")
                               , "quickenAccount"=>new db_field("quickenAccount")
                               , "status"=>new db_field("status")
      );
  }

  function get_balance($where = array(), $debug="") {
    global $current_user;
 
    // If no status is requested then default to approved.  
    $where["status"] or $where["status"] = "approved";
    
    // Check the current_user has PERM_READ for this
    check_entity_perm("transaction", PERM_READ, $current_user, $this->is_owner());

    // Get belance
    $db = new db_alloc;
    $query = sprintf("SELECT sum(if(fromTfID=%d,-amount,amount)) AS balance 
                        FROM transaction 
                       WHERE (tfID = %d or fromTfID = %d) "
                    ,$this->get_id(),$this->get_id(),$this->get_id());

    // Build up the rest of the WHERE sql
    foreach($where as $column_name=>$value) {
      $op = " = ";
      if (is_array($value)) {
        $op = $value[0];
        $value = $value[1];
      }
      $query.= " AND ".$column_name.$op." '".db_esc($value)."'";
    }

    #echo "<br>".$debug." q: ".$query;
    $db->query($query);
    $db->next_record() || die("TF $tfID not found in tf::get_balance");
    return $db->f("balance");
  }

  function delete() {
    global $current_user, $TPL;
    $db = new db_alloc;

    if ($current_user->have_role("god") || $current_user->have_role("admin")) {
      $query = sprintf("DELETE FROM transaction WHERE tfID=%d",$this->get_id());
      $db->query($query);

      $query = sprintf("DELETE FROM tfPerson WHERE tfID=%d",$this->get_id());
      $db->query($query);
      db_entity::delete();
    } else {
      $TPL["message"] = "Permission denied.";
    }
  }

  function is_owner($person = "") {
    global $current_user;
    if ($person == "") {
      $person = $current_user;
    }

    if (!$this->get_id()) {
      return false;
    }
    $query = sprintf("SELECT * FROM tfPerson WHERE tfID=%d AND personID=%d",$this->get_id(),$person->get_id());
    $db = new db_alloc;
    $db->query($query);
    return $db->next_record();
  }

  function get_nav_links() {
    global $TPL, $current_user;

    $nav_links = array();

    // Alla melded the have entity perm for transactionRepeat into the 
    // have entity perm for transaction because I figured they were the 
    // same and it nukes the error message!

    if (have_entity_perm("transaction", PERM_READ, $current_user, $this->is_owner())) {
      $statement_url = $TPL["url_alloc_transactionList"]."tfID=".$this->get_id();
      $statement_link = "<a href=\"$statement_url\">Statement</a>";
      $nav_links[] = $statement_link;
      // if (have_entity_perm("transactionRepeat", PERM_READ, $current_user, $this->is_owner())) {
      $repeating_url = $TPL["url_alloc_transactionRepeatList"]."tfID=".$this->get_id();
      $repeating_link = "<a href=\"$repeating_url\">Repeating Expenses</a>";
  #    $nav_links[] = $repeating_link;
    }

    return $nav_links;
  }

  function get_link() {
    global $current_user, $TPL;
    if (have_entity_perm("transaction", PERM_READ, $current_user, $this->is_owner())) {
      return "<a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$this->get_id()."\">".$this->get_value("tfName")."</a>";
    } else {
      return $this->get_value("tfName");
    }
  }

  function get_name($tfID=false) {
    if ($tfID) {
      $db = new db_alloc;
      $db->query(sprintf("SELECT tfName FROM tf WHERE tfID=%d",$tfID));
      $db->next_record();
      return $db->f("tfName");
    }
  }



}

?>
