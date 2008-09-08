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

if ($_POST["test_email_gateway"]) {
  $info["host"] = config::get_config_item("allocEmailHost");
  $info["port"] = config::get_config_item("allocEmailPort");
  $info["username"] = config::get_config_item("allocEmailUsername");
  $info["password"] = config::get_config_item("allocEmailPassword");
  $info["protocol"] = config::get_config_item("allocEmailProtocol");

  if (!$info["host"]) {
    $TPL["message"][] = "Email mailbox host not defined, assuming email receive function is inactive.";
  } else {
    $mail = new alloc_email_receive($info,$lockfile);
    $mail->open_mailbox(config::get_config_item("allocEmailFolder"));
    $mail->check_mail();
    $TPL["message_good"][] = "Connection succeeded!";
  }

}


$config = new config;

$db = new db_alloc;
$db->query("SELECT name,value,type FROM config");
while ($db->next_record()) {
  $fields_to_save[] = $db->f("name");
  $types[$db->f("name")] = $db->f("type");

  if ($db->f("type") == "text") {
    $TPL[$db->f("name")] = htmlentities($db->f("value"));

  } else if ($db->f("type") == "array") {
    $TPL[$db->f("name")] = unserialize($db->f("value"));
  }
}


#echo "<pre>".print_r($_POST,1)."</pre>";

if ($_POST["save"]) {

  if ($_POST["hoursInDay"]) {
    $db = new db_alloc;
    $day = $_POST["hoursInDay"]*60*60;
    $q = sprintf("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'day'",$day);
    $db->query($q);
    $q = sprintf("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'week'",($day*5));
    $db->query($q);
    $q = sprintf("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'month'",(($day*5)*4));
    $db->query($q);
  }

  // remove bracketed [Alex Lance <]alla@cyber.com.au[>] bits, leaving just alla@cyber.com.au
  if ($_POST["AllocFromEmailAddress"]) {
    $_POST["AllocFromEmailAddress"] = preg_replace("/^.*</","",$_POST["AllocFromEmailAddress"]);
    $_POST["AllocFromEmailAddress"] = str_replace(">","",$_POST["AllocFromEmailAddress"]);
  }

  foreach ($_POST as $name => $value) {

    if (in_array($name,$fields_to_save)) {

      $id = $config->get_config_item_id($name);
      $c = new config;
      $c->set_id($id);
      $c->select();

      if ($types[$name] == "text") {
        $c->set_value("value",$_POST[$name]);
        $TPL[$name] = htmlentities($_POST[$name]);
      } else if ($types[$name] == "array") {
        $c->set_value("value",serialize($_POST[$name]));
        $TPL[$name] = $_POST[$name];
      }
      $c->save();
      $TPL["message_good"] = "Saved configuration.";
    }
  }
  $TPL["message"] or $TPL["message_good"] = "Saved configuration.";
}

$config = new config;
get_cached_table("config",true); // flush cache

$db->query("SELECT * FROM tf WHERE status = 'active' ORDER BY tfName");
$options[""] = "";
while($row = $db->row()) {
  $options[$row["tfID"]] = $row["tfName"];
}
$TPL["mainTfOptions"] = page::select_options($options, $config->get_config_item("mainTfID"));
$TPL["outTfOptions"] = page::select_options($options, $config->get_config_item("outTfID"));
$TPL["inTfOptions"] = page::select_options($options, $config->get_config_item("inTfID"));
$TPL["taxTfOptions"] = page::select_options($options, $config->get_config_item("taxTfID"));

$db = new db_alloc;
$display = array("", "username", ", ", "emailAddress");


$person = new person;
$people = get_cached_table("person");
foreach ($people as $p) {
  $peeps[$p["personID"]] = $p["name"];
}
$TPL["timeSheetManagerEmailOptions"] = page::select_options($peeps,$config->get_config_item("timeSheetManagerEmail"));
$TPL["timeSheetAdminEmailOptions"] = page::select_options($peeps,$config->get_config_item("timeSheetAdminEmail"));

$days =  array("Sun"=>"Sun","Mon"=>"Mon","Tue"=>"Tue","Wed"=>"Wed","Thu"=>"Thu","Fri"=>"Fri","Sat"=>"Sat");
$TPL["calendarFirstDayOptions"] = page::select_options($days,$config->get_config_item("calendarFirstDay"));

$TPL["timeSheetPrintOptions"] = page::select_options($TPL["timeSheetPrintOptions"],$TPL["timeSheetPrint"]);

$commentTemplate = new commentTemplate;
$ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName");
$TPL["task_email_header_options"] = page::select_options($ops,$config->get_config_item("task_email_header"));
$TPL["task_email_footer_options"] = page::select_options($ops,$config->get_config_item("task_email_footer"));

$TPL["main_alloc_title"] = "Setup - ".APPLICATION_NAME;
include_template("templates/configM.tpl");



?>
