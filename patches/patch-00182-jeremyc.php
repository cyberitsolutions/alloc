<?php
// Insert a config item for timezone based on system timezone.
$timezone = @date_default_timezone_get();
$query = sprintf("INSERT INTO config (name, value, type) VALUES ('allocTimezone', '%s', 'text')", db_esc($timezone));
$db = new db_alloc();
$db->query($query);

?>
