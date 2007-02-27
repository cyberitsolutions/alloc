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

if (!have_entity_perm("config", PERM_UPDATE, $current_user, true)) {
  die("Permission denied.");
}

$configName = $_POST["configName"] or $configName = $_GET["configName"];
$TPL["configName"] = $configName;

if ($configName) {
  $config = new config;
  $id = $config->get_config_item_id($configName);
  $config->set_id($id);  
  $config->select();  
}

if ($_POST["save"]) {

  $arr = $config->get_config_item($configName);
  $arr[$_POST["key"]] = $_POST["value"];
  $config->set_value("value",serialize($arr));
  $config->save();

} else if ($_POST["delete"]) {

  $arr = $config->get_config_item($configName);
  unset($arr[$_POST["key"]]);
  $config->set_value("value",serialize($arr));
  $config->save();
}


include_template("templates/configEdit.tpl");




?>
