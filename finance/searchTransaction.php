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

  function startSearch($template) {
    global $TPL, $db, $transaction, $current_user;

    if ($_POST["search"]) {
      $where.= " where 1=1";
      $_POST["fromTfID"] && $_POST["fromTfID"] != 0 and $where.= sprintf(" AND fromTfID=%d",db_esc($_POST["fromTfID"]));
      $_POST["tfID"] && $_POST["tfID"] != 0 and $where.= sprintf(" AND tfID=%d",db_esc($_POST["tfID"]));
      $_POST["status"] != "All"             and $where.= sprintf(" AND status=\"%s\"",db_esc($_POST["status"]));
      $_POST["dateOne"] != ""               and $where.= sprintf(" AND transactionDate>=\"%s\"",db_esc($_POST["dateOne"]));
      $_POST["dateTwo"] != ""               and $where.= sprintf(" AND transactionDate<=\"%s\"",db_esc($_POST["dateTwo"]));
      $_POST["expenseFormID"] != ""         and $where.= sprintf(" AND expenseFormID=%d",db_esc($_POST["expenseFormID"]));
      $_POST["transactionID"] != ""         and $where.= sprintf(" AND transactionID=%d",db_esc($_POST["transactionID"]));
      $_POST["product"]                     and $where.= sprintf(" AND product like \"%%%s%%\"",db_esc($_POST["product"]));

      if (!$current_user->have_role("god") || !$current_user->have_role("admin")) {
        $tfIDs = $current_user->get_tfIDs();
        if (is_array($tfIDs)) {
          $where.= " AND transaction.tfID in (".implode(",",$tfIDs).")";
        } 
      }
      
      $query = "select * from transaction ".$where;
      $db->query($query);
    }

    $transactionTypes = transaction::get_transactionTypes();
    while ($db->next_record()) {
      $i++;
      $transaction->read_db_record($db);
      $transaction->set_tpl_values();
      $tf = new tf;
      $tf->set_id($transaction->get_value("tfID"));
      $tf->select();
      $TPL["tfName"] = $tf->get_link();

      $tf = new tf;
      $tf->set_id($transaction->get_value("fromTfID"));
      $tf->select();
      $TPL["fromTfName"] = $tf->get_link();

      $TPL["amount"] = sprintf("%0.2f",$TPL["amount"]);   
      $TPL["transactionType"] = $transactionTypes[$transaction->get_value("transactionType")];
      include_template($template);
    }
  }


$db = new db_alloc;
$transaction = new transaction;

$q = "SELECT tfID AS value, tfName AS label FROM tf WHERE status != 'disabled' ORDER BY tfName";
$TPL["tfOptions"] = get_select_options($q, $_POST["tfID"]);
$TPL["fromTfOptions"] = get_select_options($q, $_POST["fromTfID"]);
$TPL["statusOptions"] = get_select_options(array(""=>"", "pending"=>"Pending", "rejected"=>"Rejected", "approved"=>"Approved",), $_POST["status"]);
$TPL["status"] = $_POST["status"];
$TPL["fromTfID"] = $_POST["fromTfID"];
$TPL["tfID"] = $_POST["tfID"];
$TPL["dateOne"] = $_POST["dateOne"];
$TPL["dateTwo"] = $_POST["dateTwo"];
$TPL["transactionID"] = $_POST["transactionID"];
$TPL["expenseFormID"] = $_POST["expenseFormID"];
$TPL["product"] = $_POST["product"];

$TPL["main_alloc_title"] = "Search Transactions - ".APPLICATION_NAME;
include_template("templates/searchTransactionM.tpl");


page_close();
?>
