<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

define("IN_INSTALL_RIGHT_NOW",1);
require_once("../alloc.php");

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

$config_vars = array("ALLOC_DB_NAME"   => array("default"=>"alloc",              "info"=>"Enter a name for the new allocPSA MySQL database")
                    ,"ALLOC_DB_USER"   => array("default"=>"alloc",              "info"=>"Enter the name of the database user that will access the database")
                    ,"ALLOC_DB_PASS"   => array("default"=>"changeme",           "info"=>"Enter that users database password")
                    ,"ALLOC_DB_HOST"   => array("default"=>"localhost",          "info"=>"Enter the name of the host that the database resides on")
                    ,"ATTACHMENTS_DIR" => array("default"=>"/var/local/alloc/",  "info"=>"Enter the full path to a directory that can be used for file upload storage, 
                                                                                          (The path must be outside the web document root)")
                    ,"allocURL"        => array("default"=>$default_allocURL,    "info"=>"Enter the base URL that people will use to access allocPSA, eg: http://example.com/alloc/")
                    );


foreach($config_vars as $name => $arr) {
  $val = $_POST[$name] or $val = $_GET[$name];
  $val == "" && !isset($_GET[$name]) && !isset($_POST[$name]) and $val = $arr["default"];
  $name == "ATTACHMENTS_DIR" && $val && !preg_match("/\/$/",$val) and $val.= "/";
  $name == "allocURL" && $val && !preg_match("/\/$/",$val) and $val.= "/";
  $_FORM[$name] = $val;
  $get[] = $name."=".urlencode($val);
  $hidden[] = "<input type='hidden' name='".$name."' value='".$val."'>";
  $TPL[$name] = $val;
}
$TPL["get"] = "&".implode("&",$get);
$TPL["hidden"] = implode("\n",$hidden);
 

// Path to alloc_config.php
define("ALLOC_CONFIG_PATH", realpath(dirname(__FILE__)."/..")."/alloc_config.php");

if ($_POST["refresh_tab_1"]) {
  header("Location: ".$TPL["url_alloc_installation"]."?1=1".$TPL["get"]);
  exit;
}


// Finish installation
if ($_POST["submit_stage_4"]) {

  // Create directories under attachment dir and chmod them
  $dirs = array("task","client","project");
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

  // Create alloc_config.php
  if (file_exists(ALLOC_CONFIG_PATH) && is_writeable(ALLOC_CONFIG_PATH) && filesize(ALLOC_CONFIG_PATH) == 0) {
    $str[] = "<?php";
    foreach ($config_vars as $name => $arr) {
      $name != "allocURL" and $str[] = "define(\"".$name."\",\"".$_FORM[$name]."\");";
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

  if ($failed) {
    file_exists(ALLOC_CONFIG_PATH) && is_writeable(ALLOC_CONFIG_PATH) && unlink(ALLOC_CONFIG_PATH);
    $TPL["img_install_result"] = IMG_CROSS;
    $TPL["msg_install_result"] = "The allocPSA installation has not completed successfully.";
  } else {
    define("INSTALL_SUCCESS",1);
    $TPL["img_install_result"] = IMG_TICK;
    $TPL["msg_install_result"] = "The allocPSA installation has completed successfully. <a href=\"".$TPL["url_alloc_login"]."\">Click here</a> and login with the username and password of 'alloc'.";
  }

  $_GET["tab"] = 4;
}


if ($_POST["test_db_credentials"]) {
  // Test supplied credentials

  $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  if ($link) {
    $text_tab_2b[] = "Successfully connected to MySQL database server as user '".$_FORM["ALLOC_DB_USER"]."'.";

    if (@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link)) {
      $text_tab_2b[] = "Successfully connected to database '".$_FORM["ALLOC_DB_NAME"]."'.";
    } else {
      $text_tab_2b[] = "Unable to select database '".$_FORM["ALLOC_DB_NAME"]."'. Ensure it was created. (".mysql_error().").";
      $failed = 1;
    }

    $query = "CREATE TABLE test ( hey int );";
    if (@mysql_query($query,$link)) {
      $text_tab_2b[] = "Successfully created table 'test'.";
    } else {
      $text_tab_2b[] = "Unable to create table 'test'! (".mysql_error().").";
      $failed = 1;
    }

    $query = "DROP TABLE test;";
    if (@mysql_query($query,$link)) {
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


if ($_POST["install_db"]) {
  unset($failed);
  $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  @mysql_select_db($_FORM["ALLOC_DB_NAME"], $link);

  $files = array("../util/sql/db_structure.sql","../util/sql/db_data.sql");

  foreach ($files as $file) {
    $mqr = @get_magic_quotes_runtime();
    @set_magic_quotes_runtime(0);
    $query = fread(fopen($file, 'r'), filesize($file));
    @set_magic_quotes_runtime($mqr);

    $query = ereg_replace("\n--[^\n]*\n", "\n", $query);
    $pieces = explode(";\n",$query);

    for ($i=0; $i<count($pieces); $i++) {
      $pieces[$i] = trim($pieces[$i]);
      if(!empty($pieces[$i]) && $pieces[$i] != "-") {
        if (!@mysql_query($pieces[$i],$link)) {
          $errors[] = "Error! (".mysql_error().").";
        }
      } 
    }   
  }    
  
  // Insert config data
  $query = "INSERT INTO config (name, value) VALUES ('allocURL','".$_FORM["allocURL"]."')";
  if (!@mysql_query($query,$link)) {
    $errors[] = "Error! (".mysql_error().").";
  }

  // Insert patch data
  $files = get_patch_file_list();
  foreach ($files as $f) {
    $query = sprintf("INSERT INTO patchLog (patchName, patchDesc, patchDate) VALUES ('%s','','%s')",db_esc($f), date("Y-m-d H:i:s"));
    if (!@mysql_query($query)) {
      $errors[] = "Error! (".mysql_error().").";
    }
  }


  if (!is_array($errors) && !count($errors)) {
    $text_tab_3b[] = "Database import successful!";
    $res = mysql_query("SELECT username FROM person",$link);
    $r = mysql_fetch_assoc($res);
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
$text_tab_2a[] = "DROP DATABASE IF EXISTS ".$_FORM["ALLOC_DB_NAME"].";";
$text_tab_2a[] = "";
$text_tab_2a[] = "CREATE DATABASE ".$_FORM["ALLOC_DB_NAME"].";";
$text_tab_2a[] = "";
$text_tab_2a[] = "USE mysql;";
$text_tab_2a[] = "";
$_FORM["ALLOC_DB_USER"] != 'root' and $text_tab_2a[] = "DELETE FROM user WHERE User = '".$_FORM["ALLOC_DB_USER"]."';";
$_FORM["ALLOC_DB_USER"] != 'root' and $text_tab_2a[] = "";
$_FORM["ALLOC_DB_USER"] != 'root' and $text_tab_2a[] = "DELETE FROM db WHERE User = '".$_FORM["ALLOC_DB_USER"]."';";
$_FORM["ALLOC_DB_USER"] != 'root' and $text_tab_2a[] = "";
$text_tab_2a[] = "INSERT INTO user (Host, User, Password) values ('".$_FORM["ALLOC_DB_HOST"]."','".$_FORM["ALLOC_DB_USER"]."',PASSWORD('".$_FORM["ALLOC_DB_PASS"]."'));";
$text_tab_2a[] = "";
$text_tab_2a[] = "INSERT INTO db \n(Host,Db,User,Select_priv,Insert_priv,Update_priv,Delete_priv,\nCreate_priv,Drop_priv,References_priv,Index_priv,Alter_priv) \nVALUES ('".$_FORM["ALLOC_DB_HOST"]."','".$_FORM["ALLOC_DB_NAME"]."','".$_FORM["ALLOC_DB_USER"]."','y','y','y','y','y','y','y','y','y');";
$text_tab_2a[] = "";
$text_tab_2a[] = "FLUSH PRIVILEGES;";



// Tab 1 Text
foreach ($config_vars as $name => $arr) {
  $text_tab_1[] = "<tr><td>".$arr["info"]."</td><td><input type='text' name='".$name."' size='30' value='".$_FORM[$name]."'></td></tr>";
}


is_array($text_tab_1) and $TPL["text_tab_1"] = implode("\n",$text_tab_1);
is_array($text_tab_2a) and $TPL["text_tab_2a"] = implode("<br/>",$text_tab_2a);
is_array($text_tab_2b) and $TPL["text_tab_2b"] = implode("<br/>",$text_tab_2b);
is_array($text_tab_3b) and $TPL["text_tab_3b"] = implode("<br/>",$text_tab_3b);
is_array($text_tab_4) and $TPL["text_tab_4"] = implode("<br/>",$text_tab_4);


$tab = $_GET["tab"] or $tab = $_POST["tab"] or $tab = $_FORM["tab"];
$tab == 1 || !$tab and $TPL["tab1"] = " active";
$tab == 2 and $TPL["tab2"] = " active";
$tab == 3 and $TPL["tab3"] = " active";
$tab == 4 and $TPL["tab4"] = " active";


include_template("templates/install.tpl");

?>
