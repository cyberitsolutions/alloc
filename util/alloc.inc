<?php
ini_set("error_reporting", E_ALL & ~E_NOTICE);

define("ALLOC_DB_USER",""); // Database username
define("ALLOC_DB_PASS",""); // Database password
define("ALLOC_DB_NAME",""); // Database name
define("ALLOC_DB_HOST",""); // Database hostname (can be left blank or set to localhost)

define("ALLOC_TITLE", "AllocPSA");
define("ALLOC_VERSION", "v1.20");
define("ALLOC_SHOOER","");
define("ALLOC_GD_IMAGE_TYPE","PNG");
define("ATTACHMENTS_DIR","/var/local/home/alloc/alloc_uploaded_files/");

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
