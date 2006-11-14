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

class installation_module extends module {
}

function get_patch_file_list() {
  $dir = ALLOC_MOD_DIR."/patches/";
  $files = array();
  if (is_dir($dir)) {
    $dh = opendir($dir);
    if ($dh) {
      while (($file = readdir($dh)) !== false) {
        if (filetype($dir.$file) == "file") {
          $files[] = $file;
        }
      }
      closedir($dh);
    }

    // Sort files in natural counting order file8 fil9 fil10
    natsort($files);
    // Order the indexes too
    $files = array_values($files);
  }
  return $files;
}

function get_most_recent_patch() {
  $db = new db_alloc;
  $db->query("SELECT patchName FROM patchLog ORDER BY patchDate DESC,patchName DESC LIMIT 1;");
  $row = $db->row();
  return $row["patchName"];
}

function perform_test($test) {
  global $_FORM;
  $arr = array();
  $extensions = get_loaded_extensions();

  switch ($test) {
    case "php_version":
      $arr["value"] = phpversion();
      if (!version_compare(phpversion(), "4.3.0", ">=")) {
        $arr["remedy"] = "Some functionality will not work correctly with your version of PHP. It is recommended that you upgrade.";
      }
    break;
    case "php_memory":
      $arr["value"] = get_cfg_var("memory_limit");
      if (str_ireplace("m","",$arr["value"]) < 32) {
        $arr["remedy"] = "PHP does not have enough memory enabled. It is recommended to change the memory limit in the PHP config file: ".get_cfg_var("cfg_file_path");
      }
    break;
    case "php_gd":
      $gd_info = gd_info();
      $arr["value"] = $gd_info["GD Version"];
      if (!in_array("gd",$extensions)) {
        $arr["remedy"] = "PHP does not have the GD extension. It is recommended to install GD.";
      }
    break;
    case "mysql_version":
      $arr["value"] =  "Client: ".mysql_get_client_info();
      if (!in_array("mysql",$extensions)) {
        $arr["remedy"] = "PHP does not have the MySQL extension. allocPSA requires the use of a MySQL database.";
      }
    break;
    case "mail_exists":
      $arr["value"] = get_cfg_var("sendmail_path");
      if (!function_exists("mail") || !file_exists(get_cfg_var("sendmail_path"))) {
        $arr["remedy"] = "PHP doesn't know the path to sendmail, alloc may not be able to send out emails. Please check the sendmail_path option in the PHP config file: ".get_cfg_var("cfg_file_path");
      }
    break;
    case "db_connect":
      $arr["value"] = "User:".$_FORM["ALLOC_DB_USER"]."<br/>Password:".$_FORM["ALLOC_DB_PASS"]."<br/>Host:".$_FORM["ALLOC_DB_HOST"];
      $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
      if (!$_FORM["ALLOC_DB_USER"] || !$link) {
        $arr["remedy"] = "Unable to connect to the MySQL server. Check the credentials in the 'Input' step.";
      }
    break;

    case "db_select":
      $arr["value"] = $_FORM["ALLOC_DB_NAME"];
      $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
      if (!@mysql_select_db($_FORM["ALLOC_DB_NAME"], $link)) {
        $arr["remedy"] = "The database '".$_FORM["ALLOC_DB_NAME"]."' cannot be selected. It may not exist. Please repeat the 'DB Setup' step.";
      }
    break;

    case "db_tables":
      $link = @mysql_connect($_FORM["ALLOC_DB_HOST"],$_FORM["ALLOC_DB_USER"],$_FORM["ALLOC_DB_PASS"]);
      $qid = @mysql_query("SHOW TABLES",$link);
      if (is_resource($qid)) {
        while ($row = mysql_fetch_array($qid)) {
          $count++;
        }
      }
      $arr["value"] = sprintf("%d",$count);
      if (!$count || $count < 2) {
        $arr["remedy"] = "The database tables don't appear to have imported correctly. Please repeat the 'DB Install' step.";
      }
    break;

    case "attachments_dir":
      $arr["value"] = $_FORM["ATTACHMENTS_DIR"];
      if ($_FORM["ATTACHMENTS_DIR"] == "" || !is_dir($_FORM["ATTACHMENTS_DIR"]) || !is_writeable($_FORM["ATTACHMENTS_DIR"])) {
        $arr["remedy"] = "The directory specified for file uploads is either not defined or not writeable by the webserver process. Run:";
        $arr["remedy"].= "<br/>mkdir ".$_FORM["ATTACHMENTS_DIR"];
        $arr["remedy"].= "<br/>chmod a+w ".$_FORM["ATTACHMENTS_DIR"];
      }
    break;

    case "alloc_config":
      $arr["value"] = "alloc_config.php";
      if (!file_exists(ALLOC_CONFIG_PATH) || !is_writeable(ALLOC_CONFIG_PATH)) {
        $arr["remedy"] = "Please create an empty, webserver-writeable file: ";
        $arr["remedy"].= "<br/><nobr>touch ".ALLOC_CONFIG_PATH."</nobr>";
        $arr["remedy"].= "<br/><nobr>chmod 600 ".ALLOC_CONFIG_PATH."</nobr>";
        $arr["remedy"].= "<br/><nobr>chown apache ".ALLOC_CONFIG_PATH."</nobr>";
      }
    break;
  }

  $arr["status"] = IMG_TICK;
  $arr["remedy"] and $arr["status"] = IMG_CROSS;
  $arr["remedy"] or $arr["remedy"] = "Ok.";


  return $arr;
}













?>
