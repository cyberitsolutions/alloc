<?php

$_PHPLIB["libdir"] = ALLOC_MOD_DIR."/shared/";

require($_PHPLIB["libdir"]."phplib_db_mysql.inc");  
require($_PHPLIB["libdir"]."phplib_ct_sql.inc");    
require($_PHPLIB["libdir"]."phplib_session.inc");   
require($_PHPLIB["libdir"]."phplib_auth.inc");      
require($_PHPLIB["libdir"]."phplib_perm.inc");       
require($_PHPLIB["libdir"]."phplib_user.inc");    
require($_PHPLIB["libdir"]."phplib_page.inc");


define("REWRITE_TEMPLATE_URLS", false);
eregi("^".ALLOC_MOD_DIR."/(.*)$", $SCRIPT_FILENAME, $match) && $SCRIPT_FILENAME_SHORT = $match[1];
$MODULE_NAME = "";
eregi("^([^/]*)/", $SCRIPT_FILENAME_SHORT, $match) && $MODULE_NAME = $match[1];
if ((!isset($modules[$MODULE_NAME])) && $MODULE_NAME != "") {
  die("Invalid module: $MODULE_NAME");
}
$SCRIPT_PATH = $SCRIPT_NAME;
$SCRIPT_FILENAME_SHORT and $SCRIPT_PATH = eregi_replace($SCRIPT_FILENAME_SHORT, "", $SCRIPT_NAME);
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
  include(ALLOC_MOD_DIR."/$module_name/lib/init.php");
  $module_class = $module_name."_module";
  $module = new $module_class;
  $modules[$module_name] = $module;
}

$module = $orig_module;
if (isset($NO_AUTH) && (!isset($HTTP_GET_VARS["NO_AUTH"])) && (!isset($HTTP_POST_VARS["NO_AUTH"]))) {
  page_open(array("sess"=>"alloc_Session"));
} else {
  page_open(array("sess"=>"alloc_Session", "auth"=>"alloc_Auth", "perm"=>"alloc_Perm", "user"=>"alloc_User"));
}

include(ALLOC_MOD_DIR."/shared/global_tpl_values.inc");
include(ALLOC_MOD_DIR."/shared/history.inc");

register_toolbar_items();
?>
