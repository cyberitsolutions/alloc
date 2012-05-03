<?php
// Apply db_triggers.sql to the database.
// 
// YOU MAY NEED TO MANUALLY APPLY THIS FILE TO YOUR DATABASE AS ADMINISTRATOR/ROOT. IF THIS IS THE CASE, YOU COULD TRY INPUTTING THOSE CREDENTIALS BELOW, AND THEN APPLYING THIS PATCH.
// 
// user: <input type="text" size="10" name="root_db_user">
// pass: <input type="text" size="10" name="root_db_pass">
// host: <input type="text" size="10" name="root_db_host">
// database: <input type="text" size="10" name="root_db_name">

$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."installation".DIRECTORY_SEPARATOR."db_triggers.sql";

$user = $_REQUEST["root_db_user"] or $user = ALLOC_DB_USER;
$pass = $_REQUEST["root_db_pass"] or $pass = ALLOC_DB_PASS;
$host = $_REQUEST["root_db_host"] or $host = ALLOC_DB_HOST;
$name = $_REQUEST["root_db_name"] or $name = ALLOC_DB_NAME;

$command = sprintf("mysql -u%s -p%s -h%s -D%s < %s 2>&1"
                  ,escapeshellarg($user)
                  ,escapeshellarg($pass)
                  ,escapeshellarg($host)
                  ,escapeshellarg($name)
                  ,escapeshellarg($file));

$e = shell_exec($command);

if ($e) {
  echo "Error applying ".$file.":<br><br><b>".$e."</b>";
  echo "<br><br>This is the command that was attempted:<br><br>".$command;
}



?>
