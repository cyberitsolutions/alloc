<?php

$q = prepare("SELECT * FROM person WHERE dailyTaskEmail = 'yes'");
$db = new db_alloc();
$db->query($q);

while ($r = $db->row()) {
  $person = new person();
  $person->set_id($r["personID"]);
  $person->select();
  $person->load_prefs();
  $person->prefs["dailyTaskEmail"] = 'yes';
  $person->store_prefs();
}


?>
