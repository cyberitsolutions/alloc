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


include(ALLOC_MOD_DIR."/shared/db.inc.php");

class db_alloc extends db {
  function db_alloc() {
    parent::db(ALLOC_DB_USER,ALLOC_DB_PASS,ALLOC_DB_HOST,ALLOC_DB_NAME);
  }
}


function page_close() {
  $sess = Session::GetSession();
  $sess->Save();

  global $current_user;
  if (is_object($current_user) && $current_user->get_id()) {
    if (is_array($current_user->prefs)) {
      $current_user->select();
      $arr = serialize($current_user->prefs);
      $current_user->set_value("sessData",$arr);
    }
    $current_user->save();
  }
}

function get_alloc_modules() {
  if (defined("ALLOC_MODULES")) {
    return unserialize(ALLOC_MODULES);
  } else {
    echo "ALLOC_MODULES is not defined!";
  }
}

$modules = get_alloc_modules();

eregi("^".ALLOC_MOD_DIR."/(.*)$", $SCRIPT_FILENAME, $match) && $script_filename_short = $match[1];
eregi("^([^/]*)/", $script_filename_short, $match) && $module_name = $match[1];


if ((!isset($modules[$module_name])) && $module_name != "" && $module_name != "util") {
  die("Invalid module: $module_name");
} else {
  define("ALLOC_CURRENT_MODULE",$module_name);
}

$SCRIPT_PATH = $SCRIPT_NAME;
$script_filename_short and $SCRIPT_PATH = eregi_replace($script_filename_short, "", $SCRIPT_NAME);

define("SCRIPT_PATH",$SCRIPT_PATH);

include(ALLOC_MOD_DIR."/shared/alloc_template.inc.php");
include(ALLOC_MOD_DIR."/shared/alloc_session.inc.php");
include(ALLOC_MOD_DIR."/shared/util.inc.php");
include(ALLOC_MOD_DIR."/shared/home.inc.php");
include(ALLOC_MOD_DIR."/shared/toolbar.inc.php");
include(ALLOC_MOD_DIR."/shared/help.inc.php");
include(ALLOC_MOD_DIR."/shared/db_utils.inc.php");
include(ALLOC_MOD_DIR."/shared/module.inc.php");
include(ALLOC_MOD_DIR."/shared/event.inc.php");
include(ALLOC_MOD_DIR."/shared/alloc_email.inc.php");
include(ALLOC_MOD_DIR."/shared/alloc_cache.inc.php");

reset($modules);
while (list($module_name,) = each($modules)) {
  if ($module_name != "util") {
    include(ALLOC_MOD_DIR."/$module_name/lib/init.php");
    $module_class = $module_name."_module";
    $module = new $module_class;
    $modules[$module_name] = $module;
  }
}

define("ALLOC_DEFAULT_FROM_ADDRESS",config::get_config_item("AllocFromEmailAddress"));

include(ALLOC_MOD_DIR."/shared/global_tpl_values.inc.php");
global $current_user;
$current_user = new person;




if (defined("NO_AUTH") && NO_AUTH) {


} else {

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
    if ($sess->mode=="cookie") {
      session_start();
    }
  }
}

include(ALLOC_MOD_DIR."/shared/history.inc.php");

register_toolbar_items();
?>
