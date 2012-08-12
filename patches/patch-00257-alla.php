<?php

// Cyber-only patch, revoking inbox privileges
if (config::for_cyber()) {
  $db = new db_alloc();
  $db->query("UPDATE permission SET actions = 1 WHERE tableName = 'inbox' AND roleName = 'manage'");
  $db->query("UPDATE permission SET actions = 1 WHERE tableName = 'inbox' AND roleName = 'admin'");
}

?>
