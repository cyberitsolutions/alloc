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

class product extends db_entity {
  var $classname = "product";
  var $data_table = "product";
  var $display_field_name = "productName";

  function product() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("productID");
    $this->data_fields = array("productName"=>new db_field("productName")
                        ,"buyCost"=>new db_field("buyCost")
                        ,"sellPrice"=>new db_field("sellPrice")
                        ,"description"=>new db_field("description")
                        ,"comment"=>new db_field("comment")
                      );
  }

    function delete() {
      $db = new db_alloc;
      $query = sprintf("DELETE FROM productCost WHERE productID = ". $this->get_id());
      $db->query($query);
      return parent::delete();
    }

  /* format is an array in column order
   * Each element is a hash:
   *  title=>column title string
   *  field=>DB field name (from rows) _or_ array argument for the sprintf thing
   *  format=>sprintf formatting string to convert field to display output
   *    format may also be a callback that takes an array of fields and returns some html.
   */

  /* This is admittedly a somewhat nasty way of doing things, but the
   * alternative is a massive branching function that manually picks out all
   * the values.
   */

  function db_to_html($rows, $format) {
    
    // helper function for array_map
    function look_up($k) {
      return $rows[$k];
    }
 
    global $TPL;
    $output = array();
    $titles = array();
    foreach ($format as $row) {
      $titles[] = $row["title"];
    }

    foreach ($rows as $row) {
      $line = array();
      foreach ($format as $fmt) {
        // extract the fields
        if (is_array($fmt["field"])) {
          $args = array_map("look_up", $fmt["field"]);
        } else {
          $args = array($row[$fmt["field"]]);
        }
        if (is_callable($fmt["format"])) {
          // $fmt["format"] is a callback
          $line[] = $fmt["format"]($args);
        } else {
          $line[] = vsprintf($fmt["format"], $row[$fmt["field"]]);
        }
      }
      $lines[] = $line;
    }
    $TPL["productSale_rowtitles"] = $titles;
    $TPL["productSale_rows"] = $lines;
    include_template("../product/templates/productSaleListS.tpl");
  }
  
  function get_product_list($_FORM) {
    $filter = array();
    foreach ($_FORM as $k => $v) {
      $filter[] = $k . " = " . $v;
    }
    $query = "SELECT * FROM product";
    $filter and $query .= " WHERE " . implode(" AND ", $filter);
    $query .= ";";
    $db = new db_alloc;
    $db->query($query);
    while ($row = $db->next_record()) {
      $rows[] = $row;
    }
    return $rows;
  }


}





?>
