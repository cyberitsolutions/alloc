<?php

$db = new db_alloc();

// extract the old options
$db->query("SELECT `name`, `value` FROM `config` WHERE `name` IN ('timeSheetManagerEmail', 'timeSheetAdminEmail')");

$tsm_list = array();
$tsa_list = array();

while($db->next_record()) {
  switch($db->f("name")) {
    case 'timeSheetManagerEmail':
      if($db->f("value")) {
        $tsm_list[] = $db->f("value");
      }
    break;
    case 'timeSheetAdminEmail':
      if($db->f("value")) {
        $tsa_list[] = $db->f("value");
      }
    break;
  }
}

// remove the old options from the database
$db->query("DELETE FROM `config` WHERE `name` IN ('timeSheetManagerEmail', 'timeSheetAdminEmail')");

// insert the new options, with the data from the old options
$db->query("INSERT INTO `config` (`name`, `value`, `type`) VALUES ('defaultTimeSheetAdminList', '%s', 'array')", serialize($tsa_list));
$db->query("INSERT INTO `config` (`name`, `value`, `type`) VALUES ('defaultTimeSheetManagerList', '%s', 'array')", serialize($tsm_list));


?>
