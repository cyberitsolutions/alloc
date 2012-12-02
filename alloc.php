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

// The order of file processing usually goes: 
// requested_script.php -> alloc.php -> alloc_config.php -> more includes -> back to requested_script.php

function &singleton($name, $thing=null) {
  static $instances;
  isset($name) && isset($thing) and $instances[$name] = &$thing;
  return $instances[$name];
}

ini_set("error_reporting", E_ALL & ~E_NOTICE);
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.dirname(__FILE__).DIRECTORY_SEPARATOR."zend");
singleton("errors_fatal",false);
singleton("errors_format","html");
singleton("errors_logged",false);
singleton("errors_thrown",false);
singleton("errors_haltdb",false);

// Set the charset for Zend Lucene search indexer http://framework.zend.com/manual/en/zend.search.lucene.charset.html
require_once("Zend".DIRECTORY_SEPARATOR."Search".DIRECTORY_SEPARATOR."Lucene.php");
Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);

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
define("ALLOC_GD_IMAGE_TYPE","PNG");

define("DATE_FORMAT","d/m/Y");

// Source and destination modifiers for various values
define("SRC_DATABASE"       , 1);  // Reading the value from the database
define("SRC_VARIABLE"       , 2);  // Reading the value from a PHP variable (except a form variable)
define("SRC_REQUEST"        , 3);  // Reading the value from a get or post variable
define("DST_DATABASE"       , 1);  // For writing to a database
define("DST_VARIABLE"       , 2);  // For use within the PHP script itself
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
          ,"services"         
          ,"installation" 
          ,"help" 
          ,"email" 
          ,"sale"
          ,"wiki"
          ,"audit"
          ,"calendar"
          );

// Sub-dirs under ATTACHMENTS_DIR where upload, email and backup data can be stored
$external_storage_directories = array("task","client","project","invoice","comment","backups","whatsnew","wiki","logos","search","tmp");

// Helper functions
require_once(ALLOC_MOD_DIR."shared".DIRECTORY_SEPARATOR."util.inc.php");

foreach ($m as $module_name) {
  if (file_exists(ALLOC_MOD_DIR.$module_name.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."init.php")) {
    require_once(ALLOC_MOD_DIR.$module_name.DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."init.php");
    $module_class = $module_name."_module";
    $module = new $module_class();
    $modules[$module_name] = $module;
  }
}
singleton("modules",$modules);

// Get the web base url SCRIPT_PATH for the alloc site
$path = dirname($_SERVER["SCRIPT_NAME"]);
$bits = explode("/",$path);
is_array($m) && in_array(end($bits),$m) && array_pop($bits);
is_array($bits) and $path = implode("/",$bits);
$path[0] != "/" and $path = "/".$path;
$path[strlen($path)-1] != "/" and $path.="/";
define("SCRIPT_PATH",$path); 

unset($m);

$TPL = array("url_alloc_index"                          => SCRIPT_PATH."index.php"
            ,"url_alloc_login"                          => SCRIPT_PATH."login/login.php"
            ,"url_alloc_installation"                   => SCRIPT_PATH."installation/install.php"
            ,"url_alloc_styles"                         => ALLOC_MOD_DIR."css/src/"
            ,"url_alloc_stylesheets"                    => SCRIPT_PATH."css/"
            ,"url_alloc_javascript"                     => SCRIPT_PATH."javascript/"
            ,"url_alloc_images"                         => SCRIPT_PATH."images/"
            ,"url_alloc_cache"                          => SCRIPT_PATH."cache_".get_alloc_version()."/"
            ,"url_alloc_help"                           => ALLOC_MOD_DIR."help".DIRECTORY_SEPARATOR
            ,"alloc_help_link_name"                     => end(array_slice(explode("/", $_SERVER["PHP_SELF"]), -2, 1))
            ,"script_path"                              => SCRIPT_PATH
            ,"main_alloc_title"                         => end(explode("/", $_SERVER["SCRIPT_NAME"]))
            );

  
if (file_exists(ALLOC_MOD_DIR."alloc_config.php")) {
  require_once(ALLOC_MOD_DIR."alloc_config.php");
}

