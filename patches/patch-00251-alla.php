<?php

// This Cyber-only patch permits the unruly client-can-reopen-task functionality.
if (config::for_cyber()) {
  $db = new db_alloc();
  $db->query("INSERT INTO permission (tableName,entityID,roleName,actions,comment) values ('task',0,'','2','permit clients to re-open tasks, CYBER-ONLY.')");
} else {
  $db = new db_alloc();
  // Else need to permit client all to read tasks
  $db->query("update permission set roleName = '' where tableName = 'task' and roleName = 'employee' and actions = 1;");
}

?>
