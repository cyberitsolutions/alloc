<?php
require_once("alloc.inc");

check_entity_perm("announcement", PERM_READ_WRITE);

function show_announcements($template_name) {
  global $TPL;

  $query = "SELECT announcement.*, person.username 
              FROM announcement LEFT JOIN person ON announcement.personID = person.personID
              ORDER BY displayFromDate";
  $db = new db_alloc();
  $db->query($query);
  while ($db->next_record()) {
    $announcement = new announcement;
    $announcement->read_db_record($db);
    $announcement->set_tpl_values();
    $TPL["personName"] = $db->f("username");
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    include_template($template_name);
  }
}

include_template("templates/announcementListM.tpl");

page_close();



?>
