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

// The order of file processing usually goes: 
// requested_script.php -> alloc.php -> alloc_config.php -> more includes -> back to requested_script.php


ini_set("error_reporting", E_ALL & ~E_NOTICE);

// Can't call this script directly..
if (basename($_SERVER["SCRIPT_FILENAME"]) == "alloc.php") {
  die();
} 
define("ALLOC_MOD_DIR",dirname(__FILE__));
define("ALLOC_TITLE", "allocPSA");
define("ALLOC_SHOOER","");
define("ALLOC_GD_IMAGE_TYPE","PNG");

// Task type definitions, these are shared across modules, so we're specifying them here
define("TT_TASK"     , 1);
define("TT_PHASE"    , 2);
define("TT_MESSAGE"  , 3);
define("TT_FAULT"    , 4);
define("TT_MILESTONE", 5);

define("ALLOC_MODULES",serialize(array("shared"       => true
                                      ,"home"         => true
                                      ,"project"      => true
                                      ,"time"         => true
                                      ,"finance"      => true
                                      ,"client"       => true
                                      ,"item"         => true
                                      ,"person"       => true
                                      ,"announcement" => true
                                      ,"notification" => true
                                      ,"security"     => true
                                      ,"config"       => true
                                      ,"search"       => true
                                      ,"tools"        => true
                                      ,"report"       => true
                                      ,"login"        => true
                                      ,"soap"         => true
                                      ,"history"      => true
                                      ,"installation" => true
                                      )));
                                     #,"util"         => true


require_once(ALLOC_MOD_DIR."/shared/util.inc.php");
define("SCRIPT_PATH",get_script_path()); // Needs ALLOC_MOD_DIR to be defined, and get_script_path() is defined in shared/util.inc.php
define("ALLOC_VERSION", get_alloc_version());

$m = get_alloc_modules();
foreach ($m as $module_name => $v) {
  if (file_exists(ALLOC_MOD_DIR."/$module_name/lib/init.php")) {
    require_once(ALLOC_MOD_DIR."/$module_name/lib/init.php");
    $module_class = $module_name."_module";
    $module = new $module_class;
    $modules[$module_name] = $module;
  }
}

$TPL = array("url_alloc_index"                          => SCRIPT_PATH."index.php"
            ,"url_alloc_login"                          => SCRIPT_PATH."login/login.php"
            ,"url_alloc_installation"                   => SCRIPT_PATH."installation/install.php"
            ,"current_date"                             => date("Y-m-d H:i:s")
            ,"today"                                    => date("Y-m-d")
            ,"alloc_help_link_name"                     => end(array_slice(explode("/", $_SERVER["PHP_SELF"]), -2, 1))
            ,"script_path"                              => SCRIPT_PATH
            ,"table_box"                                => "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\">"
            ,"table_box_border"                         => "<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" class=\"box\">"
            ,"main_alloc_title"                         => end(explode("/", $_SERVER["SCRIPT_NAME"]))
            ,"ALLOC_VERSION"                            => ALLOC_VERSION
            ,"url_alloc_stylesheets"                    => SCRIPT_PATH."stylesheets/"
            ,"url_alloc_javascript"                     => SCRIPT_PATH."javascript/"
            ,"url_alloc_images"                         => SCRIPT_PATH."images/"
            ,"url_alloc_help"                           => ALLOC_MOD_DIR."/help/"
            );


// If we're inside the installation process
if (defined("IN_INSTALL_RIGHT_NOW")) {

  // Re-direct home if an alloc_config.php already exists
  if (file_exists(ALLOC_MOD_DIR."/alloc_config.php") && is_readable(ALLOC_MOD_DIR."/alloc_config.php") && filesize(ALLOC_MOD_DIR."/alloc_config.php") >0) {
    header("Location: ".$TPL["url_alloc_login"]);
    exit();
  }


// Else if were not in the installation process and there's no alloc_config.php file then redirect to the installation directory
} else if (!file_exists(ALLOC_MOD_DIR."/alloc_config.php") || !is_readable(ALLOC_MOD_DIR."/alloc_config.php") || filesize(ALLOC_MOD_DIR."/alloc_config.php") == 0) {
  header("Location: ".$TPL["url_alloc_installation"]);
  exit();

// Else include the alloc_config.php file and begin with proceedings..
} else {
  require_once(ALLOC_MOD_DIR."/alloc_config.php");

  define("ALLOC_DEFAULT_FROM_ADDRESS",get_default_from_address());
  require_once(ALLOC_MOD_DIR."/shared/global_tpl_values.inc.php");

  $current_user = new person;

  if (!defined("NO_AUTH")) {

    // Check for existing session..
    $sess = new Session;

    if (!$sess->Started() && !defined("IN_LOGIN_RIGHT_NOW")) { 
      header("Location: ". $TPL["url_alloc_login"]);
      exit();

    } else {
      $current_user = person::load_get_current_user($sess->Get("personID"));
    }
  }

  // Save history entry
  $history = new history;
  $history->save_history();

  register_toolbar_items($modules);
}


?>
