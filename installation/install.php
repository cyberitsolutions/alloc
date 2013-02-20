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

define("IN_INSTALL_RIGHT_NOW",1);
require_once("../alloc.php");

function errors_on() {
  singleton("errors_fatal",true);
  singleton("errors_format","text");
  singleton("errors_logged",true);
  singleton("errors_thrown",false);
  singleton("errors_haltdb",true);
  ini_set('display_errors',1);
}
function errors_off() {
  singleton("errors_fatal",false);
  singleton("errors_format","text");
  singleton("errors_logged",false);
  singleton("errors_thrown",false);
  singleton("errors_haltdb",false);
  ini_set("display_errors",0);
}

errors_on();
// The user hasn't set their timezone, so pull up the default and suppress the warning
$timeZone = @date_default_timezone_get();
date_default_timezone_set($timeZone);

define("IMG_TICK","<img src=\"".$TPL["url_alloc_images"]."tick.gif\" alt=\"Good\">");
define("IMG_CROSS","<img src=\"".$TPL["url_alloc_images"]."cross.gif\" alt=\"Bad\">");
$TPL["IMG_TICK"] = IMG_TICK;
$TPL["IMG_CROSS"] = IMG_CROSS;

function show_tab_1() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 1 || !$tab;
}
function show_tab_2() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 2 || $_POST["submit_stage_1"];
}
function show_tab_2a() {
  return $_POST["submit_stage_2"];
}
function show_tab_3() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 3;
}
function show_tab_3b() {
  return $_POST["test_db"];
}
function show_tab_4() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 4;
}
function show_tab_4b() {
  return $_POST["submit_stage_4"];
}

$server_user = getenv("APACHE_RUN_USER") or $server_user = "apache";


$default_allocURL = "http://".$_SERVER["SERVER_NAME"].SCRIPT_PATH;

$config_vars = array("ALLOC_DB_NAME"     => array("default"=>"alloc",              "info"=>"Database name")
                    ,"ALLOC_DB_USER"     => array("default"=>"alloc",              "info"=>"Database username")
                    ,"ALLOC_DB_PASS"     => array("default"=>"changeme",           "info"=>"Database password")
                    ,"ALLOC_DB_HOST"     => array("default"=>"localhost",          "info"=>"Database hostname")
                    ,"ATTACHMENTS_DIR"   => array("default"=>"/var/local/alloc/",  "info"=>"Enter a folder that can be used for file upload storage (outside webroot)")
                    ,"allocURL"          => array("default"=>$default_allocURL,    "info"=>"The URL for allocPSA, eg: http://example.com/alloc/")
                    ,"currency"          => array("default"=>"USD",                "info"=>"The default currency")
                    );


foreach($config_vars as $name => $arr) {
  $val = $_POST[$name] or $val = $_GET[$name];
  $val == "" && !isset($_GET[$name]) && !isset($_POST[$name]) and $val = $arr["default"];
  $name == "ATTACHMENTS_DIR" && $val and $val = rtrim($val,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  $name == "allocURL" && $val and $val = rtrim($val,"/")."/";
  $name == "currency" and $val = trim(strtoupper($val));
  $_FORM[$name] = $val;
  $get[] = $name."=".urlencode($val);
  $hidden[] = "<input type='hidden' name='".$name."' value='".$val."'>";
  $TPL[$name] = $val;
}
$TPL["config_vars"] = $config_vars;
$TPL["_FORM"] = $_FORM;
$TPL["get"] = "&".implode("&",$get);
$TPL["hidden"] = implode("\n",$hidden);

if ($_FORM["ALLOC_DB_USER"] && $_FORM["ALLOC_DB_NAME"]) {
  $db = new db($_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"],$_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_NAME"]);
  $db->verbose = false;
}

if ($_POST["refresh_tab_1"]) {
  alloc_redirect($TPL["url_alloc_installation"]."?1=1".$TPL["get"]);
  exit;
}


