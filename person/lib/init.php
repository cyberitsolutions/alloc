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

include("$MOD_DIR/person/lib/person.inc");
include("$MOD_DIR/person/lib/absence.inc");
include("$MOD_DIR/person/lib/skillList.inc");
include("$MOD_DIR/person/lib/skillProficiencys.inc");




?>
