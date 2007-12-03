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

#$field_map = array("transactionDate"=>0, "employeeNum"=>1, "name"=>2, ""=>3, ""=>4, ""=>5, ""=>6, ""=>7, ""=>8, ""=>9, "amount"=>10, ""=>11, ""=>12);

$field_map = array(""                =>0
                  ,"transactionDate" =>1
                  ,"name"            =>2
                  ,"memo"            =>3
                  ,"account"         =>4
                  ,"amount"          =>5
                  ,"employeeNum"     =>7
                  );

if ($_POST["upload"] && is_uploaded_file($_FILES["wages_file"]["tmp_name"])) {
  $db = new db_alloc;

  $lines = file($_FILES["wages_file"]["tmp_name"]);

  reset($lines);
  foreach ($lines as $line) {
  
    // Read field values from the line
    $fields = explode("\t", $line);
    $transactionDate = trim($fields[$field_map["transactionDate"]]);
    $employeeNum = trim($fields[$field_map["employeeNum"]]);
    $amount = trim($fields[$field_map["amount"]]);
    $memo = trim($fields[$field_map["memo"]]);
    $account = trim($fields[$field_map["account"]]);
    $name = trim($fields[$field_map["name"]]);

    // Skip tax lines
    if (stristr($account,"Payroll Liabilities")) {
      continue;
    }
   
    // Remove leading guff "789 - " 
    $account = preg_replace("/^\d+\s.\s/","",$account);

    // If there's a memo field then append it to account
    $memo and $account.= " - ".$memo;


    #echo "<br/>";
    #echo "<br/>date: ".$transactionDate;
    #echo "<br/>memo: ".$memo;
    #echo "<br/>account: ".$account;
    #echo "<br/>amount: ".$amount;
    #echo "<br/>employeeNum: ".$employeeNum;

    // Ignore heading row, dividing lines and total rows
    if ($transactionDate == "Date" || !$transactionDate || eregi("_____", $transactionDate) || eregi("¯¯¯", $transactionDate) || eregi("total", $transactionDate)) {
      continue;
    }
    // If the employeeNum field is blank use the previous employeeNum
    #if (!$employeeNum) {
     # $employeeNum = $prev_employeeNum;
    #}
    #$prev_employeeNum = $employeeNum;

    // Find the TF for the wage
    $query = sprintf("SELECT * FROM tf WHERE qpEmployeeNum=%d", $employeeNum);
    $db->query($query);
    if (!$db->next_record()) {
      $msg.= "<b>Warning: Could not find TF for employee number '$employeeNum' $name</b><br>";
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
    if (!ereg("^[-]?[0-9]+(\\.[0-9]+)?$", $amount)) {
      $msg.= "<b>Warning: Could not convert amount '$amount'</b><br>";
      continue;
    }
    // Negate the amount - Wages are a debit from TF's
    #$amount = -$amount;

    // Check for an existing transaction for this wage - note we have to use a range or amount because it is floating point
    $query = sprintf("SELECT transactionID
                        FROM transaction
                        WHERE tfID=%d AND transactionDate='%s' AND amount>%0.3f AND amount < %0.3f", $tfID, db_esc($transactionDate), $amount - 0.001, $amount + 0.001);
    $db->query($query);
    if ($db->next_record()) {
      $msg.= "Warning: Salary for employee #$employeeNum $name on $transactionDate already exists as transaction #".$db->f("transactionID")."<br>";
      continue;
    }

    // Create a transaction object and then save it
    $transaction = new transaction;
    $transaction->set_value("tfID", $tfID);
    $transaction->set_value("transactionDate", $transactionDate);
    $transaction->set_value("amount", $amount);
    $transaction->set_value("companyDetails", "");
    $transaction->set_value("product", $account);
    $transaction->set_value("status", "approved");
    $transaction->set_value("expenseFormID", "0");
    $transaction->set_value("quantity", 1);
    $transaction->set_value("transactionType", "salary");
    $transaction->save();

    $msg.= "\$$amount for employee $employeeNum $name on $transactionDate saved<br/>";
  }
  $TPL["msg"] = $msg;
}

$TPL["main_alloc_title"] = "Upload Wages File - ".APPLICATION_NAME;
include_template("templates/wagesUploadM.tpl");

page_close();



?>
