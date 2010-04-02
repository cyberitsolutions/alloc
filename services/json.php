<?php

// enable error reporting
define("NO_AUTH",1); 
require_once("../alloc.php");

$json = urldecode($_GET["json"]) or $json = $_POST["json"] or $json = $_REQUEST["json"];

$o = alloc_json_decode($json);
$a = new alloc_services();

if ($o["username"] && $o["password"]) {
  $sess = $a->authenticate($o["username"], $o["password"]);
  $o["sess"] = alloc_json_encode(array("key"=>$sess));
} 

if ($o["sess"]) {
  if (method_exists($a,$o["method"])) {

    $modelReflector = new ReflectionClass('alloc_services');
    $method = $modelReflector->getMethod($o["method"]);
    $parameters = $method->getParameters();

    foreach ($parameters as $v) {
      $a[] = $o[$v->name];
    }

    // Ouch
    $n = count($parameters);
    if ($n == 9) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],$a[8]));
    } else if ($n == 8) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6],$a[7]));
    } else if ($n == 7) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6]));
    } else if ($n == 6) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3],$a[4],$a[5]));
    } else if ($n == 5) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3],$a[4]));
    } else if ($n == 4) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2],$a[3]));
    } else if ($n == 3) {
      return alloc_json_encode($a->$o($a[0],$a[1],$a[2]));
    } else if ($n == 2) {
      return alloc_json_encode($a->$o($a[0],$a[1]));
    } else if ($n == 1) {
      return alloc_json_encode($a->$o($a[0]));
    } else if ($n == 0) {
      return alloc_json_encode($a->$o());
    }
  }
}

?>
