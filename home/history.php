<?php
require_once("alloc.inc");

global $sess;


if ($historyID) {
  if (is_numeric($historyID)) {
    $db = new db_alloc;
    $query = sprintf("select * from history where historyID = %d", $historyID);
    $db->query($query);
    $db->next_record();
    header("Location: ".$sess->url($db->f("the_place"))."&historyID=".$historyID);
  } else {
    header("Location: ".$sess->url($historyID));
  }
}









?>
