<?php
class announcement_module extends module {
  var $db_entities = array("announcement");

  function register_toolbar_items() {
    // if (have_entity_perm("announcement", PERM_READ_WRITE)) {
    // register_toolbar_item("announcementList", "Announcements");
    // }
  }

  // announcements are registered in the projects init so that they come
  // before the massive list of projects....
  // function register_home_items() {
  // 
  // include(ALLOC_MOD_DIR."/announcement/lib/announcements_home_item.inc");
  // register_home_item(new announcements_home_item());
  // }
}

include(ALLOC_MOD_DIR."/announcement/lib/announcement.inc");



?>
