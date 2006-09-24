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

check_entity_perm("transaction", PERM_FINANCE_WRITE_APPROVED_TRANSACTION);

include_template("templates/expFormM.tpl");

function show_expForm($template_name) {

  global $db, $TPL;

  $db = new db_alloc;
  $transDB = new db_alloc;
  $expenseForm = new expenseForm;
  $transaction = new transaction;




  $db->query("SELECT expenseForm.*, SUM(amount) as formTotal, MIN(status) as formStatus
             FROM expenseForm 
                  LEFT JOIN transaction on expenseForm.expenseFormID = transaction.expenseFormID 
             GROUP BY expenseFormID 
             HAVING formStatus = 'pending'
             ORDER BY expenseFormID");


  while ($db->next_record()) {


    $expenseForm->read_db_record($db);
    $expenseForm->set_tpl_values();

    $TPL["formTotal"] = number_format(-$db->f("formTotal"), 2);

    include_template($template_name);

  }
}




page_close();











?>
