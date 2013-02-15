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

class tf extends db_entity {
  public $data_table = "tf";
  public $display_field_name = "tfName";
  public $key_field = "tfID";
  public $data_fields = array("tfName"
                             ,"tfComments"
                             ,"tfModifiedUser"
                             ,"tfModifiedTime"
                             ,"qpEmployeeNum"
                             ,"quickenAccount"
                             ,"tfActive"
                             );

  function get_balance($where = array(), $debug="") {
    $current_user = &singleton("current_user");
 
    // If no status is requested then default to approved.  
    $where["status"] or $where["status"] = "approved";
    
    if (!$this->is_owner() && !$current_user->have_role("admin")) {
      return false;
    }

    // Get belance
    $db = new db_alloc();
    $query = prepare("SELECT sum( if(fromTfID=%d,-amount,amount) * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance 
                        FROM transaction 
                   LEFT JOIN currencyType ON transaction.currencyTypeID = currencyType.currencyTypeID
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
    $db->next_record() || alloc_error("TF $tfID not found in tf::get_balance");
    return $db->f("balance");
  }

  function is_owner($person = "") {
    $current_user = &singleton("current_user");
    static $owners;
    if ($person == "") {
      $person = $current_user;
    }

    if (!$this->get_id()) {
      return false;
    }
  
    // optimization
    if (isset($owners[$person->get_id()])) {
      return in_array($this->get_id(),$owners[$person->get_id()]);
    }
    $owners[$person->get_id()] = $this->get_tfs_for_person($person->get_id());
    return in_array($this->get_id(),(array)$owners[$person->get_id()]);
  }

  function get_tfs_for_person($personID) {
    $query = prepare("SELECT * FROM tfPerson WHERE personID=%d",$personID);
    $db = new db_alloc();
    $db->query($query);
    while ($row = $db->row()) {
      $owners[] = $row["tfID"];
    }
    return $owners;
  }

  function get_nav_links() {
    global $TPL;
    $current_user = &singleton("current_user");

    $nav_links = array();

    // Alla melded the have entity perm for transactionRepeat into the 
    // have entity perm for transaction because I figured they were the 
    // same and it nukes the error message!

    if (have_entity_perm("tf", PERM_UPDATE, $current_user, $this->is_owner())) {
      $statement_url = $TPL["url_alloc_tf"]."tfID=".$this->get_id();
      $statement_link = "<a href=\"$statement_url\">Edit TF</a>";
      $nav_links[] = $statement_link;
    }

    return $nav_links;
  }

  function get_link() {
    $current_user = &singleton("current_user");
    global $TPL;
    if (have_entity_perm("transaction", PERM_READ, $current_user, $this->is_owner())) {
      return "<a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$this->get_id()."\">".$this->get_value("tfName",DST_HTML_DISPLAY)."</a>";
    } else {
      return $this->get_value("tfName",DST_HTML_DISPLAY);
    }
  }

  function get_name($tfID=false) {
    if ($tfID) {
      $db = new db_alloc();
      $db->query(prepare("SELECT tfName FROM tf WHERE tfID=%d",$tfID));
      $db->next_record();
      return $db->f("tfName");
    }
  }

  function get_tfID($name) {
    if ($name) {
      $db = new db_alloc();
      $q = "SELECT tfID FROM tf WHERE ".sprintf_implode("tfName = '%s'",$name);
      $db->query($q);
      while ($row = $db->row()) {
        $rtn[] = $row["tfID"];
      }
    }
    return (array)$rtn;
  }

  function get_permitted_tfs($requested_tfs=array()) {
    $current_user = &singleton("current_user");
    // If admin, just use the requested tfs
    if ($current_user->have_role('admin')) {
      $rtn = $requested_tfs;

    // If not admin, then remove the items from $requested_tfs that the user can't access
    } else {
      $allowed_tfs = (array)tf::get_tfs_for_person($current_user->get_id());
      foreach ((array)$requested_tfs as $tf) {
        if (in_array($tf,$allowed_tfs)) {
          $rtn[] = $tf;
        }
      }
    }
    
    // db_esc everything
    foreach ((array)$rtn as $tf) {
      $r[] = db_esc($tf);
    }
    return (array)array_unique((array)$r);
  }

  function get_list_filter($_FORM=array()) {
    $current_user = &singleton("current_user");

    if (!$_FORM["tfIDs"] && !$current_user->have_role('admin')) {
      $_FORM["owner"] = true;
    }
    $_FORM["owner"] and $filter1[] = sprintf_implode("tfPerson.personID = %d",$current_user->get_id());

    $tfIDs = tf::get_permitted_tfs($_FORM["tfIDs"]);
    $tfIDs and $filter1[] = sprintf_implode("tf.tfID = %d",$tfIDs);
    $tfIDs and $filter2[] = sprintf_implode("tf.tfID = %d",$tfIDs);
    $_FORM["showall"] or $filter1[] = "(tf.tfActive = 1)";
    $_FORM["showall"] or $filter2[] = "(tf.tfActive = 1)";

    return array($filter1,$filter2);
  }

  function get_list($_FORM=array()) {
    $current_user = &singleton("current_user");

    list($filter1,$filter2) = tf::get_list_filter($_FORM);

    if (is_array($filter1) && count($filter1)) {
      $f = " AND ".implode(" AND ",$filter1);
    }
    if (is_array($filter2) && count($filter2)) {
      $f2 = " AND ".implode(" AND ",$filter2);
    }
  
    $db = new db_alloc();
    $q = prepare("SELECT transaction.tfID as id, tf.tfName, transactionID, transaction.status,
                         sum(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                    FROM transaction
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
               LEFT JOIN tf on transaction.tfID = tf.tfID
                   WHERE 1 AND transaction.status != 'rejected' ".$f2."
                GROUP BY transaction.status,transaction.tfID"
                );
    $db->query($q);
    while ($row = $db->row()) {
      if ($row["status"] == "approved") {
        $adds[$row["id"]] = $row["balance"];
      } else if ($row["status"] == "pending") {
        $pending_adds[$row["id"]] = $row["balance"];
      }
    }

        
    $q = prepare("SELECT transaction.fromTfID as id, tf.tfName, transactionID, transaction.status,
                         sum(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                    FROM transaction
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
               LEFT JOIN tf on transaction.fromTfID = tf.tfID
                   WHERE 1 AND transaction.status != 'rejected' ".$f2."
                GROUP BY transaction.status,transaction.fromTfID"
                );
    $db->query($q);
    while ($row = $db->row()) {
      if ($row["status"] == "approved") {
        $subs[$row["id"]] = $row["balance"];
      } else if ($row["status"] == "pending") {
        $pending_subs[$row["id"]] = $row["balance"];
      }
    }

    $q = prepare("SELECT tf.* 
                    FROM tf 
               LEFT JOIN tfPerson ON tf.tfID = tfPerson.tfID 
                   WHERE 1 ".$f."
                GROUP BY tf.tfID 
                ORDER BY tf.tfName");  

    $db->query($q);
    while ($row = $db->row()) {
      $tf = new tf();
      $tf->read_db_record($db);
      $tf->set_values();

      $total = $adds[$db->f("tfID")] - $subs[$db->f("tfID")];
      $pending_total = $pending_adds[$db->f("tfID")] - $pending_subs[$db->f("tfID")];

      if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
        $row["tfBalance"] = page::money(config::get_config_item("currency"),$total,"%s%m %c");
        $row["tfBalancePending"] = page::money(config::get_config_item("currency"),$pending_total,"%s%m %c");
        $row["total"] = $total;
        $row["pending_total"] = $pending_total;
      } else {
        $row["tfBalance"] = "";
        $row["tfBalancePending"] = "";
        $row["total"] = "";
        $row["pending_total"] = "";
      }

      $nav_links = $tf->get_nav_links();
      $row["nav_links"] = implode(" ", $nav_links);
      $row["tfActive_label"] = "";
      $tf->get_value("tfActive") and $row["tfActive_label"] = "Y";
      $rows[$tf->get_id()] = $row;
    }
    return (array)$rows;
  }


}

?>
