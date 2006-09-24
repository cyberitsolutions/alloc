<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("../alloc.php");

global $TPL;

$db = new db_alloc;
$TPL["tfID"] = $_GET["tfID"];



include_template("templates/transactionRepeatListM.tpl");

function show_expenseFormList($template_name) {

  global $db, $TPL, $transactionRepeat, $current_user;

  $db = new db_alloc;
  $transactionRepeat = new transactionRepeat;

  if (!$_GET["tfID"] && !have_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION)) {
    $tfIDs = $current_user->get_tfIDs();
    $tfIDs and $sql = "WHERE tfID in (".implode(",",$tfIDs).")";

  } else if ($_GET["tfID"]) {
    $sql = sprintf("WHERE tfID = %d",$_GET["tfID"]);
  }

  $db->query("select * FROM transactionRepeat ".$sql);

  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";
    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_tpl_values();
    $TPL["tfName"] = get_tf_name($transactionRepeat->get_value("tfID"));
    include_template($template_name);
  }
  $TPL["tfID"] = $tfID;
}




page_close();




?>
