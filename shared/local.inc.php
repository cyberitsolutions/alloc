<?php

# PHPLIB Stuff
require(ALLOC_MOD_DIR."/shared/phplib_db_mysql.inc");  
require(ALLOC_MOD_DIR."/shared/phplib_ct_sql.inc");    
require(ALLOC_MOD_DIR."/shared/phplib_session.inc");   
require(ALLOC_MOD_DIR."/shared/phplib_auth.inc");      
require(ALLOC_MOD_DIR."/shared/phplib_perm.inc");       
require(ALLOC_MOD_DIR."/shared/phplib_user.inc");    
require(ALLOC_MOD_DIR."/shared/phplib_page.inc");


eregi("^".ALLOC_MOD_DIR."/(.*)$", $SCRIPT_FILENAME, $match) && $script_filename_short = $match[1];
eregi("^([^/]*)/", $script_filename_short, $match) && $module_name = $match[1];

if ((!isset($modules[$module_name])) && $module_name != "" && $module_name != "util") {
  die("Invalid module: $module_name");
} else {
  define("ALLOC_MODULE_NAME",$module_name);
}

$SCRIPT_PATH = $SCRIPT_NAME;
$script_filename_short and $SCRIPT_PATH = eregi_replace($script_filename_short, "", $SCRIPT_NAME);


include(ALLOC_MOD_DIR."/shared/alloc_phplib.inc");
include(ALLOC_MOD_DIR."/shared/template.php");
include(ALLOC_MOD_DIR."/shared/util.inc");
include(ALLOC_MOD_DIR."/shared/home.inc");
include(ALLOC_MOD_DIR."/shared/toolbar.inc");
include(ALLOC_MOD_DIR."/shared/help.inc");
include(ALLOC_MOD_DIR."/shared/db_utils.inc");
include(ALLOC_MOD_DIR."/shared/module.inc");
include(ALLOC_MOD_DIR."/shared/event.inc");
include(ALLOC_MOD_DIR."/shared/alloc_email.inc");
include(ALLOC_MOD_DIR."/shared/alloc_cache.inc");

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

include(ALLOC_MOD_DIR."/shared/global_tpl_values.inc");
include(ALLOC_MOD_DIR."/shared/history.inc");

register_toolbar_items();
?>
