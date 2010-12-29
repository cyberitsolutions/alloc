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

define("IN_INSTALL_RIGHT_NOW",1);
require_once("../alloc.php");

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
  return $tab == 2;
}
function show_tab_2b() {
  return $_POST["test_db_credentials"];
}
function show_tab_3() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 3;
}
function show_tab_3b() {
  return $_POST["install_db"];
}
function show_tab_4() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 4;
}
function show_tab_4b() {
  return $_POST["submit_stage_4"];
}
function show_tab_4c() {
  return defined("INSTALL_SUCCESS");
}



$default_allocURL = "http://".$_SERVER["SERVER_NAME"].SCRIPT_PATH;

$config_vars = array("ALLOC_DB_NAME"     => array("default"=>"alloc",              "info"=>"Enter a name for the new allocPSA MySQL database")
                    ,"ALLOC_DB_USER"     => array("default"=>"alloc",              "info"=>"Enter the name of the database user that will access the database")
                    ,"ALLOC_DB_PASS"     => array("default"=>"changeme",           "info"=>"Enter that users database password")
                    ,"ALLOC_DB_HOST"     => array("default"=>"localhost",          "info"=>"Enter the name of the host that the database resides on")
                    ,"ATTACHMENTS_DIR"   => array("default"=>"/var/local/alloc/",  "info"=>"Enter the full path to a directory that can be used for file upload storage, 
                                                                                          (The path must be outside the web document root)")
                    ,"allocURL"          => array("default"=>$default_allocURL,    "info"=>"Enter the base URL that people will use to access allocPSA, eg: http://example.com/alloc/")
                    ,"currency"          => array("default"=>"USD",                "info"=>"Enter the default currency code that will be used by this instance of allocPSA.")
                    );


foreach($config_vars as $name => $arr) {
  $val = $_POST[$name] or $val = $_GET[$name];
  $val == "" && !isset($_GET[$name]) && !isset($_POST[$name]) and $val = $arr["default"];
  $name == "ATTACHMENTS_DIR" && $val and $val = rtrim($val,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  $name == "allocURL" && $val and $val = rtrim($val,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  $name == "currency" and $val = trim(strtoupper($val));
  $_FORM[$name] = $val;
  $get[] = $name."=".urlencode($val);
  $hidden[] = "<input type='hidden' name='".$name."' value='".$val."'>";
  $TPL[$name] = $val;
}
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


// Finish installation
if ($_POST["submit_stage_4"]) {

  // Create directories under attachment dir and chmod them
  $dirs = $external_storage_directories; // something like array("task","client","project","invoice","comment","backups");
  foreach ($dirs as $dir) {
    $d = $_FORM["ATTACHMENTS_DIR"].$dir;
    if (is_dir($d)) {
      $text_tab_4[] = "Already exists: ".$d;
    } else {
      @mkdir($d,0777);
      if (is_dir($d)) {
        $text_tab_4[] = "Created: ".$d;
      } else {
        $text_tab_4[] = "Unable to create directory: ".$d;
        $failed = 1;
      }
    }

    if (!is_writeable($d)) {
      $text_tab_4[] = "This directory is not writeable by the webserver: ".$d;
      $failed = 1;
    }
  }

  // Create search indexes
  $search_item_indexes = array("client", "comment", "item", "project", "task", "timeSheet", "wiki");
  foreach ($search_item_indexes as $i) {
    $index = Zend_Search_Lucene::create($_FORM["ATTACHMENTS_DIR"].'search'.DIRECTORY_SEPARATOR.$i);
    $index->commit();
  }

  // Create alloc_config.php
  if (file_exists(ALLOC_CONFIG_PATH) && is_writeable(ALLOC_CONFIG_PATH) && filesize(ALLOC_CONFIG_PATH) <= 5) {
    $str[] = "<?php";
    foreach ($config_vars as $name => $arr) {
      $name != "allocURL" && $name != "currency" and $str[] = "define(\"".$name."\",\"".$_FORM[$name]."\");";
    }
    $str[] = "?>";
    $str = implode("\n",$str);
    $fh = fopen(ALLOC_CONFIG_PATH,"w+");
    fputs($fh,$str);
    fclose($fh);

    // Clear PHP file cache
    clearstatcache();

    if (file_exists(ALLOC_CONFIG_PATH) && filesize(ALLOC_CONFIG_PATH) > 0) {
      $text_tab_4[] = "Created ".ALLOC_CONFIG_PATH;
    } else {
      $text_tab_4[] = "Unable to create(1): ".ALLOC_CONFIG_PATH;
      $failed = 1;
    }

  } else {
    $text_tab_4[] = "Unable to create(2): ".ALLOC_CONFIG_PATH;
    $failed = 1;
  }

  // Insert config data
  $query = "UPDATE config SET value = '".$_FORM["currency"]."' WHERE name = 'currency'";
  if (!$db->query($query)) {
    $errors[] = "(1)Error! (".mysql_error().").";
    $failed = 1;
  }

  $query = "UPDATE currencyType SET currencyTypeActive = true, currencyTypeSeq = 1 WHERE currencyTypeID = '".$_FORM["currency"]."'";
  if (!$db->query($query)) {
    $errors[] = "(2)Error! (".mysql_error().").";
    $failed = 1;
  }

  $query = sprintf("INSERT INTO exchangeRate (exchangeRateCreatedDate,exchangeRateCreatedTime,fromCurrency,toCurrency,exchangeRate) VALUES ('%s','%s','%s','%s',%d)",date("Y-m-d"),date("Y-m-d H:i:s"),$_FORM["currency"],$_FORM["currency"],1);
  if (!$db->query($query)) {
    $errors[] = "(2.5)Error! (".mysql_error().").";
    $failed = 1;
  }


  if ($failed) {
    $TPL["img_install_result"] = IMG_CROSS;
    $TPL["msg_install_result"] = "The allocPSA installation has not completed successfully.";
  } else {
    define("INSTALL_SUCCESS",1);
    $TPL["img_install_result"] = IMG_TICK;
    $TPL["url_alloc_login"][strlen($TPL["url_alloc_login"])-1] != "?" and $qm = "?";
    $TPL["msg_install_result"] = "The allocPSA installation has completed successfully. <a href=\"".$TPL["url_alloc_login"].$qm."message_help=".urlencode("Default login username/password: <b>alloc</b><br>You should change both the username and password of this administrator account ASAP.")."\">Click here</a> and login with the username and password of 'alloc'.";
  }

  $_GET["tab"] = 4;
}


if ($_POST["test_db_credentials"] && is_object($db)) {
  // Test supplied credentials


  $link = @$db->connect();
  #@mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  if ($link) {
    $text_tab_2b[] = "Successfully connected to MySQL database server as user '".$_FORM["ALLOC_DB_USER"]."'.";

    if ($db->select_db($_FORM["ALLOC_DB_NAME"])) {
      #@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link)
      $text_tab_2b[] = "Successfully connected to database '".$_FORM["ALLOC_DB_NAME"]."'.";
    } else {
      $text_tab_2b[] = "Unable to select database '".$_FORM["ALLOC_DB_NAME"]."'. Ensure it was created. (".mysql_error().").";
      $failed = 1;
    }

    $query = "CREATE TABLE test ( hey int );";
    if ($db->query($query)) {
      $text_tab_2b[] = "Successfully created table 'test'.";
    } else {
      $text_tab_2b[] = "Unable to create table 'test'! (".mysql_error().").";
      $failed = 1;
    }

    $query = "DROP TABLE test;";
    if ($db->query($query)) {
      $text_tab_2b[] = "Successfully deleted table 'test'.";
    } else {
      $text_tab_2b[] = "Unable to delete table 'test'! (".mysql_error().").";
      $failed = 1;
    }

  } else {
    $text_tab_2b[] = "Unable to connect to MySQL database server with supplied credentials! (".mysql_error().").";
    $failed = 1;
  }

  if ($failed) {
    $TPL["img_test_db_result"] = IMG_CROSS;
    $TPL["msg_test_db_result"] = "Database connection test unsuccessful!";
  } else {
    $TPL["img_test_db_result"] = IMG_TICK;
    $TPL["msg_test_db_result"] = "Database connection test successful.";
  }

  $_GET["tab"] = 2;

} else if ($_POST["submit_stage_2"]) {
  $_GET["tab"] = 3;

}


if ($_POST["install_db"] && is_object($db)) {
  unset($failed);
  $link = $db->connect();
  $db->select_db($_FORM["ALLOC_DB_NAME"]);
  #$link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  #@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link);
  $files = array();
  $files[] = "../installation/db_structure.sql";
  $files[] = "../installation/db_data.sql";
  $files[] = "../installation/db_constraints.sql";

  foreach ($files as $file) {
    list($sql,$comments) = parse_sql_file($file);

    foreach($sql as $q) {
      if (!$db->query($q)) {
        $errors[] = "(3)Error! (".mysql_error().").";
      }
    }   
  }    
  

  // Insert config data
  $query = "INSERT INTO config (name, value, type) VALUES ('allocURL','".$_FORM["allocURL"]."','text')";
  if (!$db->query($query)) {
    $errors[] = "(4)Error! (".mysql_error().").";
  }


$rand = sprintf("%02d",rand(0,59));
$rand2 = sprintf("%d",rand(1,5));

  $body = <<<EOD
If you're new to allocPSA, just follow the tabs across left to right at the
top of the page, ie: Clients have Projects > Projects have Tasks > Time
Sheet are billed against Tasks > and the Finance section will help you out
when there are Time Sheets.

Here are the cron jobs from the installation in case you hadn't installed
them yet. You will need to install at least the first one to enable the very
useful automated reminders functionality. 

# Check every day in the early hours for the exchange rates
{$rand} {$rand2} * * * wget -q -O /dev/null {$_FORM["allocURL"]}finance/updateExchangeRates.php

# Check every 10 minutes for any allocPSA Reminders to send
*/10 * * * * wget -q -O /dev/null {$_FORM["allocURL"]}reminder/sendReminders.php

# Check every 5 minutes for any new emails to import into allocPSA
*/5 * * * * wget -q -O /dev/null {$_FORM["allocURL"]}email/receiveEmail.php

# Send allocPSA Daily Digest emails once a day at 4:35am
35 4 * * * wget -q -O /dev/null {$_FORM["allocURL"]}person/sendEmail.php

# Check for allocPSA Repeating Expenses once a day at 4:40am
40 4 * * * wget -q -O /dev/null {$_FORM["allocURL"]}finance/checkRepeat.php

Please feel free to contact us at Cybersource <info@cyber.com.au> or just use
the forums at http://sourceforge.net/projects/allocpsa/ if you have any questions.

To remove this announcement click on the Tools tab and then click the
Announcements link.
EOD;

  // Insert new person
  $query = sprintf("INSERT INTO person (username,password,personActive,perms) VALUES ('alloc','%s',1,'god,admin,manage,employee')",encrypt_password("alloc"));
  if (!$db->query($query)) {
    $errors[] = "(5)Error! (".mysql_error().").";
  }

  // Insert new announcement
  $query = "INSERT INTO announcement (heading, body, personID,displayFromDate,displayToDate) VALUES (\"Getting Started in allocPSA\",\"".db_esc($body)."\",1,'2000-01-01','2030-01-01')";
  if (!$db->query($query)) {
    $errors[] = "(6)Error! (".mysql_error().").";
  }

  // Insert patch data
  $files = get_patch_file_list();
  foreach ($files as $f) {
    $query = sprintf("INSERT INTO patchLog (patchName, patchDesc, patchDate) VALUES ('%s','','%s')",db_esc($f), date("Y-m-d H:i:s"));
    if (!$db->query($query)) {
      $errors[] = "(7)Error! (".mysql_error().").";
    }
  }

  // Set up the default timezone
  $query = sprintf("INSERT INTO config (name, value, type) VALUES ('allocTimezone', '%s', 'text')", db_esc($timeZone));
  if (!$db->query($query)) {
    $errors[] = "(8)Error! (".mysql_error().").";
  }

  if (!is_array($errors) && !count($errors)) {
    $text_tab_3b[] = "Database import successful!";
    $res = $db->query("SELECT username FROM person");
    $r = $db->row();
    if (is_array($r)) {
      $text_tab_3b[] = "Admin user '".$r["username"]."' imported successfully!";
    } else {
      $text_tab_3b[] = "Problem importing data. Recommended to manually drop database and try again.";
      $failed = 1;
    }
  } else {
    $text_tab_3b = $errors;
    $failed = 1;
  }

  if ($failed) {
    $TPL["img_install_db_result"] = IMG_CROSS;
    $TPL["msg_install_db_result"] = "Database installation unsuccessful!";
  } else {
    $TPL["img_install_db_result"] = IMG_TICK;
    $TPL["msg_install_db_result"] = "Database installation successful.";
  }
  $_GET["tab"] = 3;

} else if ($_POST["patch_db"]) {
  $_GET["tab"] = 3;

} else if ($_POST["submit_stage_3"]) {
  $_GET["tab"] = 4;
}


// Tab 2 Text
if ($_FORM["ALLOC_DB_NAME"] && $_FORM["ALLOC_DB_USER"]) {
  $text_tab_2a[] = "DROP DATABASE IF EXISTS ".$_FORM["ALLOC_DB_NAME"].";";
  $text_tab_2a[] = "";
  $text_tab_2a[] = "CREATE DATABASE ".$_FORM["ALLOC_DB_NAME"].";";

  if ($_FORM["ALLOC_DB_USER"] != 'root') {
    // grant all on alloc14.* to 'heydiddle'@'localhost' IDENTIFIED BY 'hey';
    $text_tab_2a[] = "";
    $text_tab_2a[] = "GRANT ALL ON ".$_FORM["ALLOC_DB_NAME"].".* TO '".$_FORM["ALLOC_DB_USER"]."'@'".$_FORM["ALLOC_DB_HOST"]."' IDENTIFIED BY '".$_FORM["ALLOC_DB_PASS"]."';";
  }

  $text_tab_2a[] = "";
  $text_tab_2a[] = "FLUSH PRIVILEGES;";

}


// Tab 1 Text
foreach ($config_vars as $name => $arr) {
  $text_tab_1[] = "<tr><td>".$arr["info"]."</td><td><input type='text' name='".$name."' size='30' value='".$_FORM[$name]."'></td></tr>";
}


is_array($text_tab_1) and $TPL["text_tab_1"] = implode("\n",$text_tab_1);
is_array($text_tab_2a) and $TPL["text_tab_2a"] = implode("<br>",$text_tab_2a);
is_array($text_tab_2b) and $TPL["text_tab_2b"] = implode("<br>",$text_tab_2b);
is_array($text_tab_3b) and $TPL["text_tab_3b"] = implode("<br>",$text_tab_3b);
is_array($text_tab_4) and $TPL["text_tab_4"] = implode("<br>",$text_tab_4);


$tab = $_GET["tab"] or $tab = $_POST["tab"] or $tab = $_FORM["tab"];
$tab == 1 || !$tab and $TPL["tab1"] = " active";
$tab == 2 and $TPL["tab2"] = " active";
$tab == 3 and $TPL["tab3"] = " active";
$tab == 4 and $TPL["tab4"] = " active";


include_template("templates/install.tpl");

?>
