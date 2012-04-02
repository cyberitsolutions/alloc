<?php

$extra = config::get_config_item("defaultInterestedParties");

$db = new db_alloc();
foreach ((array)$extra as $name => $email) {
  $db->query("UPDATE interestedParty set external = null WHERE emailAddress = '%s'",$email);
}

?>
