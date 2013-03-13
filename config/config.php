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

if ($_POST["test_email_gateway"]) {
  $info["host"] = config::get_config_item("allocEmailHost");
  $info["port"] = config::get_config_item("allocEmailPort");
  $info["username"] = config::get_config_item("allocEmailUsername");
  $info["password"] = config::get_config_item("allocEmailPassword");
  $info["protocol"] = config::get_config_item("allocEmailProtocol");

  if (!$info["host"]) {
    alloc_error("Email mailbox host not defined, assuming email receive function is inactive.");
  } else {
    $mail = new email_receive($info,$lockfile);
    $mail->open_mailbox(config::get_config_item("allocEmailFolder"));
    $mail->check_mail();
    $TPL["message_good"][] = "Connection succeeded!";
  }

}


$config = new config();

$db = new db_alloc();
$db->query("SELECT name,value,type FROM config");
while ($db->next_record()) {
  $fields_to_save[] = $db->f("name");
  $types[$db->f("name")] = $db->f("type");

  if ($db->f("type") == "text") {
    $TPL[$db->f("name")] = page::htmlentities($db->f("value"));

  } else if ($db->f("type") == "array") {
    $TPL[$db->f("name")] = unserialize($db->f("value"));
  }
}


#echo "<pre>".print_r($_POST,1)."</pre>";

