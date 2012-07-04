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

if (!have_entity_perm("config", PERM_UPDATE, $current_user, true)) {
  alloc_error("Permission denied.",true);
}

$table = $_POST["configName"] or $table = $_GET["configName"];
$TPL["table"] = $table;


if ($_POST["save"]) {

  foreach ((array)$_POST[$table."ID"] as $k => $tableID) {
    // Delete
    if (in_array($tableID, (array)$_POST["delete"])) {
      $t = new meta($table);
      $t->set_id($tableID);
      $t->delete();

    // Save
    } else {

      $a = array($table."ID"     => $tableID
                ,$table."Seq"    => $_POST[$table."Seq"][$k]
                ,$table."Label"  => $_POST[$table."Label"][$k]
                ,$table."Name"   => $_POST[$table."Name"][$k]
                ,$table."Colour" => $_POST[$table."Colour"][$k]
                ,$table."Seq"    => $_POST[$table."Seq"][$k]
                ,"numberToBasic" => $_POST["numberToBasic"][$k] // currencyType field
                ,$table."Active" => in_array($tableID, $_POST[$table."Active"])
                );

      $orig_tableID = $_POST[$table."IDOrig"][$k];
      $t = new meta($table);
      $t->read_array($a);
      $errs = $t->validate();
      if (!$errs) {
        if ($orig_tableID && $orig_tableID != $tableID) {
          $a[$table."Active"] = in_array($orig_tableID, $_POST[$table."Active"]);
          $t->read_array($a);
          $t->set_id($orig_tableID);
          $k = new db_field($table."ID"); // If the primary key has changed, then it needs special handling.
          $k->set_value($tableID);        // The primary keys in the referential integrity tables are not 
          $t->data_fields[] = $k;         // usually just auto-incrementing IDs like every other table in alloc
          $t->update();                   // So we have to trick db_entity into letting us update a primary key.
        } else {
          $t->save();
        }
      }
    }
  }

}

include_template("templates/metaEdit.tpl");




?>
