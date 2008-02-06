<?php 

ini_set("soap.wsdl_cache_enabled", 0);

$alloc = new SoapClient("http://alloc_dev/soap/alloc.wsdl");

$username = "alloc";
$password = "alloc";
$tableName = "transaction";

$nl = "\n";

try { 
  $key = $alloc->authenticate($username,$password);
  echo $nl."1: ".$key;
  echo $nl."2:".print_r($alloc->get_table_rows($key, $tableName),1);
} catch (SoapFault $exception) { 
  echo $exception;       
} 

echo $nl;




?>