// ATTACHMENTS_DIR is defined above in alloc_config.php
define("ALLOC_LOGO", ATTACHMENTS_DIR."logos/logo.jpg");
define("ALLOC_LOGO_SMALL", ATTACHMENTS_DIR."logos/logo_small.jpg");

// If we're inside the installation process
if (defined("IN_INSTALL_RIGHT_NOW")) {

  // Re-direct home if an alloc_config.php already exists
  if (file_exists(ALLOC_MOD_DIR."alloc_config.php") && is_readable(ALLOC_MOD_DIR."alloc_config.php") && filesize(ALLOC_MOD_DIR."alloc_config.php") >= 2 && defined("ALLOC_DB_NAME")) {
    alloc_redirect($TPL["url_alloc_login"]);
    exit();
  }

// Else if were not in the installation process and there's no alloc_config.php file then redirect to the installation directory
} else if (!file_exists(ALLOC_MOD_DIR."alloc_config.php") || !is_readable(ALLOC_MOD_DIR."alloc_config.php") || filesize(ALLOC_MOD_DIR."alloc_config.php") < 5 || !defined("ALLOC_DB_NAME")) {
  alloc_redirect($TPL["url_alloc_installation"]);
  exit();

// Else include the alloc_config.php file and begin with proceedings..
} else {

  // Need to just touch a db connection, so that calls to
  // mysql_real_escape_string() et al, don't break because 
  // no prior connection was initialized.
  $db = new db_alloc();
  $db->connect();

  // The timezone must be dealt with before anything else uses it or php will emit a warning
  $timezone = config::get_config_item("allocTimezone");

  /*
  if (empty($timezone)) {
    $timezone = @date_default_timezone_get();
  }
  */

  date_default_timezone_set($timezone); 

  // Now the timezone is set, replace the missing stuff from the template
  $TPL["current_date"] = date("Y-m-d H:i:s");
  $TPL["today"] = date("Y-m-d");


  // The default From: email address 
  if (config::get_config_item("AllocFromEmailAddress")) {
    define("ALLOC_DEFAULT_FROM_ADDRESS", add_brackets(config::get_config_item("AllocFromEmailAddress")));
  }


//  // The default To: email address -- this is no longer used anywhere. If you revive it, you'll need to add a new config option to get around the fact there may be multiple time sheet administrators
//  $p = get_cached_table("person");
//  define("ALLOC_DEFAULT_TO_ADDRESS", "allocPSA Administrator ".add_brackets($p[config::get_config_item("timeSheetAdminEmail")]["emailAddress"]));

  // The default email bounce address
  define("ALLOC_DEFAULT_RETURN_PATH_ADDRESS",config::get_config_item("allocEmailAdmin"));


  // If a script has NO_AUTH enabled, then it will perform its own
  // authentication. And will be responsible for setting up any of:
  // $current_user and $sess.
  if (!defined("NO_AUTH")) {

    $current_user = &singleton("current_user",new person());
    $sess = new session();

    // If session hasn't been started re-direct to login page
    if (!$sess->Started()) {
      defined("NO_REDIRECT") && exit("Session expired. Please <a href='".$TPL["url_alloc_login"]."'>log in</a> again.");
      alloc_redirect($TPL["url_alloc_login"] . ($_SERVER['REQUEST_URI'] != '/' ? '?forward='.urlencode($_SERVER['REQUEST_URI']) : ''));

    // Else load up the current_user and continue
    } else if ($sess->Get("personID")) {
      $current_user->load_current_user($sess->Get("personID"));
    }
  }

  // Setup all the urls
  require_once(ALLOC_MOD_DIR."shared".DIRECTORY_SEPARATOR."global_tpl_values.inc.php");
  foreach ($alloc_urls as $k=>$v) {
    if (is_object($sess)) {
      $TPL[$k] = $sess->url(SCRIPT_PATH.$v);
    } else {
      $TPL[$k] = SCRIPT_PATH.$v;
    }
  }

  // Add user's navigation to quick list dropdown
  if (is_object($current_user) && $current_user->get_id()) {
    $history = new history();
    $history->save_history();
    $TPL["current_user"] = &$current_user;
  }
}
?>
