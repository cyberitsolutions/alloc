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

class htmlElement extends db_entity {
  var $data_table = "htmlElement";

  function htmlElement() {
    $this->db_entity();
    $this->key_field = new db_field("htmlElementID");
    $this->data_fields = array("htmlElementTypeID"=>new db_field("htmlElementTypeID")
                              ,"htmlElementParentID"=>new db_field("htmlElementParentID",array("empty_to_null"=>false))
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

  function get_list_children($htmlElementParentID=0, $padding=5) {
    $rows = array();
    $db = new db_alloc();
    $q = sprintf("SELECT htmlElement.*,htmlElementType.hasChildElement
                    FROM htmlElement 
               LEFT JOIN htmlElementType on htmlElement.htmlElementTypeID = htmlElementType.htmlElementTypeID
                   WHERE htmlElementParentID = %d
                GROUP BY htmlElement.htmlElementID
                 ",$htmlElementParentID);
    $db->query($q);
    while ($row = $db->row()) {

      $row["padding"] = $padding;
      
      if ($row["hasChildElement"]) {
        $rows[$row["htmlElementID"]] = $row;
        $row["padding"] += 15;

        $arr = htmlElement::get_list_children($row["htmlElementID"],$row["padding"]);
        if (is_array($arr) && count($arr)) {
          $rows = array_merge($rows,$arr);
        }
        $row["padding"] -= 15;

      } else {
        $rows[$row["htmlElementID"]] = $row;
      }
      
    }

    return $rows;
  }

  function createDefaultAttributes() {

    $db = new db_alloc();
    $q = sprintf("SELECT * FROM htmlAttributeType WHERE htmlElementTypeID = '%s' OR htmlElementTypeID IS NULL",db_esc($this->get_value("htmlElementTypeID")));
    $db->query($q);
    while ($row = $db->next_record()) {
      $htmlAttribute= new htmlAttribute();
      $htmlAttribute->set_value("htmlElementID",$this->get_id());
      $htmlAttribute->set_value("name",$row["name"]);
      $default = $row["defaultValue"] or $default = $this->get_value("handle");
      $htmlAttribute->set_value("value",$default);
      $htmlAttribute->save();
    }

  }

  function delete() {
    $db = new db_alloc();
    $q = sprintf("DELETE FROM htmlAttribute WHERE htmlElementID = %d",$this->get_id());
    $db->query($q);
    parent::delete();
  }


}



?>
