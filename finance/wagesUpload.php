<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

if (!config::get_config_item("outTfID")) {
  alloc_error("Please select a default Outgoing TF from the Setup -> Finance menu.");
}

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
  $db = new db_alloc();

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
    // The dash isn't an ASCII dash, hence non-greedy anything match
    $account = preg_replace("/^\d+\s.*?\s/","",$account);

    // If there's a memo field then append it to account
    $memo and $account.= " - ".$memo;


    #echo "<br>";
    #echo "<br>date: ".$transactionDate;
    #echo "<br>memo: ".$memo;
    #echo "<br>account: ".$account;
    #echo "<br>amount: ".$amount;
    #echo "<br>employeeNum: ".$employeeNum;

    // Ignore heading row, dividing lines and total rows
    if ($transactionDate == "Date" || !$transactionDate || strpos("_____", $transactionDate) !== FALSE || strpos("¯¯¯", $transactionDate) !== FALSE || stripos("total", $transactionDate) !== FALSE) {
      continue;
    }
    // If the employeeNum field is blank use the previous employeeNum
    #if (!$employeeNum) {
     # $employeeNum = $prev_employeeNum;
    #}
    #$prev_employeeNum = $employeeNum;

    // Find the TF for the wage
    $query = prepare("SELECT * FROM tf WHERE qpEmployeeNum=%d", $employeeNum);
    $db->query($query);
    if (!$db->next_record()) {
      $msg.= "<b>Warning: Could not find TF for employee number '$employeeNum' $name</b><br>";
      continue;
    }
    $fromTfID = $db->f("tfID");

    // Convert the date to yyyy-mm-dd
    if (!preg_match("|^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})$|i", $transactionDate, $matches)) {
      $msg.= "<b>Warning: Could not convert date '$transactionDate'</b><br>";
      continue;
    }
    $transactionDate = sprintf("%04d-%02d-%02d", $matches[3], $matches[2], $matches[1]);


    // Strip $ and , from amount
    $amount = str_replace(array('$',','), array(), $amount);
    if (!preg_match("/^[-]?[0-9]+(\\.[0-9]+)?$/", $amount)) {
      $msg.= "<b>Warning: Could not convert amount '$amount'</b><br>";
      continue;
    }

    // Negate the amount - Wages are a debit from TF's
    $amount = -$amount;

    // Check for an existing transaction for this wage - note we have to use a range or amount because it is floating point
    $query = prepare("SELECT transactionID
                        FROM transaction
                        WHERE fromTfID=%d AND transactionDate='%s' AND amount=%d", $fromTfID, $transactionDate, page::money(config::get_config_item("currency"),$amount,"%mi"));
    $db->query($query);
    if ($db->next_record()) {
      $msg.= "Warning: Salary for employee #$employeeNum $name on $transactionDate already exists as transaction #".$db->f("transactionID")."<br>";
      continue;
    }

    // Create a transaction object and then save it
    $transaction = new transaction();
    $transaction->set_value("currencyTypeID",config::get_config_item("currency"));
    $transaction->set_value("fromTfID", $fromTfID);
    $transaction->set_value("tfID", config::get_config_item("outTfID"));
    $transaction->set_value("transactionDate", $transactionDate);
    $transaction->set_value("amount", $amount);
    $transaction->set_value("companyDetails", "");
    $transaction->set_value("product", $account);
    $transaction->set_value("status", "approved");
    $transaction->set_value("quantity", 1);
    $transaction->set_value("transactionType", "salary");
    $transaction->save();

    $msg.= "\$$amount for employee $employeeNum $name on $transactionDate saved<br>";
  }
  $TPL["msg"] = $msg;
}

$TPL["main_alloc_title"] = "Upload Wages File - ".APPLICATION_NAME;
include_template("templates/wagesUploadM.tpl");


?>
