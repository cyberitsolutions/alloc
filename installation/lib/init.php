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


// Path to alloc_config.php
$ALLOC_CONFIG_PATH = $_POST["ALLOC_CONFIG_PATH"] or $ALLOC_CONFIG_PATH = $_GET["ALLOC_CONFIG_PATH"];
if ($ALLOC_CONFIG_PATH) {
  define("ALLOC_CONFIG_PATH",$ALLOC_CONFIG_PATH);
} else {
  define("ALLOC_CONFIG_PATH", realpath(dirname(__FILE__)."/../..")."/alloc_config.php");
} 
unset($ALLOC_CONFIG_PATH);



class installation_module extends module {
  var $module = "installation";
}

function get_patch_file_list() {
  $dir = ALLOC_MOD_DIR."patches/";
  $files = array();
  if (is_dir($dir)) {
    $dh = opendir($dir);
    if ($dh) {
      while (($file = readdir($dh)) !== false) {
        if (filetype($dir.$file) == "file" && substr($file,0,1) != ".") {
          $files[] = $file;
        }
      }
      closedir($dh);
    }

    #// Sort files in natural counting order file8 fil9 fil10
    #natsort($files);
    // filenames no longer require natsort
    sort($files);
    // Order the indexes too
    $files = array_values($files);
  }
  return $files;
}

function get_applied_patches() {
  $rows = array();
  $db = new db_alloc();
  $db->query("SELECT patchName FROM patchLog ORDER BY patchDate DESC,patchName DESC");
  while ($row = $db->row()) {
    $rows[] = $row["patchName"];
  }
  return $rows;
}

function perform_test($test) {
  global $_FORM;
  $arr = array();
  $extensions = get_loaded_extensions();

  switch ($test) {
    case "php_mbstring":
      $arr["value"] = defined("MB_OVERLOAD_MAIL") ? "Enabled" : "";
      $arr["value"] or $arr["remedy"] = "Your installation of PHP does not have the mbstring extension.";
    break;
    case "php_version":
      $arr["value"] = phpversion();
      if (!version_compare(phpversion(), "5.2.6", ">=")) {
        $arr["remedy"] = "Some functionality may not work correctly with your version of PHP. It is recommended that you upgrade.";
      }
    break;
    case "php_memory":
      $arr["value"] = get_cfg_var("memory_limit");
      if (str_replace(array("m","M"),"",$arr["value"]) < 32) {
        $arr["remedy"] = "Your installation of PHP may not have enough memory enabled. It is recommended to change the memory limit in the PHP config file: ".get_cfg_var("cfg_file_path");
      }
    break;
    case "php_gd":
      if (function_exists("gd_info")) {
        $gd_info = gd_info();
      } else {
        $gd_info = array();
      }
      $arr["value"] = $gd_info["GD Version"];
      if (!in_array("gd",$extensions)) {
        $arr["remedy"] = "Your installation of PHP does not have the GD extension. It is recommended to install GD.";
      }
    break;
    case "mysql_version":
      $arr["value"] =  "Client: ".mysql_get_client_info();
      if (!in_array("mysql",$extensions)) {
        $arr["remedy"] = "Your installation of PHP does not have the MySQL extension. allocPSA requires the use of a MySQL database.";
      }
    break;
    case "mail_exists":
      $arr["value"] = "mail()";
      if (!function_exists("mail")) {
        $arr["remedy"] = "Your installation of PHP does not have the mail() function. allocPSA may not be able to send out emails.";
      }
    break;

    case "attachments_dir":
      $arr["value"] = $_FORM["ATTACHMENTS_DIR"];
      if ($_FORM["ATTACHMENTS_DIR"] == "" || !is_dir($_FORM["ATTACHMENTS_DIR"]) || !is_writeable($_FORM["ATTACHMENTS_DIR"])) {
        $arr["remedy"] = "The directory specified for file uploads is either not defined or not writeable by the webserver process. Run:";
        $arr["remedy"].= "<br>mkdir ".$_FORM["ATTACHMENTS_DIR"];
        $arr["remedy"].= "<br>chmod a+w ".$_FORM["ATTACHMENTS_DIR"];
      }
    break;

    case "valid_currency":
      $arr["value"] = $_FORM["currency"];
      $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
      $qid = @mysql_query("SELECT * FROM currencyType WHERE currencyTypeID = '".$_FORM["currency"]."'",$link);
      if (is_resource($qid)) {
        if ($row = mysql_fetch_array($qid)) {
          $arr["value"].= " ".$row["currencyTypeName"];
        } else {
          $failed = 1;
        }
      }
      $failed and $arr["remedy"] = "The currency code you entered is not valid.";
    break;
  }

  $arr["status"] = IMG_TICK;
  $arr["remedy"] and $arr["status"] = IMG_CROSS;
  $arr["remedy"] or $arr["remedy"] = "Ok.";


  return $arr;
}













?>
