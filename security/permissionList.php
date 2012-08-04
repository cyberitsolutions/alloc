<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

require_once("../alloc.php");

function show_permission_list($template_name) {
  global $TPL;

  $roles = permission::get_roles();

  if ($_REQUEST["submit"] || $_REQUEST["filter"] != "") {
    $where = " where tableName like '%".db_esc($_REQUEST["filter"])."%' ";   // TODO: Add filtering to permission list
  }
  $db = new db_alloc();
  $db->query("SELECT * FROM permission $where ORDER BY tableName, sortKey");
  while ($db->next_record()) {
    $permission = new permission();
    $permission->read_db_record($db);
    $permission->set_values();
    $TPL["actions"] = $permission->describe_actions();
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    $TPL["roleName"] = $roles[$TPL["roleName"]];
    include_template($template_name);
  }
}

$TPL["main_alloc_title"] = "Permissions List - ".APPLICATION_NAME;

include_template("templates/permissionListM.tpl");

?>
