<?php 

ini_set("soap.wsdl_cache_enabled", 0);

$alloc = new SoapClient("http://leaf/alloc2/soap/alloc.wsdl");

$username = "alloc";
$password = "alloc";

$nl = "\n";

try { 
  $key = $alloc->authenticate($username,$password);
  echo $nl."111: ".$key;

  echo $alloc->get_help("get_list");

  #$str = $alloc->get_list($key, "task", array('return'=>'array',"taskView"=>"byProject","personID"=>60, "taskTypeID"=>2));
  $str = $alloc->get_list($key, "transaction", array('return'=>'array', 'debug'=>1));

  echo $nl."2222:".print_r($str,1);

} catch (SoapFault $exception) { 
  echo $exception;       
} 

echo $nl;




?>
