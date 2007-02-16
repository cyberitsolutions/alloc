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

require_once("../alloc.php");


function build_html_tag($htmlElementID,$value) {
  $db = new db_alloc();
 
  $q = sprintf("SELECT * FROM htmlElement WHERE htmlElementID = %d",$htmlElementID);
  $db->query($q);
  $row = $db->next_record();

  $q = sprintf("SELECT * FROM htmlElementType WHERE htmlElementTypeID = %d",$row["htmlElementTypeID"]);
  $db->query($q);
  $row_type = $db->next_record();

  $str[] = "<".$row_type["name"];

  $q = sprintf("SELECT * FROM htmlAttribute WHERE htmlElementID = '%s'",db_esc($row["htmlElementID"]));
  $db->query($q);
  while ($row_attr = $db->next_record()) {
    $str[] = $row_attr["name"]."=\"".$row_attr["value"]."\"";
  }

  if ($row_type["hasValueAttribute"]) {
    $str[] = $row_type["valueAttributeName"]."=\"".$value."\"";
  }

  if (!$row_type["hasEndTag"]) {
    $str[] = " />";
  } else {
    $str[] = ">";
  }

  if ($row_type["hasValueContent"]) {
    $str[] = $value;
  }
  
  if ($row_type["hasChildElement"]) {
    $q = sprintf("SELECT * FROM htmlElement WHERE htmlElementParentID = %d AND enabled = 1 ORDER BY sequence",$row["htmlElementID"]);
    $db->query($q);
    while ($r = $db->next_record()) {
      $str[] = array_merge($str,build_html_tag($r["htmlElementID"]));
    }
  } 

  if ($row_type["hasEndTag"]) {
    $str[] = "</".$row_type["name"].">";
  }

  return $str;
}



function build_html_element($handle,$value) {
  $db = new db_alloc();
  $q = sprintf("SELECT * FROM htmlElement WHERE handle = '%s'",db_esc($handle));
  $db->query($q);
  $row = $db->next_record();

  $str = build_html_tag($row["htmlElementID"],$value);

  if (is_array($str))
  return implode("\n",$str);

  #$q = sprintf("SELECT * FROM htmlElementType WHERE htmlElementTypeID = %d",$row["htmlElementTypeID"]);
  #$db->query($q);
  #$row_type = $db->next_record();

}





$htmlElement = new htmlElement();
if ($_POST["htmlElementID"]) {
  $htmlElement->set_id($_POST["htmlElementID"]);
  $htmlElement->select();
}
$htmlElement->read_globals();

$htmlAttribute = new htmlAttribute();
$htmlAttribute->read_globals();

if ($_POST["save"]) {
  
  !$_POST["enabled"] and $htmlElement->set_value("enabled",0);
  $htmlElement->save();

} else if ($_POST["save_attribute"]) {
  $htmlAttribute->save();

} else if ($_POST["delete_attribute"] && $_POST["htmlAttributeID"]) {
  $htmlAttribute->delete();
}

$htmlElement->set_tpl_values();





$TPL["htmlElementType_options"] = get_select_options("SELECT htmlElementTypeID as name, handle as value FROM htmlElementType",$htmlElement->get_value("htmlElementTypeID"));
$htmlElement->get_value("enabled") and $TPL["enabled_checked"] = " checked";


include_template("templates/configHtmlM.tpl");

if (0) {

  $db = new db_alloc();
  $q = sprintf("SELECT * FROM htmlElementType WHERE htmlElementTypeID = %d",$_POST["htmlElementTypeID"]);
  $db->query($q);
  $row = $db->row();

  // Get default attributes


  // If this element type has children allow adding of children
  if ($row["hasChildElement"]) {
    $q = sprintf("SELECT * FROM htmlElementType WHERE parentHtmlElementID = %d",$_POST["htmlElementTypeID"]);
    $db->query($q);
    while ($row_child = $db->row()) {
      

    }

  }


}



?>
