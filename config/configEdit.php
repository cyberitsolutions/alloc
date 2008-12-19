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

  if ($configName == "taskStatusOptions" && is_array($_POST["status"])) {
    foreach($_POST["status"] as $k => $v) { 
      $arr[$_POST["status"][$k]][$_POST["subStatus"][$k]]   = array("label"=>$_POST["label"][$k],"colour"=>$_POST["colour"][$k]);
    }
    $config->set_value("value",serialize($arr));
    $config->save();

  } else {
    $arr = $config->get_config_item($configName);
    $arr[$_POST["key"]] = $_POST["value"];
    $config->set_value("value",serialize($arr));
    $config->save();
  }

} else if ($_POST["delete"]) {

  $arr = $config->get_config_item($configName);
  unset($arr[$_POST["key"]]);
  $config->set_value("value",serialize($arr));
  $config->save();
}


if (file_exists("templates/configEdit_".$configName.".tpl")) {
  include_template("templates/configEdit_".$configName.".tpl");
} else {
  include_template("templates/configEdit.tpl");
}




?>
