<?php 

ini_set("soap.wsdl_cache_enabled", 0);

$alloc = new SoapClient("http://leaf/alloc/soap/alloc.wsdl");

$username = "";
$password = "";

$nl = "\n";

try { 
  $key = $alloc->authenticate($username,$password);
  echo $nl."Key: ".$key."\n";

  echo $alloc->get_help();

  #$str = $alloc->get_list($key, "task", array('return'=>'array',"taskView"=>"byProject","personID"=>60, "taskTypeID"=>2));
  #$str = $alloc->get_list($key, "transaction", array('return'=>'array', 'debug'=>0));
  #$str = $alloc->get_list($key, "comment", array('entity'=>'task', 'entityID'=>12643));

  echo $nl.print_r($str,1);

} catch (SoapFault $exception) { 
  echo "This is the exception: ".$exception;       
} 

echo $nl;




?>
