<?php

eregi("^".ALLOC_MOD_DIR."/(.*)$", $SCRIPT_FILENAME, $match) && $script_filename_short = $match[1];
eregi("^([^/]*)/", $script_filename_short, $match) && $module_name = $match[1];

if ((!isset($modules[$module_name])) && $module_name != "" && $module_name != "util") {
  die("Invalid module: $module_name");
} else {
  define("ALLOC_MODULE_NAME",$module_name);
}

$SCRIPT_PATH = $SCRIPT_NAME;
$script_filename_short and $SCRIPT_PATH = eregi_replace($script_filename_short, "", $SCRIPT_NAME);


include(ALLOC_MOD_DIR."/shared/db.inc.php");
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

$orig_module = $module;
reset($modules);
while (list($module_name,) = each($modules)) {
  if ($module_name != "util") {
    include(ALLOC_MOD_DIR."/$module_name/lib/init.php");
    $module_class = $module_name."_module";
    $module = new $module_class;
    $modules[$module_name] = $module;
  }
}

global $current_user;
$current_user = new person;

class db_alloc {
  function db_alloc() {
    $this = db::get_db(ALLOC_DB_USER,ALLOC_DB_PASS,ALLOC_DB_HOST,ALLOC_DB_NAME);
  }
}


function page_close() {
  $sess = Session::GetSession();
  $sess->Save();

  global $current_user;
  if (is_object($current_user) && $current_user->get_id()) {
    if (is_array($current_user->prefs)) {
      $arr = serialize($current_user->prefs);
      $current_user->set_value("sessData",$arr);
    }
    $current_user->save();
  }
}


include(ALLOC_MOD_DIR."/shared/global_tpl_values.inc.php");


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
    if ($sess->mode=="cookie") session_start();
  }
}

include(ALLOC_MOD_DIR."/shared/history.inc.php");

register_toolbar_items();
?>
