<?php

$_PHPLIB["libdir"] = $MOD_SHARED_DIR."/";

require($_PHPLIB["libdir"]."phplib_db_mysql.inc");  
require($_PHPLIB["libdir"]."phplib_ct_sql.inc");    
require($_PHPLIB["libdir"]."phplib_session.inc");   
require($_PHPLIB["libdir"]."phplib_auth.inc");      
require($_PHPLIB["libdir"]."phplib_perm.inc");       
require($_PHPLIB["libdir"]."phplib_user.inc");    
require($_PHPLIB["libdir"]."phplib_page.inc");


define("REWRITE_TEMPLATE_URLS", false);
eregi("^$MOD_DIR/(.*)$", $SCRIPT_FILENAME, $match) && $SCRIPT_FILENAME_SHORT = $match[1];
$MODULE_NAME = "";
eregi("^([^/]*)/", $SCRIPT_FILENAME_SHORT, $match) && $MODULE_NAME = $match[1];
if ((!isset($modules[$MODULE_NAME])) && $MODULE_NAME != "") {
  die("Invalid module: $MODULE_NAME");
}
  // This gets the path part of the URI.  It should get something
  // like /alloc/, but it could be just /
$SCRIPT_PATH = $SCRIPT_NAME;
$SCRIPT_FILENAME_SHORT and $SCRIPT_PATH = eregi_replace($SCRIPT_FILENAME_SHORT, "", $SCRIPT_NAME);
include($MOD_SHARED_DIR."/alloc_phplib.inc");
include($MOD_SHARED_DIR."/php_template2.php");
include($MOD_SHARED_DIR."/util.inc");
include($MOD_SHARED_DIR."/home.inc");
include($MOD_SHARED_DIR."/toolbar.inc");
include($MOD_SHARED_DIR."/help.inc");
include($MOD_SHARED_DIR."/db_utils.inc");
include($MOD_SHARED_DIR."/module.inc");
include($MOD_SHARED_DIR."/event.inc");
include($MOD_SHARED_DIR."/alloc_email.inc");
include($MOD_SHARED_DIR."/alloc_cache.inc");
$orig_module = $module;
reset($modules);
while (list($module_name,) = each($modules)) {
  include($MOD_DIR."/$module_name/lib/init.php");
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

include($MOD_SHARED_DIR."/global_tpl_values.inc");
include($MOD_SHARED_DIR."/history.inc");
register_toolbar_items();



?>
