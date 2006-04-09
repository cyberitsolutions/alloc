<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

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
