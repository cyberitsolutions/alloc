<?php
class announcements_home_item extends home_item {
  function announcements_home_item() {
    home_item::home_item("announcements", "Announcements", "announcement", "announcementsH.tpl", "standard", "2");
  }

  function show_announcements($template_name) {
    global $current_user, $TPL;

    $query = "SELECT announcement.*, person.username
             FROM announcement LEFT JOIN person ON announcement.personID = person.personID
             WHERE displayFromDate <= CURDATE() AND displayToDate >= CURDATE()
             ORDER BY displayFromDate desc";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $announcement = new announcement;
      $announcement->read_db_record($db);
      $announcement->set_tpl_values();
      $TPL["personName"] = $db->f("username");
      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
