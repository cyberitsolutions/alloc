<?php
require_once("alloc.inc");



$config = new config;

$db = new db_alloc;
$db->query("SELECT name,value FROM config");
while ($db->next_record()) {
  $fields_to_save[] = $db->f("name");
  $TPL[$db->f("name")] = $db->f("value");
}


if ($save) {
  foreach ($fields_to_save as $name) {
    if ($_POST[$name]) {
      $id = $config->get_config_item_id($name);
      $c = new config;
      $c->set_id($id);
      $c->select();
      $c->set_value("value",$_POST[$name]);
      $c->save();
      $TPL[$name] = $_POST[$name];
    }
  }
}


$db->query("SELECT * FROM tf ORDER BY tfName");
$TPL["tfOptions"] = get_option("", "0", false)."\n";
$TPL["tfOptions"].= get_options_from_db($db, "tfName", "tfID", $config->get_config_item("cybersourceTfID"));


$db = new db_alloc;
$display = array("", "username", ", ", "emailAddress");

$db->query("SELECT * FROM person ORDER BY username");
$TPL["timeSheetAdminEmailOptions"] = get_option("Time Sheet Admin (email)", "0", false)."\n";
$TPL["timeSheetAdminEmailOptions"].= get_options_from_db($db, $display, "personID", $config->get_config_item("timeSheetAdminEmail"));



include_template("templates/configM.tpl");



?>
