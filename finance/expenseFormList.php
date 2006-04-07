<?php
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
