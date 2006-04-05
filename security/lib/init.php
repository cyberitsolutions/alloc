<?php
class security_module extends module {
  var $db_entities = array("permission");

  function register_toolbar_items() {
    // if (have_entity_perm("permission", PERM_READ_WRITE)) {
    // register_toolbar_item("permissionList", "Security");
    // }
  }
}

include(ALLOC_MOD_DIR."/security/lib/permission.inc.php");



?>
