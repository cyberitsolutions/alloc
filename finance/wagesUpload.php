<?php
include("alloc.inc");

$field_map = array("transactionDate"=>0, "employeeNum"=>1, "name"=>2, ""=>3, ""=>4, ""=>5, ""=>6, ""=>7, ""=>8, ""=>9, "amount"=>10, ""=>11, ""=>12);

if ($upload && is_uploaded_file($wages_file)) {
  $db = new db_alloc;

  $lines = file($wages_file);

  reset($lines);
  while (list(, $line) = each($lines)) {
    // Read field values from the line
    $fields = explode("\t", $line);
    $transactionDate = trim($fields[$field_map["transactionDate"]]);
    $employeeNum = trim($fields[$field_map["employeeNum"]]);
    $amount = trim($fields[$field_map["amount"]]);

    // Ignore heading row, dividing lines and total rows
    if ($transactionDate == "Date Paid" || $transactionDate == "" || eregi("_____", $transactionDate) || eregi("¯¯¯", $transactionDate) || eregi("total", $transactionDate)) {
      continue;
    }
    // If the employeeNum field is blank use the previous employeeNum
    if (!$employeeNum) {
      $employeeNum = $prev_employeeNum;
    }
    $prev_employeeNum = $employeeNum;

    // Find the TF for the wage
    $query = sprintf("SELECT * FROM tf WHERE qpEmployeeNum=%d", $employeeNum);
    $db->query($query);
    if (!$db->next_record()) {
      $msg.= "<b>Warning: Could not find TF for employee number '$employeeNum'</b><br>";
      continue;
    }
    $tfID = $db->f("tfID");

    // Convert the date to yyyy-mm-dd
    if (!eregi("^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})$", $transactionDate, $matches)) {
      $msg.= "<b>Warning: Could not convert date '$transactionDate'</b><br>";
      continue;
    }
    $transactionDate = sprintf("%04d-%02d-%02d", $matches[3], $matches[2], $matches[1]);


    // Strip $ and , from amount
    $amount = ereg_replace("[\$,]", "", $amount);
    if (!ereg("^[0-9]+(\\.[0-9]+)?$", $amount)) {
      $msg.= "<b>Warning: Could not convert amount '$amount'</b><br>";
      continue;
    }
    // Negate the amount - Wages are a debit from TF's
    $amount = -$amount;

    // Check for an existing transaction for this wage - note we have to use a range or amount because it is floating point
    $query = sprintf("SELECT transactionID
                        FROM transaction
                        WHERE tfID=%d AND transactionDate='%s' AND amount>%0.3f AND amount < %0.3f", $tfID, addslashes($transactionDate), $amount - 0.001, $amount + 0.001);
    $db->query($query);
    if ($db->next_record()) {
      $msg.= "Warning: Salary for employee #$employeeNum on $transactionDate already exists as transaction #".$db->f("transactionID")."<br>";
      continue;
    }
    // Create a transaction object and then save it
    $transaction = new transaction;
    $transaction->set_value("tfID", $tfID);
    $transaction->set_value("transactionDate", $transactionDate);
    $transaction->set_value("amount", $amount);
    $transaction->set_value("companyDetails", "Cybersource");
    $transaction->set_value("product", "Wages");
    $transaction->set_value("status", "approved");
    $transaction->set_value("expenseFormID", "0");
    $transaction->set_value("quantity", 1);
    $transaction->set_value("transactionType", "salary");
    $transaction->save();

    $msg.= "\$$amount for employee $employeeNum on $transactionDate saved<br>";
  }
  $TPL["msg"] = $msg;
}
include_template("templates/wagesUploadM.tpl");

page_close();



?>
