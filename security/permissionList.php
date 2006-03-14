<?php
include("alloc.inc");

check_entity_perm("permission", PERM_READ_WRITE);

function show_permission_list($template_name) {
  global $TPL, $submit, $filter;

  if ($submit || $filter != "") {
    $where = " where tableName like '".$filter."%' ";   // TODO: Add filtering to permission list
  }
  $db = new db_alloc;
  $db->query("SELECT * FROM permission $where ORDER BY tableName, sortKey");
  while ($db->next_record()) {
    $permission = new permission;
    $permission->read_db_record($db);
    $permission->set_tpl_values(DST_HTML_ATTRIBUTE);
    $TPL["actions"] = $permission->describe_actions();
    if ($permission->get_value("personID")) {
      $person = $permission->get_foreign_object("person");
      $TPL["username"] = $person->get_value("username");
    } else {
      $TPL["username"] = "(all)";
    }
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    include_template($template_name);
  }
}

include_template("templates/permissionListM.tpl");

page_close();



?>
