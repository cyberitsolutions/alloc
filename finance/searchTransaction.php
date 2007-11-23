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

  function startSearch($template) {
    global $TPL, $db, $transaction, $current_user;

    if ($_POST["search"]) {
      $where.= " where 1=1";
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

    while ($db->next_record()) {
      $i++;
      $TPL["row_class"] = "odd";
      $i % 2 == 0 and $TPL["row_class"] = "even";

      $transaction->read_db_record($db);
      $transaction->set_tpl_values();
      $tf = $transaction->get_foreign_object("tf");
      $tf->set_tpl_values();
      $TPL["amount"] = sprintf("%0.2f",$TPL["amount"]);
      include_template($template);
    }
  }


$db = new db_alloc;
$transaction = new transaction;

$db->query("SELECT * FROM tf ORDER BY tfName");
$TPL["tfOptions"] = get_option("", "0", false)."\n";
$TPL["tfOptions"].= get_options_from_db($db, "tfName", "tfID", $_POST["tfID"]);

$TPL["statusOptions"] = get_options_from_array(array("All", "Pending", "Rejected", "Approved",), $_POST["status"], false);

$TPL["status"] = $_POST["status"];
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
