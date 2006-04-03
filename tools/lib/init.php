<?php
class tools_module extends module {

  function register_toolbar_items() {

    if (1) {                    // FIX ME
      register_toolbar_item("tools", "Tools");
    }
  }
}

include(ALLOC_MOD_DIR."/tools/lib/stats.inc");




?>