if ($_POST["update_currencyless_transactions"] && $_POST["currency"]) {
  $db = new db_alloc();
  $q = prepare("UPDATE transaction SET currencyTypeID = '%s' WHERE currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE transactionRepeat SET currencyTypeID = '%s' WHERE currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE product SET sellPriceCurrencyTypeID = '%s' WHERE sellPriceCurrencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE productCost SET currencyTypeID = '%s' WHERE currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE productSaleItem SET sellPriceCurrencyTypeID = '%s' WHERE sellPriceCurrencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE project SET currencyTypeID = '%s' WHERE currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE timeSheet SET currencyTypeID = '%s' WHERE currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);
  $q = prepare("UPDATE invoice SET invoice.currencyTypeID = '%s' WHERE invoice.currencyTypeID IS NULL",$_POST["currency"]);
  $db->query($q);

  // Update currencyType table too
  $q = prepare("UPDATE currencyType SET currencyTypeSeq = 1, currencyTypeActive = true WHERE currencyTypeID = '%s'",$_POST["currency"]);
  $db->query($q);
  $_POST["save"] = true;
}

if ($_POST["fetch_exchange_rates"]) {
  $rtn = exchangeRate::download();
  $rtn and $TPL["message_good"] = $rtn;
}




if ($_POST["save"]) {

  if ($_POST["hoursInDay"]) {
    $db = new db_alloc();
    $day = $_POST["hoursInDay"]*60*60;
    $q = prepare("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'day'",$day);
    $db->query($q);
    $q = prepare("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'week'",($day*5));
    $db->query($q);
    $q = prepare("UPDATE timeUnit SET timeUnitSeconds = '%d' WHERE timeUnitName = 'month'",(($day*5)*4));
    $db->query($q);
  }

  // remove bracketed [Alex Lance <]alla@cyber.com.au[>] bits, leaving just alla@cyber.com.au
  if ($_POST["AllocFromEmailAddress"]) {
    $_POST["AllocFromEmailAddress"] = preg_replace("/^.*</","",$_POST["AllocFromEmailAddress"]);
    $_POST["AllocFromEmailAddress"] = str_replace(">","",$_POST["AllocFromEmailAddress"]);
  }

  // Save the companyLogo and a smaller version too.
  if ($_FILES["companyLogo"] && !$_FILES["companyLogo"]["error"]) {
    $img = image_create_from_file($_FILES["companyLogo"]["tmp_name"]);
    if ($img) {
      imagejpeg($img, ALLOC_LOGO, 100);
      $x = imagesx($img);
      $y = imagesy($img);
      $save = imagecreatetruecolor($x/($y/40), $y/($y/40));
      imagecopyresized($save, $img, 0, 0, 0, 0, imagesx($save), imagesy($save), $x, $y);
      imagejpeg($save, ALLOC_LOGO_SMALL, 100);
    }
  }

  foreach ($_POST as $name => $value) {

    if (in_array($name,$fields_to_save)) {

      $id = $config->get_config_item_id($name);
      $c = new config();
      $c->set_id($id);
      $c->select();

      if ($types[$name] == "text") {
        //current special case for the only money field
        if ($name == "defaultTimeSheetRate") {
          $value = page::money(0, $_POST[$name], "%mi");
          $c->set_value("value",$value);
        } else {
          $c->set_value("value",$_POST[$name]);
        }
        $TPL[$name] = page::htmlentities($value);
      } else if ($types[$name] == "array") {
        $c->set_value("value",serialize($_POST[$name]));
        $TPL[$name] = $_POST[$name];
      }
      $c->save();
      $TPL["message_good"] = "Saved configuration.";
    }
  }

  // Handle the only checkbox specially. If more checkboxes are added this 
  // should be rewritten.
  #echo var_dump($_POST);
  if ($_POST['sbs_link'] == "rss" && !$_POST['rssShowProject']) {
    $c = new config();
    $c->set_id($config->get_config_item_id('rssShowProject'));
    $c->select();
    $c->set_value("value", '0');
    $c->save();
  }

  $TPL["message"] or $TPL["message_good"] = "Saved configuration.";

} else if ($_POST["delete_logo"]) {
  foreach (array(ALLOC_LOGO,ALLOC_LOGO_SMALL) as $logo) {
    if (file_exists($logo)) {
      if (unlink($logo)) {
        $TPL["message_good"][] = "Deleted ".$logo;
      }
    }
    if (file_exists($logo)) {
      alloc_error("Unable to delete ".$logo);
    }
  }
}


$config = new config();
get_cached_table("config",true); // flush cache

if (has("finance")) {
  $tf = new tf();
  $options = $tf->get_assoc_array("tfID","tfName");
}
$TPL["mainTfOptions"] = page::select_options($options, $config->get_config_item("mainTfID"));
$TPL["outTfOptions"] = page::select_options($options, $config->get_config_item("outTfID"));
$TPL["inTfOptions"] = page::select_options($options, $config->get_config_item("inTfID"));
$TPL["taxTfOptions"] = page::select_options($options, $config->get_config_item("taxTfID"));
$TPL["expenseFormTfOptions"] = page::select_options($options, $config->get_config_item("expenseFormTfID"));

$tabops = array("home"=>"Home"
               ,"client"=>"Clients"
               ,"project"=>"Projects"
               ,"task"=>"Tasks"
               ,"time"=>"Time"
               ,"invoice"=>"Invoices"
               ,"sale"=>"Sales"
               ,"person"=>"People"
               ,"wiki"=>"Wiki"
               ,"inbox"=>"Inbox"
               ,"tools"=>"Tools"
                );
$selected_tabops = $config->get_config_item("allocTabs") or $selected_tabops = array_keys($tabops);
$TPL["allocTabsOptions"] = page::select_options($tabops, $selected_tabops);

$m = new meta("currencyType");
$currencyOptions = $m->get_assoc_array("currencyTypeID","currencyTypeName");
$TPL["currencyOptions"] = page::select_options($currencyOptions, $config->get_config_item("currency"));

$db = new db_alloc();
$display = array("", "username", ", ", "emailAddress");


$person = new person();
$people =& get_cached_table("person");
foreach ($people as $p) {
  $peeps[$p["personID"]] = $p["name"];
}

// get the default time sheet manager/admin options
$TPL["defaultTimeSheetManagerListText"] = get_person_list(config::get_config_item("defaultTimeSheetManagerList"));
$TPL["defaultTimeSheetAdminListText"] = get_person_list(config::get_config_item("defaultTimeSheetAdminList"));

$days =  array("Sun"=>"Sun","Mon"=>"Mon","Tue"=>"Tue","Wed"=>"Wed","Thu"=>"Thu","Fri"=>"Fri","Sat"=>"Sat");
$TPL["calendarFirstDayOptions"] = page::select_options($days,$config->get_config_item("calendarFirstDay"));

$TPL["timeSheetPrintOptions"] = page::select_options($TPL["timeSheetPrintOptions"],$TPL["timeSheetPrint"]);

$commentTemplate = new commentTemplate();
$ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName");

$TPL["rssStatusFilterOptions"] = page::select_options(task::get_task_statii_array(true), $config->get_config_item("rssStatusFilter"));

if (has("timeUnit")) {
  $timeUnit = new timeUnit();
  $rate_type_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelB");
}
$TPL["timesheetRate_options"] = page::select_options($rate_type_array, $config->get_config_item("defaultTimeSheetUnit"));

$TPL["main_alloc_title"] = "Setup - ".APPLICATION_NAME;
include_template("templates/configM.tpl");

function get_person_list($personID_array) {
  global $peeps;
  $people = array();
  foreach($personID_array as $personID) {
    $people[] = $peeps[$personID];
  }
  if(count($people) > 0) {
    return implode(", ", $people);
  } else {
    return "<i>none</i>";
  }
}

?>
