<?php
define("NO_AUTH",true);
include("alloc.inc");

$db = new db_alloc;

  // do reminders
$query = "SELECT * FROM reminder";
$db->query($query);
while ($db->next_record()) {
  $reminder = new reminder;
  $reminder->read_db_record($db);
  $reminder->mail_reminder();
  $reminder->mail_advnotice();
}

page_close();



?>
