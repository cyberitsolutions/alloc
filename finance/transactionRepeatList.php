<?php
require_once("alloc.inc");

global $tfID, $TPL, $transactionRepeat, $db;

$db = new db_alloc;
$TPL["tfID"] = $tfID;
$db->query("select * from tf where tfID=$tfID");
$db->next_record();
$TPL["user"] = $db->f("tfName");

include_template("templates/transactionRepeatListM.tpl");



function show_expenseFormList($template_name) {

  global $db, $TPL, $tfID, $john, $transactionRepeat;

  $db = new db_alloc;
  $transactionRepeat = new transactionRepeat;

  if ($tfID) {
    $db->query("select * from transactionRepeat where tfID=$tfID");
  }

  if ($john) {
    $db->query("select * from transactionRepeat where tfID=$john");
  }

  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_tpl_values();
    include_template($template_name);
  }
  $TPL["tfID"] = $tfID;
}




page_close();




?>
