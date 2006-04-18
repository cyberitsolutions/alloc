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

require_once("alloc.inc");

include_template("templates/expenseFormListM.tpl");

function show_expense_form_list($template_name) {

  global $db, $dbTwo, $TPL;

  $db = new db_alloc;
  $dbTwo = new db_alloc;
  $transDB = new db_alloc;
  $expenseForm = new expenseForm;
  $transaction = new transaction;

  $db->query("SELECT expenseForm.*, SUM(transaction.amount) as formTotal
                FROM expenseForm, transaction
                WHERE expenseForm.expenseFormID = transaction.expenseFormID
                AND transaction.status = 'pending'
                GROUP BY expenseForm.expenseFormID
                ORDER BY expenseFormID");


  while ($db->next_record()) {
    if ($expenseForm->read_db_record($db, false)) {
      $i++;
      $TPL["row_class"] = "odd";
      $i % 2 == 0 and $TPL["row_class"] = "even";

      $expenseForm->set_tpl_values();
      $TPL["formTotal"] = number_format(-$db->f("formTotal"), 2);
      $dbTwo->query("select username from person where personID=".$expenseForm->get_value("expenseFormModifiedUser"));
      $dbTwo->next_record();
      $TPL["expenseFormModifiedUser"] = $dbTwo->f("username");
      $TPL["lastModified"] = get_mysql_date_stamp($expenseForm->get_value("lastModified"));
      include_template($template_name);
    }
  }

}

page_close();



?>
