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

class tf extends db_entity {
  var $data_table = "tf";
  var $display_field_name = "tfName";

  function tf() {
    $this->db_entity();
    $this->key_field = new db_text_field("tfID");
    $this->data_fields = array("tfName"=>new db_text_field("tfName")
                               , "tfComments"=>new db_text_field("tfComments")
                               , "tfBalance"=>new db_text_field("tfBalance")
                               , "tfModifiedUser"=>new db_text_field("tfModifiedUser")
                               , "tfModifiedTime"=>new db_text_field("tfModifiedTime")
                               , "qpEmployeeNum"=>new db_text_field("qpEmployeeNum")
                               , "quickenAccount"=>new db_text_field("quickenAccount")
      );
  }

  /** 
   *  Get the balance of a TF.
   *  @param where An associative array whose key is the column name and whose value is the sql criteria. Eg: array("status"=>"approved")
   */
  function get_balance($where = array(), $debug="") {
    global $current_user;
 
    // If no status is requested then default to approved.  
    $where["status"] or $where["status"] = "approved";
    $where["tfID"] = $this->get_id();
    
    // Check the current_user has PERM_READ for this
    check_entity_perm("transaction", PERM_READ, $current_user, $this->is_owner());

    // Get belance
    $db = new db_alloc;
    $query = "SELECT SUM(amount) as balance FROM transaction WHERE";

    // Build up the rest of the WHERE sql
    $query.= db_get_where($where);
#echo "<br>".$debug." q: ".$query;
    $db->query($query);
    $db->next_record() || die("TF $tfID not found in tf::get_balance");
    return $db->f("balance");
  }



  function delete() {
    $query = "DELETE FROM transaction WHERE tfID='".$this->get_id()."'";
    $db = new db_alloc;
    $db->query($query);

    db_entity::delete();
  }


  // Check if user has permission to access this project

  function is_owner($person = "") {
    global $current_user;
    if ($person == "") {
      $person = $current_user;
    }
    // echo "<br>" . $this->get_id() . " " . $person->get_id();

    if (!$this->get_id()) {
      return false;
    }
    $query = "SELECT * FROM tfPerson WHERE tfID=".$this->get_id()." AND personID=".$person->get_id();
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
      $nav_links[] = $repeating_link;
    }

    if ($this->have_perm(PERM_UPDATE)) {
      $edit_url = $TPL["url_alloc_tf"]."tfID=".$this->get_id();
      $edit_link = "<a href=\"$edit_url\">Edit TF</a>";
      $nav_links[] = $edit_link;
    }

    return $nav_links;
  }
}

?>
