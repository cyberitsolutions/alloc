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

// The order of file processing goes: 
// requested_script.php -> /php/include/path/../alloc.php -> ./local.inc.php (this) -> more includes -> back to requested_script.php


// Get alloc version
if (file_exists(ALLOC_MOD_DIR."/util/alloc_version") && is_readable(ALLOC_MOD_DIR."/util/alloc_version") && !defined("ALLOC_VERSION")) {
  $v = file(ALLOC_MOD_DIR."/util/alloc_version");
  define("ALLOC_VERSION", $v[0]);
  unset($v);
}

// Include the util functions
require_once(ALLOC_MOD_DIR."/shared/util.inc.php");

$modules = get_alloc_modules();
$fake_modules = array("util","login","shared");

eregi("^".ALLOC_MOD_DIR."/(.*)$", $_SERVER["SCRIPT_FILENAME"], $match) && $script_filename_short = $match[1];
eregi("^([^/]*)/", $script_filename_short, $match) && $module_name = $match[1];


if ((!isset($modules[$module_name])) && $module_name != "" && !in_array($module_name,$fake_modules)) {
  die("Invalid module: $module_name");
} else {
  define("ALLOC_CURRENT_MODULE",$module_name);
}

$SCRIPT_PATH = $_SERVER["SCRIPT_NAME"];
$script_filename_short and $SCRIPT_PATH = eregi_replace($script_filename_short, "", $_SERVER["SCRIPT_NAME"]);
define("SCRIPT_PATH",$SCRIPT_PATH);

// Task type definitions, these are shared across modules, so we're specifying them here
define("TT_TASK"     , 1);
define("TT_PHASE"    , 2);
define("TT_MESSAGE"  , 3);
define("TT_FAULT"    , 4);
define("TT_MILESTONE", 5);


// Include stuff from shared/ 
require_once(ALLOC_MOD_DIR."/shared/template.inc.php");
require_once(ALLOC_MOD_DIR."/shared/help.inc.php");
require_once(ALLOC_MOD_DIR."/shared/db_utils.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_db.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_db_alloc.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_session.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_home_item.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_toolbar_item.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_db_field.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_db_entity.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_module.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_event.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_alloc_email.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_alloc_cache.inc.php");
require_once(ALLOC_MOD_DIR."/shared/class_history.inc.php");

foreach ($modules as $module_name => $v) {
  if (!in_array($module_name,$fake_modules)) {
    require_once(ALLOC_MOD_DIR."/$module_name/lib/init.php");
    $module_class = $module_name."_module";
    $module = new $module_class;
    $modules[$module_name] = $module;
  }
}

// Wrap angle brackets around the default From: email address 
$f = config::get_config_item("AllocFromEmailAddress");
$l = strpos($f, "<");
$r = strpos($f, ">");
$l === false and $f = "<".$f;
$r === false and $f .= ">";
define("ALLOC_DEFAULT_FROM_ADDRESS","allocPSA ".$f);
unset($f, $l, $r);

require_once(ALLOC_MOD_DIR."/shared/global_tpl_values.inc.php");
$current_user = new person;


if (!defined("NO_AUTH")) {

  // Check for existing session..
  $sess = Session::GetSession();

  if (!$sess->Started() && !defined("IN_LOGIN_RIGHT_NOW")) { 
    header("Location: ". $TPL["url_alloc_login"]);
    exit();

  } else {
    $current_user = new person;
    $current_user->set_id($sess->Get("personID"));
    $current_user->select();
    $current_user->prefs = unserialize($current_user->get_value("sessData"));
    if (is_array($current_user->prefs)) {
      foreach ($current_user->prefs as $n=>$v) {
        ${$n} = $v;
        global ${$n};
      }
      unset($n,$v);
    }
    isset($current_user->prefs["topTasksNum"]) or $current_user->prefs["topTasksNum"] = 5;
    $current_user->prefs["topTasksStatus"] or $current_user->prefs["topTasksStatus"] = "not_completed";
    isset($current_user->prefs["projectListNum"]) or $current_user->prefs["projectListNum"] = "10";
  }
}


// Take care of saving history entries
$history = new history;
$ignored_files = $history->get_ignored_files();
$ignored_files[] = "index.php";
$ignored_files[] = "home.php";
$ignored_files[] = "taskSummary.php";
$ignored_files[] = "projectList.php";
$ignored_files[] = "timeSheetList.php";
$ignored_files[] = "menu.php";
$ignored_files[] = "clientList.php";
$ignored_files[] = "itemLoan.php";
$ignored_files[] = "personList.php";
$ignored_files[] = "eventFilterList.php";
$ignored_files[] = "search.php";
$ignored_files[] = "person.php";

if ($_SERVER["QUERY_STRING"]) {
  $qs = preg_replace("[&$]", "", $_SERVER["QUERY_STRING"]);
  $qs = "?".$qs;
}

$file = end(explode("/", $_SERVER["SCRIPT_NAME"])).$qs;

if (is_object($current_user) && !in_array($file, $ignored_files)
    && !$_GET["historyID"] && !$_POST["historyID"] && $the_label = $history->get_history_label($_SERVER["SCRIPT_NAME"], $qs)) {

  $the_place = $_SERVER["SCRIPT_NAME"].$qs;
  $history = new history;
  $history->set_value("personID", $current_user->get_id());
  $history->set_value("the_place", $the_place);
  $history->set_value("the_label", $the_label);
  $history->save();
}

register_toolbar_items();
?>
