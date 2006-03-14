<?php
class home_module extends module {
  var $db_entities = array("history");
  function register_toolbar_items() {
    register_toolbar_item("home", "Home");
  }

  function register_home_items() {
    global $MOD_DIR, $modules;

    if (isset($modules["finance"]) && $modules["finance"]) {
      include("$MOD_DIR/home/lib/tfList_home_item.inc");
      register_home_item(new tfList_home_item);
    } else {
      include("$MOD_DIR/home/lib/date_home_item.inc");
      register_home_item(new date_home_item);
    }

    include("$MOD_DIR/home/lib/customize_alloc_home_item.inc");
    register_home_item(new customize_alloc_home_item);


    // include("$MOD_DIR/home/lib/quick_links_home_item.inc");
    // register_home_item(new quick_links_home_item);
    // include("$MOD_DIR/home/lib/history_home_item.inc");
    // register_home_item(new history_home_item);
  }
}




?>
