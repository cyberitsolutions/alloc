<?php 

ini_set("soap.wsdl_cache_enabled", 0);

$alloc = new SoapClient("http://leaf/alloc6/soap/alloc.wsdl");

$username = "alloc";
$password = "alloc";

$nl = "\n";

try { 
  $key = $alloc->authenticate($username,$password);
  echo $nl."1: ".$key;
  $str = $alloc->get_list($key, "project", array('return'=>'array','applyFilter'=>'1','showProjectName'=>'1'));
  echo $nl."2222:".print_r($str,1);

} catch (SoapFault $exception) { 
  echo $exception;       
} 

echo $nl;




?>
