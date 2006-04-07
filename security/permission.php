<?php
require_once("alloc.inc");

$permission = new permission;

if (isset($permissionID)) {
  $permission->set_id($permissionID);
  $permission->select();
}

if (is_array($actions_array)) {
  $actions = 0;
  reset($actions_array);
  while (list(, $a) = each($actions_array)) {
    $actions = $actions | $a;
  }
}

$permission->read_globals();
$permission->set_tpl_values();

if (!$permission->get_value("tableName")) {
  $table_name_options = get_entity_table_names();
  asort($table_name_options);
  $TPL["tableNameOptions"] = get_options_from_array($table_name_options, $permission->get_value("tableName"), false);
  include_template("templates/permissionTableM.tpl");
  page_close();
  exit();
}

if (isset($save)) {
  $permission->save();
} else if (isset($delete)) {
  $permission->delete();
  page_close();
  header("Location: ".$TPL["url_alloc_permissionList"]);
  exit();
}

$TPL["personOptions"] = get_options_from_query("SELECT personID, username FROM person ORDER BY username", "username", "personID", $permission->get_value("personID"));
$TPL["roleNameOptions"] = get_options_from_array(array("god", "admin", "manage", "employee"), $permission->get_value("roleName"), false);
$TPL["allowOptions"] = get_options_from_array(array("Y"=>"Yes", "N"=>"No"), $permission->get_value("allow"));

$table_name = $permission->get_value("tableName");
$entity = new $table_name;
$TPL["actionOptions"] = get_options_from_array($entity->permissions, $permission->get_value("actions"), true, 40, true);

include_template("templates/permissionM.tpl");

page_close();



?>
