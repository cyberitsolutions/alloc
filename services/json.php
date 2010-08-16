<?php

// enable error reporting
define("NO_AUTH",1); 
require_once("../alloc.php");

function g($var) {
  $rtn = urldecode($_GET[$var]) or $rtn = $_POST[$var] or $rtn = $_REQUEST[$var];
  return $rtn;
}

$a = new alloc_services();

if (g("username") && g("password")) {
  $key = $a->authenticate(g("username"), g("password"));
  $sess = alloc_json_encode(array("key"=>$key));
  echo $sess;
} 

$key or $key = g("key");


if ($key) {
  if (method_exists($a,g("method"))) {

    $modelReflector = new ReflectionClass('alloc_services');
    $method = $modelReflector->getMethod(g("method"));
    $parameters = $method->getParameters();

    foreach ($parameters as $v) {
      $a[] = g($v->name);
    }

    $method = g("method");

    // Ouch
    $n = count($parameters);
    if ($n == 9) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8]));
    } else if ($n == 8) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7]));
    } else if ($n == 7) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6]));
    } else if ($n == 6) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3],$a[4],$a[5]));
    } else if ($n == 5) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3],$a[4]));
    } else if ($n == 4) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2],$a[3]));
    } else if ($n == 3) {
      return alloc_json_encode($a->$method($a[0],$a[1],$a[2]));
    } else if ($n == 2) {
      return alloc_json_encode($a->$method($a[0],$a[1]));
    } else if ($n == 1) {
      return alloc_json_encode($a->$method($a[0]));
    } else if ($n == 0) {
      return alloc_json_encode($a->$method());
    }
  }
}

?>
