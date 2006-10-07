<?php

define("NO_AUTH",true);
require_once("../alloc.php");

class alloc_soap {

  function authenticate($username,$password) {
    $person = new person;
    $sess = new Session;
    $row = $person->get_valid_login_row($username,$password); 
    if ($row) {
      $sess->Start($row);
      $sess->UseGet();
      $sess->Save();
      return $sess->GetKey();
    } else {
      throw new SoapFault("Server","Authentication Failed(1)."); 
    }

  }  

/*
  function check_auth($key) {
    $sess = new Session($key);
    if (!$sess->Started()) {
      throw new SoapFault("Server","Authentication Failed(2)."); 
      exit();
    } else {
      $person = new person;
      $current_user = $person->load_get_current_user($sess->Get("personID"));
      global $current_user;
      return true;
    }
  }

  
  function get_table_array($key,$table) {
    $this->check_auth($key);

    if ($table) {
    
      preg_match("/([a-zA-Z]+)/",$table,$m);
      $table = $m[1];
    
  
      $db = new db_alloc;
      $q = sprintf("SELECT %sID, FROM %s";

    }

    return "HERE IS THE TABLE ARRAY";
  }
*/
} 

$server = new SoapServer("alloc.wsdl"); 
$server->setClass("alloc_soap"); 
$server->handle();



?>
