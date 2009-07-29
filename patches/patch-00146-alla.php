<?php

$tsMultipliers = config::get_config_item("timeSheetMultipliers");

$db = new db_alloc();

foreach($tsMultipliers as $id => $arr) {

  // populate the timeSheetItemMultiplier table
  $q = "INSERT INTO timeSheetItemMultiplier
          (timeSheetItemMultiplierID,timeSheetItemMultiplierName,timeSheetItemMultiplierSeq,timeSheetItemMultiplierActive)
        VALUES
          (".$arr["multiplier"].", '".$arr["label"]."', ".$id.", true)";

  $db->query($q);

  // update existing time entries
  $q = "UPDATE timeSheetItem SET multiplier = ".$arr["multiplier"]." WHERE multiplier = ".$id;
  $db->query($q);
}


?>