if ($_POST["submit_stage_2"]) {

  foreach($config_vars as $name => $arr) {
    $val = $_POST[$name] or $val = $_GET[$name];
    if (!$val) {
      $text_tab_2a[] = "Missing a value for ".$arr["info"];
      $failed = 1;
    }
  }

  if (!is_dir($_FORM["ATTACHMENTS_DIR"])) {
    $text_tab_2a[] = "This directory does not exist, please create it: ".$_FORM["ATTACHMENTS_DIR"];
    $failed = 1;
  }

  if (!$failed && !is_writeable($_FORM["ATTACHMENTS_DIR"])) {
    $text_tab_2a[] = "This directory is not writeable by the webserver: ".$_FORM["ATTACHMENTS_DIR"];
    $failed = 1;
  } else if (!$failed) {

    // Create directories under attachment dir and chmod them
    $dirs = $external_storage_directories; // something like array("task","client","project","invoice","comment","backups");
    foreach ($dirs as $dir) {
      $d = $_FORM["ATTACHMENTS_DIR"].$dir;
      @mkdir($d,0777);
      if (!is_dir($d)) {
        $text_tab_2a[] = "<b>Unable to create directory: ".$d."</b>";
        $failed = 1;
      } else if (!is_writeable($d)) {
        $text_tab_2a[] = "This directory is not writeable by the webserver: ".$d;
        $failed = 1;
      }
    }
  }

  if (!$failed) {
    // Create search indexes
    $search_item_indexes = array("client", "comment", "item", "project", "task", "timeSheet", "wiki");
    foreach ($search_item_indexes as $i) {
      $index = Zend_Search_Lucene::create($_FORM["ATTACHMENTS_DIR"].'search'.DIRECTORY_SEPARATOR.$i);
      $index->commit();
    }

    $query[] = sprintf("UPDATE config SET value = '%s' WHERE name = 'currency';",$_FORM["currency"]);
    $query[] = sprintf("UPDATE currencyType SET currencyTypeActive = true, currencyTypeSeq = 1 WHERE currencyTypeID = '%s';",$_FORM["currency"]);
    $query[] = sprintf("DELETE FROM exchangeRate;");
    $query[] = sprintf("INSERT INTO exchangeRate (exchangeRateCreatedDate,exchangeRateCreatedTime,fromCurrency,toCurrency,exchangeRate) VALUES ('%s','%s','%s','%s',%d);",date("Y-m-d"),date("Y-m-d H:i:s"),$_FORM["currency"],$_FORM["currency"],1);
    $query[] = sprintf("UPDATE config SET value = '%s' WHERE name = 'allocURL';",$_FORM["allocURL"]);
    $query[] = sprintf("UPDATE person SET password = '%s' WHERE personID = 1;",encrypt_password("alloc"));
    $query[] = sprintf("UPDATE config SET value = '%s' WHERE name = 'allocTimezone';",$timeZone);

    file_put_contents($_FORM["ATTACHMENTS_DIR"]."db_config.sql",implode("\n",$query));
  }



  if ($failed) {
    $TPL["img_install_result"] = IMG_CROSS;
    $TPL["msg_install_result"] = "The allocPSA installation has encountered errors.";
    $_GET["tab"] = 2;
  } else {
    define("INSTALL_SUCCESS",1);
    $TPL["img_install_result"] = IMG_TICK;
    $_GET["tab"] = 3;
  }

}


