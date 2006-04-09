<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

$current_user->check_employee();

global $save, $TPL;

$item = new item;

if ($save) {
  $item = new item;
  $item->read_globals();
  $item->save();
}

global $itemType;

if ($import_from_file) {
  if ($import_file != "none") {
    if (is_uploaded_file($import_file)) {
      $new_items = file($import_file);
      for ($i = 1; $i < count($new_items); $i++) {
        $item = new item;
        $item->read_globals();
        $line = str_replace("\"", "", $new_items[$i]);
        $entry = explode("\t", $line);
        $item->set_value('itemName', $entry[0]);
        $item->set_value('itemAuthor', $entry[1]);
        $item->set_value('itemNotes', $entry[2]);
        $item->set_value('itemType', $itemType);
	$item->set_value('personID', $current_user);
        // echo "<br>itemName: " . $entry[0] . " --itemAuthor" . $entry[1] . " and Publisher: " . $entry[2] . "--itemType: " . $itemType; 
        $item->save();
      }
      // $TPL["import_results"] = $i . " items successfully imported.";
    } else {
      $TPL["import_results"] = "Uploaded document error.  Please try again.";
    }
  }
}

if ($update_item) {
  $item = new item;
  $item->set_id($update_itemID);
  $item->select();
  $item->set_value("itemName", $update_itemName);
  $item->set_value("itemNotes", $update_itemNotes);
  $item->set_value("itemType", $update_itemType);
  $item->save();
}

if ($remove_items) {
  for ($i = 0; $i < count($itemID); $i++) {
    $item = new item;
    $item->set_id($itemID[$i]);
    $item->select();
    $item->delete();
  }
}

//so that the user can edit the item later
$TPL["personID"] = $current_user->get_id(); 

// item types
$TPL["itemTypes"] = get_options_from_array(array("Book", "CD", "Other"), $item->get_value("itemType"), false);

  // setup item list (for removals)
$item_list = array();
$db = new db_alloc;
$db->query("SELECT * FROM item ORDER BY itemName");
while ($db->next_record()) {
  $item = new item;
  $item->read_db_record($db);
  $item_list[$item->get_id()] = $item->get_value('itemName');
}

$TPL["item_list"] = get_options_from_array($item_list, "", true);

if ($edit_items) {
  $item = new item;
  $item->set_id($itemID[0]);
  $item->select();

  if (count($itemID) < 1) {
    $TPL["edit_options"] = "<font color=\"#FF0000\">\"You Must Select An Item\"</font><br>";
  } else {
    if (count($itemID) > 1) {
      $error = "<font color=\"#FF0000\">\"Can Only Edit 1 Item At A Time\"</font><br>";
    } else {
      $error = "";
    }
    $TPL["edit_options"] =
      $error."<table><tr>\n"."  <th>Name: </th>\n"."  <td colspan=\"2\"><input size=\"40\" type=\"text\" name=\"update_itemName\" value=\"".$item->get_value("itemName")."\"></td>\n"."</tr><tr>\n"."  <th>Notes: </th>\n".
      "  <td colspan=\"2\"><input size=\"40\" type=\"text\" name=\"update_itemNotes\" value=\"".$item->get_value("itemNotes")."\"></td>\n"."</tr><tr>\n"."  <th>Type: </th>\n"."  <td><select name=\"update_itemType\" value=\"".$item->get_value("itemType")."\">".
      get_options_from_array(array("book"=>"Book", "cd"=>"CD", "other"=>"Other"), $item->get_value("itemType"), true)
      ."</select>"."<input type=\"hidden\" name=\"update_itemID\" value=\"".$item->get_id()."\"></td>"."<td align=\"right\"><input type=\"submit\" name=\"update_item\" value=\"Save Changes\"></td>\n"."</tr><td colspan=\"3\"><hr></td></tr>\n"."</tr></table>\n";
  }
}

include_template("templates/addItemM.tpl");


page_close();



?>
