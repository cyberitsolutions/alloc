<?php

// Update old entries in the interested parties table that don't have names
$db = new db_alloc();
$db2 = new db_alloc();

$extra_interested_parties = config::get_config_item("defaultInterestedParties");
foreach ((array)$extra_interested_parties as $name => $email) {

  $q = prepare("UPDATE interestedParty SET fullName = '%s' WHERE emailAddress = '%s' AND (fullName is NULL or fullName = '')"
              ,db_esc($name),db_esc($email));

  $db2->query($q);
}

?>
