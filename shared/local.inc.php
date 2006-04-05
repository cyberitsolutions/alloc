<?php

# PHPLIB Stuff
require(ALLOC_MOD_DIR."/shared/phplib_db_mysql.inc.php");  
require(ALLOC_MOD_DIR."/shared/phplib_ct_sql.inc.php");    
require(ALLOC_MOD_DIR."/shared/phplib_session.inc.php");   
require(ALLOC_MOD_DIR."/shared/phplib_auth.inc.php");      
require(ALLOC_MOD_DIR."/shared/phplib_perm.inc.php");       
require(ALLOC_MOD_DIR."/shared/phplib_user.inc.php");    
require(ALLOC_MOD_DIR."/shared/phplib_page.inc.php");


eregi("^".ALLOC_MOD_DIR."/(.*)$", $SCRIPT_FILENAME, $match) && $script_filename_short = $match[1];
eregi("^([^/]*)/", $script_filename_short, $match) && $module_name = $match[1];

if ((!isset($modules[$module_name])) && $module_name != "" && $module_name != "util") {
  die("Invalid module: $module_name");
} else {
  define("ALLOC_MODULE_NAME",$module_name);
}

$SCRIPT_PATH = $SCRIPT_NAME;
$script_filename_short and $SCRIPT_PATH = eregi_replace($script_filename_short, "", $SCRIPT_NAME);


include(ALLOC_MOD_DIR."/shared/alloc_phplib.inc.php");
include(ALLOC_MOD_DIR."/shared/template.inc.php");
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

$module = $orig_module;
if (defined("NO_AUTH") && NO_AUTH) {
  page_open(array("sess"=>"alloc_Session"));
} else {
  page_open(array("sess"=>"alloc_Session", "auth"=>"alloc_Auth", "perm"=>"alloc_Perm", "user"=>"alloc_User"));
}

include(ALLOC_MOD_DIR."/shared/global_tpl_values.inc.php");
include(ALLOC_MOD_DIR."/shared/history.inc.php");

register_toolbar_items();
?>
