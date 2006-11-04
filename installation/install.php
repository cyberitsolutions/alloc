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

define("IMG_TICK","<img src=\"".$TPL["url_alloc_images"]."tick.gif\">");
define("IMG_CROSS","<img src=\"".$TPL["url_alloc_images"]."cross.gif\">");

function check_optional_step_1() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 1 || !$tab;
}
function check_optional_step_2() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 2;
}
function check_optional_step_2b() {
  return $_POST["test_db_credentials"];
}
function check_optional_step_3() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 3;
}
function check_optional_step_4() {
  $tab = $_GET["tab"] or $tab = $_POST["tab"];
  return $tab == 4;
}
function check_optional_step_4b() {
  return $_POST["submit_stage_4"];
}



$config_vars = array("ALLOC_DB_NAME"   => array("default"=>"alloc","info"=>"Enter a name for the new allocPSA MySQL database")
                    ,"ALLOC_DB_USER"   => array("default"=>"",     "info"=>"Enter the name of the database user that will access the database")
                    ,"ALLOC_DB_PASS"   => array("default"=>"",     "info"=>"Enter that users database password")
                    ,"ALLOC_DB_HOST"   => array("default"=>"",     "info"=>"Enter the name of the host that the database resides on")
                    ,"ATTACHMENTS_DIR" => array("default"=>"",     "info"=>"Enter the full path to a directory that can be used for file upload storage, 
                                                                            (The path must be outside the web document root)")
                    );


foreach($config_vars as $name => $arr) {
  $val = $_POST[$name] or $val = $_GET[$name];
  $name == "ATTACHMENTS_DIR" && $val && !preg_match("/\/$/",$val) and $val.= "/";
  $name == "ALLOC_DB_HOST" && $val == "" and $val = "localhost";
  $_FORM[$name] = $val;
  $get[] = $name."=".urlencode($val);
  $hidden[] = "<input type='hidden' name='".$name."' value='".$val."'>";
  $TPL[$name] = $val;
}
$TPL["get"] = "&".implode("&",$get);
$TPL["hidden"] = implode("\n",$hidden);
 

// Path to alloc_config.php
$TPL["alloc_config_path"] = realpath(dirname(__FILE__)."/..")."/alloc_config.php";



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
  if (file_exists($TPL["alloc_config_path"]) && is_writeable($TPL["alloc_config_path"]) && filesize($TPL["alloc_config_path"]) == 0) {
    $str[] = "<?php";
    foreach ($config_vars as $name => $arr) {
      if (!$_FORM[$name]) {
        $var_fail = true;
      }
      $str[] = "define(\"".$name."\",\"".$_FORM[$name]."\");";
    }
    $str[] = "?>";
    $str = implode("\n",$str);
    if (!$var_fail) {
      $fh = fopen($TPL["alloc_config_path"],"w+");
      fputs($fh,$str);
      fclose($fh);
    } else {
      $text_tab_4[] = "Missing variables, unable to create alloc_config.php.";
      $failed = 1;
    }

    // Clear PHP file cache
    clearstatcache();

    if (file_exists($TPL["alloc_config_path"]) && filesize($TPL["alloc_config_path"]) > 0) {
      $text_tab_4[] = "Created ".$TPL["alloc_config_path"];
    } else {
      $text_tab_4[] = "Unable to create(1): ".$TPL["alloc_config_path"];
      $failed = 1;
    }

  } else {
    $text_tab_4[] = "Unable to create(2): ".$TPL["alloc_config_path"];
    $failed = 1;
  }

  if ($failed) {
    file_exists($TPL["alloc_config_path"]) && is_writeable($TPL["alloc_config_path"]) && unlink($TPL["alloc_config_path"]);
    $TPL["img_install_result"] = IMG_CROSS;
    $TPL["msg_install_result"] = "The install has not completed successfully.";
  } else {
    $TPL["img_install_result"] = IMG_TICK;
    $TPL["msg_install_result"] = "The install has completed successfully, <a href=\"".$TPL["url_alloc_login"]."\">click here</a> and you can login with the username and password of 'alloc'.";
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
  if (!is_array($errors) && !count($errors)) {
    $text_tab_3[] = "Database import successful!";
    $res = mysql_query("SELECT username FROM person",$link);
    $r = mysql_fetch_assoc($res);
    if (is_array($r)) {
      $text_tab_3[] = "Admin user '".$r["username"]."' imported successfully!";
    } else {
      $text_tab_3[] = "Problem importing data. Recommended to drop and create database and try again.";
    }
  } else {
    $text_tab_3 = $errors;
  }

  $_GET["tab"] = 3;

} else if ($_POST["patch_db"]) {
  $_GET["tab"] = 3;

} else if ($_POST["submit_stage_3"]) {
  $_GET["tab"] = 4;
}



if ($_GET["tab"] == 4) {
  // Tab 4 Text
  // test for db connectivity
  #$results["DB_CONNECTIVITY"] = 
  $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
  if (!$link || !$_FORM["ALLOC_DB_USER"]) {
    $TPL["img_result_DB_CONNECTIVITY"] = IMG_CROSS;
    $TPL["remedy_DB_CONNECTIVITY"] = "Unable to connect to the MySQL server. Check the credentials in the 'Input' step.";
  } else {
    $TPL["img_result_DB_CONNECTIVITY"] = IMG_TICK;
    $TPL["remedy_DB_CONNECTIVITY"] = "Ok.";
  }

  if (@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link)) {
    $TPL["img_result_DB_SELECT"] = IMG_TICK;
    $TPL["remedy_DB_SELECT"] = "Ok.";
  } else {
    $TPL["img_result_DB_SELECT"] = IMG_CROSS;
    $TPL["remedy_DB_SELECT"] = "The database '".$_FORM["ALLOC_DB_NAME"]."' cannot be selected. It may not exist. Please repeat the 'DB Setup' step.";
  }

  $qid = @mysql_query("SHOW TABLES",$link);
  if (is_resource($qid)) {
    while ($row = mysql_fetch_array($qid)) {
      $count++;
    }
  }

  $TPL["num_tables"] = $count;

  if ($count > 2) {
    $TPL["img_result_DB_TABLES"] = IMG_TICK;
    $TPL["remedy_DB_TABLES"] = "Ok.";
  } else {
    $TPL["img_result_DB_TABLES"] = IMG_CROSS;
    $TPL["remedy_DB_TABLES"] = "The database tables don't appear to have imported correctly. Please repeat the 'DB Install' step.";
  }
  

  // Test attachment directory
  if ($_FORM["ATTACHMENTS_DIR"] == "" || !is_dir($_FORM["ATTACHMENTS_DIR"]) || !is_writeable($_FORM["ATTACHMENTS_DIR"])) {
    $TPL["img_result_ATTACHMENTS_DIR"] = IMG_CROSS;
    $TPL["remedy_ATTACHMENTS_DIR"] = "The directory specified for file uploads is either not defined or not writeable by the webserver process. Run:";
    $TPL["remedy_ATTACHMENTS_DIR"].= "<br/>mkdir ".$_FORM["ATTACHMENTS_DIR"];
    $TPL["remedy_ATTACHMENTS_DIR"].= "<br/>chmod a+w ".$_FORM["ATTACHMENTS_DIR"];
  } else {
    $TPL["img_result_ATTACHMENTS_DIR"] = IMG_TICK;
    $TPL["remedy_ATTACHMENTS_DIR"] = "Ok.";
  }

  // Test alloc_config.php is writeable

  if (!file_exists($TPL["alloc_config_path"]) || !is_writeable($TPL["alloc_config_path"])) {
    $TPL["img_result_ALLOC_CONFIG"] = IMG_CROSS;
    $TPL["remedy_ALLOC_CONFIG"] = "Please create an empty, webserver-writeable file: ";
    $TPL["remedy_ALLOC_CONFIG"].= "<br/><nobr>touch ".$TPL["alloc_config_path"]."</nobr>";
    $TPL["remedy_ALLOC_CONFIG"].= "<br/><nobr>chmod 600 ".$TPL["alloc_config_path"]."</nobr>";
    $TPL["remedy_ALLOC_CONFIG"].= "<br/><nobr>chown apache ".$processUser['name']." ".$TPL["alloc_config_path"]."</nobr>";

  } else {
    $TPL["img_result_ALLOC_CONFIG"] = IMG_TICK;
    $TPL["remedy_ALLOC_CONFIG"] = "Ok.";
  } 
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
$text_tab_2a[] = "INSERT INTO db \n(Host,Db,User,Select_priv,Insert_priv,Update_priv,Delete_priv,Create_priv,Drop_priv) \nVALUES ('".$_FORM["ALLOC_DB_HOST"]."','".$_FORM["ALLOC_DB_NAME"]."','".$_FORM["ALLOC_DB_USER"]."','y','y','y','y','y','y');";
$text_tab_2a[] = "";
$text_tab_2a[] = "FLUSH PRIVILEGES;";



// Tab 1 Text
foreach ($config_vars as $name => $arr) {
  $text_tab_1[] = "<tr><td>".$arr["info"]."</td><td><input type='text' name='".$name."' size='30' value='".$_FORM[$name]."'></td></tr>";
}


is_array($text_tab_1) and $TPL["text_tab_1"] = implode("\n",$text_tab_1);
is_array($text_tab_2a) and $TPL["text_tab_2a"] = implode("<br/>",$text_tab_2a);
is_array($text_tab_2b) and $TPL["text_tab_2b"] = implode("<br/>",$text_tab_2b);
is_array($text_tab_3) and $TPL["text_tab_3"] = implode("<br/>",$text_tab_3);
is_array($text_tab_4) and $TPL["text_tab_4"] = implode("<br/>",$text_tab_4);


$tab = $_GET["tab"] or $tab = $_POST["tab"] or $tab = $_FORM["tab"];
$tab == 1 || !$tab and $TPL["tab1"] = " active";
$tab == 2 and $TPL["tab2"] = " active";
$tab == 3 and $TPL["tab3"] = " active";
$tab == 4 and $TPL["tab4"] = " active";


include_template("templates/install.tpl");

?>
