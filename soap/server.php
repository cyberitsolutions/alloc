<?php

// enable error reporting
use_soap_error_handler(true);
ini_set("soap.wsdl_cache_enabled", 0);
define("DO_NOT_REDIRECT_TO_LOGIN",1); 
require_once("../alloc.php");
$server = new SoapServer(dirname(__FILE__)."/alloc.wsdl"); 
$server->setClass("alloc_soap"); 
$data = file_get_contents('php://input');
$server->handle($data);


#echo "<pre>".print_r($data,1)."</pre>";


?>
