<?php
require_once("alloc.inc");

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
