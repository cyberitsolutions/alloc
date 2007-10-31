<?php 

define("NO_AUTH",true);
require_once("../alloc.php");
require_once("class_alloc_soap.inc.php");


// NOTE THAT RUNNING THIS SCRIPT FROM THE WEB BROWSER WILL NUKE ANY ACTIVE ALLOC WEB BROWSER SESSIONS

$username = "alloc";
$password = "alloc";

$taskID = 11041;
$duration = 5.23;
$comments = "hey commedsahdjkshajdkants!";

$alloc = new alloc_soap();
$key = $alloc->authenticate($username,$password);
echo "<br>1: ".$key;
echo "<br>2: ".print_r($alloc->add_timeSheetItem_by_task($key, $taskID, $duration, $comments),1);




?>
