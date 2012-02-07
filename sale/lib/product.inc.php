<?php

/*
 *
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions Pty. Ltd.
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

class product extends db_entity {
  public $classname = "product";
  public $data_table = "product";
  public $display_field_name = "productName";
  public $key_field = "productID";
  public $data_fields = array("productName"
                             ,"sellPrice" => array("type"=>"money","currency"=>"sellPriceCurrencyTypeID")
                             ,"sellPriceCurrencyTypeID"
                             ,"sellPriceIncTax" => array("empty_to_null"=>false)
                             ,"description"
                             ,"comment"
                             ,"productActive"
                             );

  function delete() {
    $this->set_value("productActive",0);
    $this->save();
  }

  function get_list_filter($filter) {
    // stub function for one day when you can filter products
    return $sql;
  }

  function get_list($_FORM=array()) {

    $filter = product::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }

    // Put the inactive ones down the bottom.
    $f .= " ORDER BY productActive DESC"; 

    $query = sprintf("SELECT * FROM product ".$f);
    $db = new db_alloc;
    $db->query($query);
    while ($row = $db->next_record()) {
      $product = new product;
      $product->read_db_record($db);
      $body.= product::get_list_body($row,$_FORM);
    }

    $header = product::get_list_header($_FORM);
    $footer = product::get_list_footer($_FORM);
    
    if ($body) {
      return $header.$body.$footer;
    } else {
      return "<table style=\"width:100%\"><tr><td style=\"text-align:center\"><b>No Products Found</b></td></tr></table>";
    }
  }

  function get_list_header($_FORM=array()) {
    global $TPL;
    $ret[] = "<table class=\"list sortable\">";
    $ret[] = "<tr>";
    $ret[] = "  <th>Product</th>";
    $ret[] = "  <th>Description</th>";
    $ret[] = "  <th>Sell Price</th>";
    $ret[] = "  <th>Active</th>";
    $ret[] = "</tr>";
    return implode("\n",$ret);
  }

  function get_list_body($row,$_FORM=array()) {
    global $TPL;
    $ret[] = "<tr>";
    $ret[] = "  <td class=\"nobr\">".product::get_link($row)."&nbsp;</td>";
    $ret[] = "  <td>".page::htmlentities($row["description"])."&nbsp;</td>";
    $ret[] = "  <td class=\"nobr\">".page::money($row["sellPriceCurrencyTypeID"],$row["sellPrice"],"%s%mo %c")."&nbsp;</td>";
    $ret[] = "  <td class=\"nobr\">".($row["productActive"] ? "Yes" : "No")."</td>";
    $ret[] = "</tr>";
    return implode("\n",$ret);
  }

  function get_list_footer($_FORM=array()) {
    $ret[] = "</table>";
    return implode("\n",$ret);
  }

  function get_link($row=array()) {
    global $TPL;
    if (is_object($this)) {
      return "<a href=\"".$TPL["url_alloc_product"]."productID=".$this->get_id()."\">".$this->get_value("productName",DST_HTML_DISPLAY)."</a>";
    } else {
      return "<a href=\"".$TPL["url_alloc_product"]."productID=".$row["productID"]."\">".page::htmlentities($row["productName"])."</a>";
    } 
  }

  function get_list_vars() {
    // stub function for one day when you can specify list parameters
    return array();
  }

  function get_buy_cost($id=false) {
    $id or $id = $this->get_id();
    $db = new db_alloc();
    $q = sprintf("SELECT amount, currencyTypeID, tax
                    FROM productCost
                   WHERE isPercentage != 1
                     AND productID = %d
                     AND productCostActive = true
                 ",$id);
    $db->query($q);
    while ($row = $db->row()) {
      if ($row["tax"]) {
        list($amount_minus_tax,$amount_of_tax) = tax($row["amount"]);
        $row["amount"] = $amount_minus_tax;
      }
      $amount += exchangeRate::convert($row["currencyTypeID"],$row["amount"]);
    }
    return $amount;
  }

}

?>
