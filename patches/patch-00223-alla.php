<?php

// Nuke audit entries that aren't field changes
$db = new db_alloc();

// If need to back the change out: http://dev.mysql.com/doc/refman/5.1/en/load-data.html 
$db->query("SELECT * FROM audititem WHERE changeType != 'FieldChange' INTO OUTFILE '".ATTACHMENTS_DIR."tmp/auditItem_backup.csv'");

$db->query("DELETE FROM auditItem WHERE changeType != 'FieldChange'");

// This can happen much later
//$db->query("DELETE FROM changeType WHERE changeTypeID != 'FieldChange'");

?>
