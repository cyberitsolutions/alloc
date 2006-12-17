<?php

define("NO_AUTH",1);
require_once("../alloc.php");

$q = "SELECT timeSheetID from transaction WHERE status = 'approved' group by timeSheetID";
$db = new db_alloc;
$db->query($q);

while ($row = $db->row()) {
  $row["timeSheetID"] and $timeSheetIDs[] = $row["timeSheetID"];
}

if (is_array($timeSheetIDs)) {
  $q = "UPDATE timeSheet SET status = 'paid' WHERE timeSheetID in (".implode(",",$timeSheetIDs).")";
  $db->query($q);
}


?>
