<?php
class person_module extends module {
  var $db_entities = array("person", "absence", "skillList", "skillProficiencys");

  function register_toolbar_items() {
    global $current_user, $auth;

    // Note: $current_user will not be set if we are sending email
    if (have_entity_perm("person", PERM_READ_WRITE)) {
      register_toolbar_item("personList", "Personnel");
    } else {
      register_toolbar_item("person", "Personal", "personID=".$auth->auth["uid"]);
    }

  }
}

include(ALLOC_MOD_DIR."/person/lib/person.inc.php");
include(ALLOC_MOD_DIR."/person/lib/absence.inc.php");
include(ALLOC_MOD_DIR."/person/lib/skillList.inc.php");
include(ALLOC_MOD_DIR."/person/lib/skillProficiencys.inc.php");




?>
