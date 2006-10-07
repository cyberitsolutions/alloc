<?php 



  $alloc = new SoapClient("alloc.wsdl"); 

  try { 
    $username = "alloc";
    $password = "alloc";

    $auth_key = $alloc->authenticate($username,$password);
    echo "<br/>Authenticated: ".$auth_key;

    #$rtn = $alloc->get_table_array($auth_key,"project");
    #echo "<br/>Authenticated: ".print_r($rtn,1);

  } catch (SoapFault $exception) { 
    echo $exception;       
  } 
?>
