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


define("PERM_APPROVE_PRODUCT_TRANSACTIONS", 256);
class productSale extends db_entity {
  public $classname = "productSale";
  public $data_table = "productSale";
  public $key_field = "productSaleID";
  public $data_fields = array("clientID"
                             ,"projectID"
                             ,"status"
                             ,"productSaleCreatedTime"
                             ,"productSaleCreatedUser"
                             ,"productSaleModifiedTime"
                             ,"productSaleModifiedUser"
                             );
  public $permissions = array(PERM_APPROVE_PRODUCT_TRANSACTIONS => "approve product transactions");

  function validate() {
    if ($this->get_value("status") == "admin" || $this->get_value("status") == "finished") {
      $orig = new $this->classname;
      $orig->set_id($this->get_id());
      $orig->select();
      $orig_status = $orig->get_value("status");
      if ($orig_status == "allocate" && $this->get_value("status") == "admin") {

      } else if (!$this->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
        $rtn[] = "Unable to save Product Sale, user does not have correct permissions.";
      }
    }
    return parent::validate($rtn);
  }
 
  function is_owner() {
    global $current_user;
    return !$this->get_id() || $this->get_value("productSaleCreatedUser") == $current_user->get_id();
  } 

  function delete() {
    $db = new db_alloc;
    $query = sprintf("SELECT * 
                        FROM productSaleItem 
                       WHERE productSaleID = %d"
                    , $this->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $productSaleItem = new productSaleItem;
      $productSaleItem->read_db_record($db);
      $productSaleItem->delete();
    }
    $this->delete_transactions();
    return parent::delete();
  }

  function translate_meta_tfID($tfID="") {
    global $TPL;
    
    // The special -1 and -2 tfID's represent META TF, i.e. calculated at runtime
    // -1 == META: Project TF
    if ($tfID == -1) { 
      if ($this->get_value("projectID")) {
        $project = new project();
        $project->set_id($this->get_value("projectID"));
        $project->select();
        $tfID = $project->get_value("cost_centre_tfID");
      }
      if ($tfID == -1) {
        $tfID = config::get_config_item("mainTfID");
      }

    // -2 == META: Salesperson TF
    } else if ($tfID == -2) {
      if ($this->get_value("productSaleCreatedUser")) {
        $person = new person();
        $person->set_id($this->get_value("productSaleCreatedUser")); 
        $person->select();
        $tfID = $person->get_value("preferred_tfID");
        if (!$tfID) {
          $TPL["message_bad"][] = "Unable to use META: Salesperson TF. Please ensure the Sale creator has a Preferred Payment TF.";
        }
      } else {
        $TPL["message_bad"][] = "Unable to use META: Salesperson TF. No productSaleCreatedUser set.";
      }
    }
    return $tfID;
  }
  
  function get_productSaleItems() {
    $q = sprintf("SELECT * FROM productSaleItem WHERE productSaleID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    $rows = array();
    while($row = $db->row()) {
      $rows[$row["productSaleItemID"]] = $row;
    }
    return $rows;
  }

  function get_amounts() {

    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
    $rtn = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem;
      $productSaleItem->read_row_record($row);
      //$rtn["total_spent"] += $productSaleItem->get_amount_spent();
      //$rtn["total_earnt"] += $productSaleItem->get_amount_earnt();
      //$rtn["total_other"] += $productSaleItem->get_amount_other();
      list($sp,$spcur) = array($productSaleItem->get_value("sellPrice"),$productSaleItem->get_value("sellPriceCurrencyTypeID"));

      $sellPriceCurr[$spcur] += page::money($spcur,$sp,"%m");
      $total_sellPrice += exchangeRate::convert($spcur,$sp);
      $total_margin += $productSaleItem->get_amount_margin();
      $total_unallocated += $productSaleItem->get_amount_unallocated();
    }    

    unset($sep,$label,$show);

    foreach ((array)$sellPriceCurr as $code => $amount) {
      $label.= $sep.page::money($code,$amount,"%s%mo %c");
      $sep = " + ";
      $code != config::get_config_item("currency") and $show = true;
    }
    $show && $label and $sellPrice_label = " (".$label.")";

    $rtn["total_sellPrice"] = page::money(config::get_config_item("currency"),$total_sellPrice,"%s%mo %c").$sellPrice_label;
    $rtn["total_margin"] = page::money(config::get_config_item("currency"),$total_margin,"%s%mo %c");
    $rtn["total_unallocated"] = page::money(config::get_config_item("currency"),$total_unallocated,"%s%mo %c");
    $rtn["total_unallocated_number"] = page::money(config::get_config_item("currency"),$total_unallocated,"%mo");

    return $rtn;
  }

