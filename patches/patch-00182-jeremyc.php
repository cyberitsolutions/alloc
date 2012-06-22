<?php
// Insert a config item for timezone based on system timezone.
$timezone = @date_default_timezone_get();
$db = new db_alloc();

// Nuke any old timezone entries from earlier attempts to add a time zone
$db->query("DELETE FROM config WHERE name = 'allocTimezone'");

// Put a new entry in for timezone using the default for the server eg: Australia/Melbourne
$query = prepare("INSERT INTO config (name, value, type) VALUES ('allocTimezone', '%s', 'text')", $timezone);
$db->query($query);

?>
