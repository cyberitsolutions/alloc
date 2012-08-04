<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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
  alloc_error("Permission denied.",true);
}

$configName = $_POST["configName"] or $configName = $_GET["configName"];
$TPL["configName"] = $configName;

$configType = $_POST["configType"] or $configType = $_GET["configType"] or $configType = "array";
$TPL["configType"] = $configType;

if ($configName) {
  $config = new config();
  $id = $config->get_config_item_id($configName);
  $config->set_id($id);  
  $config->select();  
}

if ($_POST["save"]) {

  if($configType == "people") {
    $arr = $config->get_config_item($configName);
    if(!in_array($_POST['value'], $arr)) {
      $arr[] = $_POST["value"];
      $config->set_value("value",serialize($arr));
      $config->save();
    }
  } else {
    $arr = $config->get_config_item($configName);
    $arr[$_POST["key"]] = $_POST["value"];
    $config->set_value("value",serialize($arr));
    $config->save();
  }

} else if ($_POST["delete"]) {

  $arr = $config->get_config_item($configName);
  if($configType == "people") {
    unset($arr[array_search($_POST["value"], $arr)]);
  } else {
    unset($arr[$_POST["key"]]);
  }
  $config->set_value("value",serialize($arr));
  $config->save();
}


if (file_exists("templates/configEdit_".$configName.".tpl")) {
  include_template("templates/configEdit_".$configName.".tpl");
} elseif (file_exists("templates/configEdit_".$configType.".tpl")) {
  include_template("templates/configEdit_".$configType.".tpl");
} else {
  include_template("templates/configEdit.tpl");
}




?>
