<?php 

ini_set("soap.wsdl_cache_enabled", 0);

$alloc = new SoapClient("http://alloc_dev/soap/alloc.wsdl");

$username = "alloc";
$password = "alloc";

$taskID = 6235; // has comments hopefully

try { 
  $key = $alloc->authenticate($username,$password);
  echo "<br>1: ".$key;
  echo "<br>2: <pre>".print_r($alloc->get_task_comments($key, $taskID),1)."</pre>";
} catch (SoapFault $exception) { 
  echo "<pre>".$exception."</pre>";       
} 





?>
