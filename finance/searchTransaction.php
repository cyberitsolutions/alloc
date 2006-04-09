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

global $search;


$db = new db_alloc;
$transaction = new transaction;

$db->query("SELECT * FROM tf ORDER BY tfName");
$TPL["tfOptions"] = get_option("", "0", false)."\n";
$TPL["tfOptions"].= get_options_from_db($db, "tfName", "tfID", $tfID);

$TPL["statusOptions"] = get_options_from_array(array("All", "Pending", "Rejected", "Approved",), $status, false);

$TPL["status"] = $status;
$TPL["tfID"] = $tfID;
$TPL["dateOne"] = $dateOne;
$TPL["dateTwo"] = $dateTwo;
$TPL["transactionID"] = $transactionID;
$TPL["expenseFormID"] = $expenseFormID;

include_template("templates/searchTransactionM.tpl");

     function startSearch($template) {
  global $TPL, $db, $search, $transactionID, $transaction, $status, $tfID, $dateOne, $dateTwo, $expenseFormID;

  if ($search) {
    $where.= " where 1=1";
    isset($tfID) && $tfID != 0 and $where.= " and tfID=".db_esc($tfID);
    $status != "All" and $where.= " and status=\"".db_esc($status)."\"";
    $dateOne != "" and $where.= " and transactionDate>=\"".db_esc($dateOne)."\"";
    $dateTwo != "" and $where.= " and transactionDate<=\"".db_esc($dateTwo)."\"";
    $expenseFormID != "" and $where.= " and expenseFormID=".db_esc($expenseFormID);
    $transactionID != "" and $where.= " and transactionID=".db_esc($transactionID);
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
    $TPL["amount"] = number_format(($TPL["amount"]), 2);
    include_template($template);
  }
}


page_close();




?>