if ($_POST["test_db"] && is_object($db)) {
  // Test supplied credentials
  $link = @$db->connect();
  #@mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  if ($link) {
    $text_tab_3b[] = "Connected to MySQL database server as user '".$_FORM["ALLOC_DB_USER"]."'.";

    if ($db->select_db($_FORM["ALLOC_DB_NAME"])) {
      #@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link)
      $text_tab_3b[] = "Connected to database '".$_FORM["ALLOC_DB_NAME"]."'.";
    } else {
      $text_tab_3b[] = "<b>Unable to select database '".$_FORM["ALLOC_DB_NAME"]."'. Ensure it was created. (".mysql_error().").</b>";
      $failed = 1;
    }

    $query = "CREATE TABLE IF NOT EXISTS test ( hey int );";
    if ($db->query($query)) {
      $text_tab_3b[] = "Created table 'test'.";
    } else {
      $text_tab_3b[] = "<b>Unable to create table 'test'! (".mysql_error().").</b>";
      $failed = 1;
    }

    $query = "DROP TABLE test;";
    if ($db->query($query)) {
      $text_tab_3b[] = "Deleted table 'test'.";
    } else {
      $text_tab_3b[] = "<b>Unable to delete table 'test'! (".mysql_error().").</b>";
      $failed = 1;
    }

    $query = "SELECT * FROM config;";
    if ($db->query($query)) {
      $text_tab_3b[] = "Queried the config table.";
    } else {
      $text_tab_3b[] = "<b>Unable to query config table! Have you imported db_structure.sql as directed above? (".mysql_error().").</b>";
      $failed = 1;
    }

    $query = "SHOW TABLES;";
    if ($db->query($query)) {
      $num_tables = $db->num_rows();
    }

    $query = "SELECT * FROM config WHERE name='install_data';";
    $db->query($query);
    if ($row = $db->row()) {
      $install_data = unserialize($row["value"]);
    } else {
      $text_tab_3b[] = "<b>Can't get install_data from config table. Have you imported db_patches.sql as directed above?</b>";
      $failed = 1;
    }
      
    if ($install_data["num_tables"] == $num_tables) {
      $text_tab_3b[] = "Checked db_structure.sql (".$num_tables." out of ".$install_data["num_tables"]." tables).";
    } else {
      $text_tab_3b[] = "<b>Not all tables imported (".$num_tables." out of ".$install_data["num_tables"]."). Try re-importing db_structure.sql.</b>";
      $failed = 1;
    }

    $query = "SELECT * FROM announcement;";
    $db->query($query);
    if ($row = $db->row()) {
      $text_tab_3b[] = "Checked db_data.sql.";
    } else {
      $text_tab_3b[] = "<b>Missing the final INSERT from db_data.sql. Have you imported db_data.sql as directed above?</b>";
      $failed = 1;
    }

    $query = "SELECT * FROM patchLog;";
    $db->query($query);
    if ($row = $db->row()) {
      $text_tab_3b[] = "Checked db_patches.sql.";
    } else {
      $text_tab_3b[] = "<b>Missing the patchLog patches. Have you imported db_patches.sql as directed above?</b>";
      $failed = 1;
    }

    // one way to test the constraints is by trying to break them, so we temporarily kill error reporting.
    errors_off();
    
    $query = "DELETE FROM tfPerson WHERE tfID = 321321 AND personID = 374921;";
    @$db->query($query);
    $query = "INSERT INTO tfPerson (tfID,personID) VALUES (321321,374921);";
    if (@$db->query($query)) {
      $text_tab_3b[] = "<b>Missing constraints. Have you imported db_constraints.sql as directed above?</b>";
      $failed = 1;
    } else {
      $text_tab_3b[] = "Checked db_constraints.sql.";
    }
    $query = "DELETE FROM tfPerson WHERE tfID = 321321 AND personID = 374921;";
    @$db->query($query);
    errors_on();


    errors_off();
    $query = "SELECT neq(0,1) as result";
    if (@$db->query($query)) {
      $text_tab_3b[] = "Checked db_triggers.sql.";
    } else {
      $text_tab_3b[] = "<b>Can't use a UDF. Have you imported db_triggers.sql as the db admin user, as directed above?</b>";
      $failed = 1;
    }
    errors_on();

    $query = "SELECT password FROM person WHERE personID = 1;";
    $row = $db->qr($query);
    if ($row["password"]) {
      $text_tab_3b[] = "Checked db_config.sql.";
    } else {
      $text_tab_3b[] = "<b>No user password in person table. Have you imported db_config.sql as directed above?</b>";
      $failed = 1;
    }

  } else {
    $text_tab_3b[] = "Unable to connect to MySQL database server with supplied credentials! (".mysql_error().").";
    $failed = 1;
  }

  if ($failed) {
    $TPL["img_test_db_result"] = IMG_CROSS;
    $TPL["msg_test_db_result"] = "Database test unsuccessful!";
  } else {
    $TPL["img_test_db_result"] = IMG_TICK;
    $TPL["msg_test_db_result"] = "Database test successful.";
  }

  $_GET["tab"] = 3;
}

