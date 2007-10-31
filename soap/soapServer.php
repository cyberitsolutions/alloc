<?php

ini_set("soap.wsdl_cache_enabled", 0);
define("NO_AUTH",true);
require_once("../alloc.php");
require_once("./class_alloc_soap.inc.php");
$server = new SoapServer("alloc.wsdl"); 
$server->setClass("alloc_soap"); 
$data = file_get_contents('php://input');
$server->handle($data);


#echo "<pre>".print_r($data,1)."</pre>";


?>
