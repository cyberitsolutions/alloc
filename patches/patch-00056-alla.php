<?php

// Nuke additional index name_2 from config table
$db = new db_alloc();
$db->query("show keys from config");

while ($db->next_record()) {
  if ($db->f("Key_name") == "name_2") {
    $db->query("drop index name_2 on config");
  }
}

?>