  function create_transactions() {
    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem;
      $productSaleItem->read_row_record($row);
      $productSaleItem->create_transactions();
    }
  }

  function delete_transactions() {
    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem;
      $productSaleItem->read_row_record($row);
      $productSaleItem->delete_transactions();
    }
  }
 
  function move_forwards() {
    global $current_user;
    $status = $this->get_value("status");

    if ($status == "edit") {
      $this->set_value("status", "allocate");
      
      if (count($this->get_transactions()) == 0) {
        $this->create_transactions();
      }

    } else if ($status == "allocate") {
      $this->set_value("status", "admin");

    } else if ($status == "admin" && $this->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
      $this->set_value("status", "finished");
    }
  }

  function get_transactions($productSaleItemID=false) {
    $rows = array();
    $productSaleItemID and $productSaleItemID_sql = sprintf("AND productSaleItemID = %d",$productSaleItemID);
    $query = sprintf("SELECT * 
                        FROM transaction 
                       WHERE productSaleID = %d
                          %s
                    ORDER BY transactionID"
                    ,$this->get_id()
                    ,$productSaleItemID_sql);
    $db = new db_alloc();
    $db->query($query);
    while ($row = $db->row()) {
      $rows[] = $row;
    }
    return $rows;
  }

  function move_backwards() {
    global $current_user;

    if ($this->get_value("status") == "finished" && $current_user->have_role("admin")) {
      $this->set_value("status", "admin");

    } else if ($this->get_value("status") == "admin" && $current_user->have_role("admin")) {
      $this->set_value("status", "allocate");

    } else if ($this->get_value("status") == "allocate") {
      $this->set_value("status", "edit");
    }
  }

  function get_list_filter($filter=array()) {
    if ($filter["projectID"]) {
      $sql[] = sprintf("(productSale.projectID = %d)",$filter["projectID"]);
    }
    if ($filter["clientID"]) {
      $sql[] = sprintf("(productSale.clientID = %d)",$filter["clientID"]);
    }
    return $sql;
  }

  function get_list($_FORM=array()) {

    $filter = productSale::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }

    $db = new db_alloc();
    $query = sprintf("SELECT productSale.*, project.projectName, client.clientName
                        FROM productSale 
                   LEFT JOIN client ON productSale.clientID = client.clientID
                   LEFT JOIN project ON productSale.projectID = project.projectID
                    ".$f);
    $db->query($query);
    $statii = productSale::get_statii();
    $people = get_cached_table("person");
    $rows = array();
    while ($row = $db->next_record()) {
      $productSale = new productSale();
      $productSale->read_db_record($db);
      $row["amounts"] = $productSale->get_amounts();
      $row["statusLabel"] = $statii[$row["status"]];
      $row["creatorLabel"] = $people[$row["productSaleCreatedUser"]]["name"];
      $body.= productSale::get_list_body($row,$_FORM);
    }

    $header = productSale::get_list_header($_FORM);
    $footer = productSale::get_list_footer($_FORM);

    if ($body) {
      return $header.$body.$footer;
    } else {
      return "<table style=\"width:100%\"><tr><td style=\"text-align:center\"><b>No Product Sales Found</b></td></tr></table>";
    }
  }

  function get_list_header($_FORM=array()) {
    $ret[] = "<table class=\"list sortable\">";
    $ret[] = "<tr>";
    $ret[] = "  <th class=\"sorttable_numeric\">ID</th>";
    $ret[] = "  <th>Creator</th>";
    $ret[] = "  <th>Date</th>";
    $ret[] = "  <th>Client</th>";
    $ret[] = "  <th>Project</th>";
    $ret[] = "  <th>Status</th>";
    $ret[] = "  <th class=\"right\">Margin</th>";
    $ret[] = "  <th class=\"right\">Unallocated</th>";
    $ret[] = "</tr>";
    return implode("\n",$ret);
  }

  function get_list_body($sale,$_FORM=array()) {
    global $TPL;
    $TPL["_FORM"] = $_FORM;
    $TPL["sale"] = $sale;
    $TPL = array_merge($TPL,(array)$sale);
    return include_template(dirname(__FILE__)."/../templates/productSaleListR.tpl", true);
  }

  function get_list_footer($_FORM=array()) {
    $ret[] = "</table>";
    return implode("\n",$ret);
  }

  function get_link($row=array()) {
    global $TPL;
    if (is_object($this)) {
      return "<a href=\"".$TPL["url_alloc_productSale"]."productSaleID=".$this->get_id()."\">".$this->get_id()."</a>";
    } else {
      return "<a href=\"".$TPL["url_alloc_productSale"]."productSaleID=".$row["productSaleID"]."\">".$row["productSaleID"]."</a>";
    }
  }

  function get_statii() {
    return array("create"=>"Create", "edit"=>"Add Sale Items", "allocate" =>"Allocate", "admin"=>"Administrator", "finished"=>"Completed");
  }

}


?>
