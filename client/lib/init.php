<?php
class client_module extends module {
  var $db_entities = array("client", "comment", "clientContact");

  function register_toolbar_items() {
    if (have_entity_perm("client", PERM_READ)) {
      register_toolbar_item("clientList", "Clients");
    }
  }
}

include("$MOD_DIR/client/lib/client.inc");
include("$MOD_DIR/client/lib/clientContact.inc");
include("$MOD_DIR/client/lib/comment.inc");



?>
