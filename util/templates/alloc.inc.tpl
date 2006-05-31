<?php
ini_set("error_reporting", E_ALL & ~E_NOTICE);

define("ALLOC_DB_NAME","CONFIG_VAR_ALLOC_DB_NAME"); // Database name
define("ALLOC_DB_USER","CONFIG_VAR_ALLOC_DB_USER"); // Database username
define("ALLOC_DB_PASS","CONFIG_VAR_ALLOC_DB_PASS"); // Database password
define("ALLOC_DB_HOST","CONFIG_VAR_ALLOC_DB_HOST"); // Database hostname (can be left blank or set to localhost)

define("ALLOC_TITLE", "allocPSA");
define("ALLOC_SHOOER","");
define("ALLOC_GD_IMAGE_TYPE","PNG");

define("ATTACHMENTS_DIR","CONFIG_VAR_ALLOC_DOCS_DIR");
define("ALLOC_LOG_DIR","CONFIG_VAR_ALLOC_LOG_DIR");

if (preg_match("/^(.*alloc[^\/]*)/",$_SERVER["SCRIPT_FILENAME"],$m)) {
  define("ALLOC_MOD_DIR",$m[1]);
} else {
  die("Fatal Error: No MOD_DIR defined.");
}

$modules = array("home"         => true
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
                ,"help"         => true
                ,"search"       => true
                ,"tools"        => true
                ,"report"       => true
                );

define("ALLOC_MODULES",serialize($modules));
unset($modules);

include(ALLOC_MOD_DIR . "/shared/local.inc.php");

?>
