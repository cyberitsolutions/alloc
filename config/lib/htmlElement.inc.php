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

class htmlElement extends db_entity {
  var $data_table = "htmlElement";

  function htmlElement() {
    $this->db_entity();
    $this->key_field = new db_field("htmlElementID");
    $this->data_fields = array("htmlElementTypeID"=>new db_field("htmlElementTypeID")
                              ,"htmlElementParentID"=>new db_field("htmlElementParentID","","",array("null_to"))
                              ,"handle"=>new db_field("handle")
                              ,"label"=>new db_field("label")
                              ,"helpText"=>new db_field("helpText")
                              ,"defaultValue"=>new db_field("defaultValue")
                              ,"sequence"=>new db_field("sequence")
                              ,"enabled"=>new db_field("enabled")
      );

  }

  function validate() {

    if (!$this->get_value("handle")) {
      $str[] = "Please enter a Unique Name";
    }

    $s = parent::validate();
    $s and $str[] = $s;

    return $str;
  }


  function get_list_children($htmlElementParentID=0) {
    $rows = array();
    $db = new db_alloc();
    $q = sprintf("SELECT * 
                    FROM htmlElement 
               LEFT JOIN htmlElementType on htmlElement.htmlElementTypeID = htmlElementType.htmlElementTypeID
                   WHERE htmlElementParentID = %d
                GROUP BY htmlElement.htmlElementID
                 ",$htmlElementParentID);
    $db->query($q);
echo "het: ".$q;
    while ($row = $db->row()) {

      if ($row["hasChildElement"]) {
        $row["padding"] += 10;

        $arr = htmlElement::get_list($row["htmlElementID"]);
        if (is_array($arr) && count($arr)) {
          $rows[$row["htmlElementID"]] = $row;
          $rows = array_merge($rows,$arr);
        }
        $row["padding"] -= 10;

      } else {
        $rows[$row["htmlElementID"]] = $row;
      }
      
    }


    return $rows;
  }


}



?>
