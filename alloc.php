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

// The order of file processing usually goes: 
// requested_script.php -> alloc.php -> alloc_config.php -> more includes -> back to requested_script.php


ini_set("error_reporting", E_ALL & ~E_NOTICE);

// Can't call this script directly..
if (basename($_SERVER["SCRIPT_FILENAME"]) == "alloc.php") {
  die();
} 

// Undo magic quotes if it's enabled
if (get_magic_quotes_gpc()) {
  function stripslashes_array($array) {
    return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
  }

  $_COOKIE = stripslashes_array($_COOKIE);
  $_FILES = stripslashes_array($_FILES);
  $_GET = stripslashes_array($_GET);
  $_POST = stripslashes_array($_POST);
  $_REQUEST = stripslashes_array($_REQUEST);
}

// Get the alloc directory
$f = trim(dirname(__FILE__));
substr($f,-1,1) != DIRECTORY_SEPARATOR and $f.= DIRECTORY_SEPARATOR;
define("ALLOC_MOD_DIR",$f);
unset($f);

define("APPLICATION_NAME", "allocPSA");
define("ALLOC_SHOOER","");
define("ALLOC_GD_IMAGE_TYPE","PNG");

define("DATE_FORMAT","d/m/Y");

// Task type definitions, these are shared across modules, so we're specifying them here
define("TT_TASK"     , 1);
define("TT_PHASE"    , 2);
define("TT_MESSAGE"  , 3);
define("TT_FAULT"    , 4);
define("TT_MILESTONE", 5);

// Source and destination modifiers for various values
define("SRC_DATABASE"       , 1);  // Reading the value from the database
define("SRC_VARIABLE"       , 2);  // Reading the value from a PHP variable (except a form variable)
define("SRC_REQUEST"        , 3);  // Reading the value from a get or post variable
define("DST_DATABASE"       , 1);  // For writing to a database
define("DST_VARIABLE"       , 2);  // For use within the PHP script itself
define("DST_HTML_ATTRIBUTE" , 3);  // For use in a HTML elements attribute - e.g. a form input's value or a link's href
define("DST_HTML_DISPLAY"   , 4);  // For display to the user as non-editable HTML text
  
// The list of all the modules that are enabled for this install of alloc
$m = array("shared"       
          ,"home"         
          ,"project"      
          ,"task"         
          ,"time"         
          ,"finance"      
          ,"invoice"      
          ,"client"       
          ,"comment"       
          ,"item"         
          ,"person"       
          ,"announcement" 
          ,"reminder" 
          ,"security"     
          ,"config"       
          ,"search"       
          ,"tools"        
          ,"report"       
          ,"login"        
          ,"soap"         
          ,"installation" 
          ,"help" 
          ,"email" 
          );

// Sub-dirs under ATTACHMENTS_DIR where upload, email and backup data can be stored
$external_storage_directories = array("task","client","project","invoice","comment","backups");

// Helper functions
require_once(ALLOC_MOD_DIR."shared".DIRECTORY_SEPARATOR."util.inc.php");

// Get the web base url for the alloc site
define("SCRIPT_PATH",get_script_path($m)); 

foreach ($m as $module_name) {
  if (file_exists(ALLOC_MOD_DIR.$module_name.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."init.php")) {
    require_once(ALLOC_MOD_DIR.$module_name.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."init.php");
    $module_class = $module_name."_module";
    $module = new $module_class;
    $modules[$module_name] = $module;
  }
}
unset($m);

$TPL = array("url_alloc_index"                          => SCRIPT_PATH."index.php"
            ,"url_alloc_login"                          => SCRIPT_PATH."login/login.php"
            ,"url_alloc_installation"                   => SCRIPT_PATH."installation/install.php"
            ,"url_alloc_styles"                         => ALLOC_MOD_DIR."styles/"
            ,"url_alloc_stylesheets"                    => SCRIPT_PATH."css/"
            ,"url_alloc_javascript"                     => SCRIPT_PATH."javascript/"
            ,"url_alloc_images"                         => SCRIPT_PATH."images/"
            ,"url_alloc_help"                           => ALLOC_MOD_DIR."help".DIRECTORY_SEPARATOR
            ,"current_date"                             => date("Y-m-d H:i:s")
            ,"today"                                    => date("Y-m-d")
            ,"alloc_help_link_name"                     => end(array_slice(explode("/", $_SERVER["PHP_SELF"]), -2, 1))
            ,"script_path"                              => SCRIPT_PATH
            ,"table_box"                                => "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\">\n"
            ,"table_box_border"                         => "<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\">\n"
            ,"table_list"                               => "<table border=\"0\" cellspacing=\"0\" class=\"tasks sortable\">\n"
            ,"main_alloc_title"                         => end(explode("/", $_SERVER["SCRIPT_NAME"]))
            );

  
if (file_exists(ALLOC_MOD_DIR."alloc_config.php")) {
  require_once(ALLOC_MOD_DIR."alloc_config.php");
}

// If we're inside the installation process
if (defined("IN_INSTALL_RIGHT_NOW")) {

  // Re-direct home if an alloc_config.php already exists
  if (file_exists(ALLOC_MOD_DIR."alloc_config.php") && is_readable(ALLOC_MOD_DIR."alloc_config.php") && filesize(ALLOC_MOD_DIR."alloc_config.php") >0 && defined("ALLOC_DB_NAME")) {
    header("Location: ".$TPL["url_alloc_login"]);
    exit();
  }

// Else if were not in the installation process and there's no alloc_config.php file then redirect to the installation directory
} else if (!file_exists(ALLOC_MOD_DIR."alloc_config.php") || !is_readable(ALLOC_MOD_DIR."alloc_config.php") || filesize(ALLOC_MOD_DIR."alloc_config.php") < 5 || !defined("ALLOC_DB_NAME")) {
  header("Location: ".$TPL["url_alloc_installation"]);
  exit();

// Else include the alloc_config.php file and begin with proceedings..
} else {

  // Need to just touch a db connection, so that calls to
  // mysql_real_escape_string() et al, don't break because 
  // no prior connection was initialized.
  $db = new db_alloc();
  $db->connect();

  // Check for existing session..
  $sess = new Session;

  // Include all the urls
  require_once(ALLOC_MOD_DIR."shared".DIRECTORY_SEPARATOR."global_tpl_values.inc.php");

  // Setup some useful constants
  define("ALLOC_DEFAULT_FROM_ADDRESS",get_default_from_address());
  define("ALLOC_DEFAULT_TO_ADDRESS",get_default_to_address());
  define("ALLOC_DEFAULT_RETURN_PATH_ADDRESS",config::get_config_item("allocEmailAdmin"));

  // Setup a current_user person who will represent the logged in user
  $current_user = new person;


  // If the session hasn't started and we're not on the login screen, then redirect to login 
  // Some scripts don't require authentication
  if (!defined("NO_AUTH") && !$sess->Started() && !defined("DO_NOT_REDIRECT_TO_LOGIN")) { 
    header("Location: ". $TPL["url_alloc_login"]);
    exit();

  } 
  
  if (!defined("NO_AUTH") && !defined("DO_NOT_REDIRECT_TO_LOGIN")) {
    $current_user = person::load_get_current_user($sess->Get("personID"));

    // Save history entry
    $history = new history;
    $history->save_history();
  }
}
?>
