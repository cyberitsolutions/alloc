<?php

// enable error reporting
use_soap_error_handler(true);
ini_set("soap.wsdl_cache_enabled", 0);
define("NO_AUTH",1); 
require_once("../alloc.php");
$options = array('features' => SOAP_USE_XSI_ARRAY_TYPE + SOAP_SINGLE_ELEMENT_ARRAYS);
$server = new SoapServer(dirname(__FILE__)."/alloc.wsdl", $options); 
$server->setClass("alloc_soap"); 
$data = file_get_contents('php://input');
$server->handle($data);


#echo "<pre>".print_r($data,1)."</pre>";


?>
