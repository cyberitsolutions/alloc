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



$htmlElementID = $_POST["htmlElementID"] or $htmlElementID = $_GET["htmlElementID"];
$htmlElementParentID = $_POST["htmlElementParentID"] or $htmlElementParentID = $_GET["htmlElementParentID"];



$htmlElement = new htmlElement();
if ($htmlElementID) {
  $htmlElement->set_id($htmlElementID);
  $htmlElement->select();
}
$htmlElement->read_globals();

$htmlAttribute = new htmlAttribute();
$htmlAttribute->read_globals();

if ($_POST["save"]) {

  $htmlElementParentID and $htmlElement->set_value("htmlElementParentID",$htmlElementParentID);
  !$_POST["enabled"] and $htmlElement->set_value("enabled",0);
  $exists = $htmlElement->get_id();
  $htmlElement->save();
  $exists or $htmlElement->createDefaultAttributes();

} else if ($_POST["delete"]) {
  $htmlElement->delete();
  header("Location: ".$TPL["url_alloc_configHtmlList"]);

} else if ($_POST["save_attribute"]) {
  $htmlAttribute->save();

} else if ($_POST["delete_attribute"] && $_POST["htmlAttributeID"]) {
  $htmlAttribute->delete();
}

$htmlElement->set_tpl_values();



if ($htmlElementParentID && !$htmlElement->get_value("htmlElementTypeID")) {

  $q = sprintf("SELECT * FROM htmlElement WHERE htmlElementID = %d",$htmlElementParentID);
  $htmlElement_get = new htmlElement();
  $htmlElement_get->set_id($htmlElementParentID);
  $htmlElement_get->select();
  
  $db = new db_alloc();
  $q = sprintf("SELECT htmlElementTypeID FROM htmlElementType WHERE parentHtmlElementID = %d",$htmlElement_get->get_value("htmlElementTypeID"));
  $db->query($q);
  $row = $db->row();
  $default_htmlElementTypeID = $row["htmlElementTypeID"];
  
} else {
  $default_htmlElementTypeID = $htmlElement->get_value("htmlElementTypeID");
}

$TPL["htmlElementType_options"] = get_select_options("SELECT htmlElementTypeID as name, handle as value FROM htmlElementType",$default_htmlElementTypeID);
$htmlElement->get_value("enabled") and $TPL["enabled_checked"] = " checked";
$htmlElementParentID and $TPL["htmlElementParentID"] = $htmlElementParentID;

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
