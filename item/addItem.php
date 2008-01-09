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

require_once("../alloc.php");

$current_user->check_employee();

global $TPL;

$item = new item;

if ($_POST["save"]) {
  $item = new item;
  $item->read_globals();
  $item->save();
}

if ($_POST["import_from_file"]) {
  if (is_uploaded_file($_FILES["import_file"]["tmp_name"])) {
    $new_items = file($_FILES["import_file"]["tmp_name"]);
    for ($i = 1; $i < count($new_items); $i++) {
      $item = new item;
      $item->read_globals();
      $line = str_replace("\"", "", $new_items[$i]);
      $entry = explode("\t", $line);
      $item->set_value('itemName', $entry[0]);
      $item->set_value('itemAuthor', $entry[1]);
      $item->set_value('itemNotes', $entry[2]);
      $item->set_value('itemType', $_POST["itemType"]);
      $item->set_value('personID', $current_user);
      $item->save();
    }
  } else {
    $TPL["message"][] = "Uploaded document error.  Please try again.";
  }
}

if ($_POST["update_item"]) {
  $item = new item;
  $item->set_id($_POST["update_itemID"]);
  $item->select();
  $item->set_value("itemName", $_POST["update_itemName"]);
  $item->set_value("itemNotes", $_POST["update_itemNotes"]);
  $item->set_value("itemType", $_POST["update_itemType"]);
  $item->save();
}

if ($_POST["remove_items"]) {
  for ($i = 0; $i < count($_POST["itemID"]); $i++) {
    $item = new item;
    $item->set_id($_POST["itemID"][$i]);
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

if ($_POST["edit_items"]) {
  $item = new item;
  $item->set_id($_POST["itemID"][0]);
  $item->select();

  if (count($_POST["itemID"]) < 1) {
    $TPL["message"][] = "You Must Select An Item";
  } else {
    if (count($_POST["itemID"]) > 1) {
      $TPL["message"][] = "Can Only Edit 1 Item At A Time";
    } 
    $TPL["edit_options"] =
      "<table><tr>\n"
     ."  <td>Name: </td>\n"
     ."  <td colspan=\"2\"><input size=\"40\" type=\"text\" name=\"update_itemName\" value=\"".$item->get_value("itemName")."\"></td>\n"
     ."</tr><tr>\n"
     ."  <td>Notes: </td>\n"
     ."  <td colspan=\"2\"><input size=\"40\" type=\"text\" name=\"update_itemNotes\" value=\"".$item->get_value("itemNotes")."\"></td>\n"
     ."</tr><tr>\n"
     ."  <td>Type: </td>\n"
     ."  <td><select name=\"update_itemType\" value=\"".$item->get_value("itemType")."\">"
     .        get_options_from_array(array("book"=>"Book", "cd"=>"CD", "other"=>"Other"), $item->get_value("itemType"), true)
     ."       </select>"
     ."    <input type=\"hidden\" name=\"update_itemID\" value=\"".$item->get_id()."\"></td>"
     ."  <td align=\"right\"><input type=\"submit\" name=\"update_item\" value=\"Save Changes\"></td>\n"
     ."</tr><td colspan=\"3\"><hr></td></tr>\n"
     ."</tr></table>\n";
  }
}

$TPL["main_alloc_title"] = "Edit Items - ".APPLICATION_NAME;
include_template("templates/addItemM.tpl");


page_close();



?>
