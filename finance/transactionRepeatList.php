<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

global $TPL;

$db = new db_alloc;
$TPL["tfID"] = $_GET["tfID"];


$TPL["main_alloc_title"] = "Repeating Expenses List - ".APPLICATION_NAME;
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
    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_values();
    $TPL["tfName"] = tf::get_name($transactionRepeat->get_value("tfID"));
    $TPL["fromTfName"] = tf::get_name($transactionRepeat->get_value("fromTfID"));
    include_template($template_name);
  }
  $TPL["tfID"] = $tfID;
}


?>
