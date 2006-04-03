<?php
class item_module extends module {
  var $db_entities = array("item", "loan");

  function register_toolbar_items() {
    global $current_user;

    if (isset($current_user) && $current_user->is_employee()) {
      register_toolbar_item("loans", "Item Loans");
    }
  }
}

include(ALLOC_MOD_DIR."/item/lib/item.inc");
include(ALLOC_MOD_DIR."/item/lib/loan.inc");




?>
