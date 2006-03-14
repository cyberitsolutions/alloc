<?php


include("alloc.inc");


$f = array(
"blah" => 0,
"taskName" => 1,
"taskDescription" => 2,
"wtf1" => 3,
"wtf2" => 4,
"wtf3" => 5,
"wtf4_rate" => 6,
"wtf8 Activity Rate" => 7,
"wtf9 income account" => 8,
"wtf10 tax code" => 9,
"wtf11 unit of measure" => 10,
);

$db = new db_alloc;

$file = file("./ACTIVITY.csv");

/*
$projectID = 620;
$creatorID = 68;
*/

$projectID = 596;
$creatorID = 60;
$dateCreated = date("Y-m-d");
$taskTypeID = 1;



foreach ($file as $line) {

  $b = explode(",",$line);

  $taskName = $b[$f["taskName"]];
  $taskDescription = $b[$f["taskDescription"]];

  $q = "INSERT INTO task (taskName, taskDescription,projectID,creatorID,dateCreated,taskTypeID) VALUES ";
  $q.= sprintf("('%s','%s','%s','%s','%s','%s')
               ",addslashes(str_replace("\"","",$taskName))
                ,addslashes(str_replace("\"","",$taskDescription))
                ,addslashes(str_replace("\"","",$projectID))
                ,addslashes(str_replace("\"","",$creatorID))
                ,addslashes(str_replace("\"","",$dateCreated))
                ,addslashes(str_replace("\"","",$taskTypeID))
                );

  $db->query($q);
  echo "<br><br>".$q;

}








?>
