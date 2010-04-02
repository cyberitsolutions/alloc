<?php 

ini_set("soap.wsdl_cache_enabled", 0);


$alloc = new SoapClient("http://alloc_dev/soap/alloc.wsdl");

$username = "alloc";
$password = "alloc";

$taskID = 11041;
$duration = 5.23;
$comments = "hey commedsahdjkshajdkants!";


try { 
  $key = $alloc->authenticate($username,$password);
  echo "<br>1: ".$key;
  echo "<br>2: ".$alloc->add_timeSheetItem_by_task($key, $taskID, $duration, $comments)."</pre>";
} catch (SoapFault $exception) { 
  echo "<pre>".$exception."</pre>";       
} 





?>
