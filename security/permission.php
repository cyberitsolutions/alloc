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


$permission = new permission();
$permissionID = $_POST["permissionID"] or $permissionID = $_GET["permissionID"];

if ($permissionID) {
  $permission->set_id($permissionID);
  $permission->select();
}

$actions_array = $_POST["actions_array"];
if (is_array($actions_array)) {
  $actions = 0;
  foreach($actions_array as $k => $a) {
    $actions = $actions | $a;
  }
}

$permission->read_globals();
$permission->set_values();


if (!$permission->get_value("tableName")) {
  global $modules;
  $entities = array();
  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $mod_entities = $module->db_entities;
    $entities = array_merge($entities, $mod_entities);
  }

  $table_names = array();
  reset($entities);
  while (list(, $entity_name) = each($entities)) {
    $entity = new $entity_name;
    $table_names[] = $entity->data_table;
  }

  $ops = $table_names;
  asort($ops);
  foreach ($ops as $v) {
    $table_name_options[$v] = $v;
  }
  $TPL["tableNameOptions"] = page::select_options($table_name_options, $permission->get_value("tableName"));
  include_template("templates/permissionTableM.tpl");
  exit();
}

if ($_POST["save"]) {
  $permission->set_value("actions",$actions);
  $permission->set_value("comment",rtrim($permission->get_value("comment")));
  $permission->save();
  alloc_redirect($TPL["url_alloc_permissionList"]);
} else if ($_POST["delete"]) {
  $permission->delete();
  alloc_redirect($TPL["url_alloc_permissionList"]);
}

// necessary
$permission->select();

$TPL["roleNameOptions"] = page::select_options(permission::get_roles(), $permission->get_value("roleName"));

$table_name = $_POST["tableName"] or $table_name = $permission->get_value("tableName");
$entity = new $table_name;

foreach ($entity->permissions as $value => $label) {
  if (($permission->get_value("actions") & $value) == $value) {
    $sel[] = $value;
  }
}

$TPL["actionOptions"] = page::select_options($entity->permissions, $sel);

$TPL["main_alloc_title"] = "Edit Permission - ".APPLICATION_NAME;

include_template("templates/permissionM.tpl");

?>
