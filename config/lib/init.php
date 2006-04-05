<?php
class config_module extends module {
  var $db_entities = array("config");

  /* 
     function register_toolbar_items() { $config = new config; if ($config->have_perm(PERM_UPDATE)) { register_toolbar_item("config", "Config"); } } */
}

include(ALLOC_MOD_DIR."/config/lib/config.inc.php");



?>