// Tab 2 Text
if ($_FORM["ALLOC_DB_NAME"] && $_FORM["ALLOC_DB_USER"]) {
  $text_tab_3[] = "&nbsp;";
  $text_tab_3[] = "DROP DATABASE IF EXISTS ".$_FORM["ALLOC_DB_NAME"].";";
  $text_tab_3[] = "CREATE DATABASE ".$_FORM["ALLOC_DB_NAME"].";";

  if ($_FORM["ALLOC_DB_USER"] != 'root') {
    // grant all on alloc14.* to 'heydiddle'@'localhost' IDENTIFIED BY 'hey';
    $text_tab_3[] = "";
    $text_tab_3[] = "GRANT ALL ON ".$_FORM["ALLOC_DB_NAME"].".* TO '".$_FORM["ALLOC_DB_USER"]."'@'".$_FORM["ALLOC_DB_HOST"]."' IDENTIFIED BY '".$_FORM["ALLOC_DB_PASS"]."';";
  }

  $text_tab_3[] = "FLUSH PRIVILEGES;";
  $text_tab_3[] = "";
  $text_tab_3[] = "USE ".$_FORM["ALLOC_DB_NAME"].";";
  $text_tab_3[] = "";
  $text_tab_3[] = "SOURCE ".dirname(__FILE__).DIRECTORY_SEPARATOR."db_structure.sql;";
  $text_tab_3[] = "SOURCE ".dirname(__FILE__).DIRECTORY_SEPARATOR."db_data.sql;";
  $text_tab_3[] = "SOURCE ".dirname(__FILE__).DIRECTORY_SEPARATOR."db_patches.sql;";
  $text_tab_3[] = "SOURCE ".dirname(__FILE__).DIRECTORY_SEPARATOR."db_constraints.sql;";
  $text_tab_3[] = "SOURCE ".dirname(__FILE__).DIRECTORY_SEPARATOR."db_triggers.sql;";
  $text_tab_3[] = "";
  $text_tab_3[] = "SOURCE ".$_FORM["ATTACHMENTS_DIR"]."db_config.sql;";
  $text_tab_3[] = "&nbsp;";
}


// Tab 1 Text
foreach ($config_vars as $name => $arr) {
  $text_tab_1[] = "<tr><td>".$arr["info"]."</td><td><input type='text' name='".$name."' size='30' value='".$_FORM[$name]."'></td></tr>";
}


is_array($text_tab_1) and $TPL["text_tab_1"] = implode("\n",$text_tab_1);
is_array($text_tab_2a) and $TPL["text_tab_2a"] = implode("<br>",$text_tab_2a);
is_array($text_tab_2b) and $TPL["text_tab_2b"] = implode("<br>",$text_tab_2b);
is_array($text_tab_3) and $TPL["text_tab_3"] = implode("\n",$text_tab_3);
is_array($text_tab_3b) and $TPL["text_tab_3b"] = implode("<br>",$text_tab_3b);
is_array($text_tab_4) and $TPL["text_tab_4"] = implode("<br>",$text_tab_4);


$tab = $_GET["tab"] or $tab = $_POST["tab"] or $tab = $_FORM["tab"];
$tab == 1 || !$tab and $TPL["tab1"] = " active";
$tab == 2 and $TPL["tab2"] = " active";
$tab == 3 and $TPL["tab3"] = " active";
$tab == 4 and $TPL["tab4"] = " active";


include_template("templates/install.tpl");

?>
