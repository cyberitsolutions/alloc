<?php


include("alloc.inc");


$f = array(
"ActivityID" => 0,
"taskName" => 1,
"taskDescription" => 2,
"Use Desc. On Sale" => 3,
"Non-Hourly" => 4,
"Non-Chargeable" => 5,
"Use Rate" => 6,
"Activity Rate" => 7,
"Income Account" => 8,
"Tax Code When Sold" => 9,
"Unit of Measure" => 10,
"Inactive Activity" => 11,
);

$db = new db_alloc;

$file = file("./ACTIVITY.csv");

/*
$projectID = 596;
$creatorID = 60;
*/

$projectID = 620;
$creatorID = 68;
$dateCreated = date("Y-m-d");
$taskTypeID = 1;



foreach ($file as $line) {

  $b = explode(",",$line);

  $taskName = $b[$f["taskName"]];
  $taskDescription = $b[$f["taskDescription"]];
  $b[$f["ActivityID"]]         and $taskDescription.= "\nActivityID: ".$b[$f["ActivityID"]];
  $b[$f["Use Desc. On Sale"]]  and $taskDescription.= "\nUse Desc. On Sale: ".$b[$f["Use Desc. On SaleActivityID"]];
  $b[$f["Non-Hourly"]]         and $taskDescription.= "\nNon-Hourly: ".$b[$f["Non-Hourly"]];
  $b[$f["Non-Chargeable"]]     and $taskDescription.= "\nNon-Chargeable: ".$b[$f["Non-Chargeable"]];
  $b[$f["Use Rate"]]           and $taskDescription.= "\nUse Rate: ".$b[$f["Use Rate"]];
  $b[$f["Activity Rate"]]      and $taskDescription.= "\nActivity Rate: ".$b[$f["Activity Rate"]];
  $b[$f["Income Account"]]     and $taskDescription.= "\nIncome Account".$b[$f["Income Account"]];
  $b[$f["Tax Code When Sold"]] and $taskDescription.= "\nTax Code When Sold: ".$b[$f["Tax Code When Sold"]];
  $b[$f["Unit of Measure"]]    and $taskDescription.= "\nUnit of Measure: ".$b[$f["Unit of Measure"]];
  $b[$f["Inactive Activity"]]  and $taskDescription.= "\nInactive Activity: ".$b[$f["Inactive Activity"]];

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
