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

define("PERM_TIME_APPROVE_TIMESHEETS", 256);
define("PERM_TIME_INVOICE_TIMESHEETS", 512);

class timeSheet extends db_entity
{
  var $classname = "timeSheet";
  var $data_table = "timeSheet";
  var $display_field_name = "projectID";

  function timeSheet() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("timeSheetID");
    $this->data_fields = array("projectID"=>new db_field("projectID")
                               , "dateFrom"=>new db_field("dateFrom")
                               , "dateTo"=>new db_field("dateTo")
                               , "status"=>new db_field("status")
                               , "personID"=>new db_field("personID")
                               , "approvedByManagerPersonID"=>new db_field("approvedByManagerPersonID")
                               , "approvedByAdminPersonID"=>new db_field("approvedByAdminPersonID")
                               , "invoiceNum"=>new db_field("invoiceNum")
                               , "invoiceItemID"=>new db_field("invoiceItemID")
                               , "dateSubmittedToManager"=>new db_field("dateSubmittedToManager")
                               , "dateSubmittedToAdmin"=>new db_field("dateSubmittedToAdmin")
                               , "billingNote"=>new db_field("billingNote")
                               , "payment_insurance"=>new db_field("payment_insurance")
                               , "recipient_tfID"=>new db_field("recipient_tfID")
                               , "customerBilledDollars"=>new db_field("customerBilledDollars")
      );
    $this->permissions[PERM_TIME_APPROVE_TIMESHEETS] = "Approve";
    $this->permissions[PERM_TIME_INVOICE_TIMESHEETS] = "Invoice";
  }

  function save() {
    global $current_user;
  
    if (!$this->get_value("personID")) {
      $this->set_value("personID", $current_user->get_id());
    }

    $status = parent::save();
    return $status;
  }

  function is_owner() {
    global $current_user;
    if ($this->get_value("personID") == $current_user->get_id()) {
      return true;
    } else {

      // This allows people with transactions on this time sheet who may not
      // actually be this time sheets owner to view this time sheet.
      if ($this->get_value("status") != "edit") { 
        $current_user_tfIDs = $current_user->get_tfIDs();
        $q = sprintf("SELECT * FROM transaction WHERE timeSheetID = %d",$this->get_id());
        $db = new db_alloc();
        $db->query($q);
        while ($db->next_record()) {
          if (in_array($db->f("tfID"),$current_user_tfIDs)) {
            return true;
          }
        }
      }
    }
  }

  function select() {
    return parent::select(false);
  }

  function get_timeSheet_statii() {
    return array("create"    => "Create"
                ,"edit"      => "Add Time"
                ,"manager"   => "Project Manager"
                ,"admin"     => "Administrator"
                ,"invoiced"  => "Invoiced"
                ,"finished"  => "Completed"
                );
  } 

  function delete() {
    $db = new db_alloc;
    $db->query("DELETE FROM timeSheetItem where timeSheetID = ".$this->get_id());  
    db_entity::delete();
  }

  function load_pay_info() {

    /***************************************************************************
     *                                                                         *
     * load_pay_info() loads these vars:                                       *
     * $this->pay_info["project_rate"];	    according to projectPerson table   *
     * $this->pay_info["timeSheetItem_rate"];according to timeSheetItem table  *
     * $this->pay_info["customerBilledDollars"];                               *
     * $this->pay_info["project_rateUnitID"];	according to projectPerson table *
     * $this->pay_info["duration"][time sheet ITEM ID];                        *
     * $this->pay_info["total_duration"]; of a timesheet                       *
     * $this->pay_info["total_dollars"];  of a timesheet                       *
     * $this->pay_info["total_customerBilledDollars"]                          *
     * $this->pay_info["total_dollars_minus_gst"]                              *
     * $this->pay_info["total_customerBilledDollars_minus_gst"]                *
     * $this->pay_info["unit"]                                                 *
     * $this->pay_info["summary_unit_totals"]                                  *
     *                                                                         *
     ***************************************************************************/

    static $rates;

    unset($this->pay_info);
    $db = new db_alloc;

    if (!$this->get_value("projectID") || !$this->get_value("personID")) {
      return false;
    }
    
    // The unit labels
    $timeUnit = new timeUnit;
    $units = array_reverse($timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA"),true);

    if ($rates[$this->get_value("projectID")][$this->get_value("personID")]) {
      list($this->pay_info["project_rate"],$this->pay_info["project_rateUnitID"]) = $rates[$this->get_value("projectID")][$this->get_value("personID")];
    } else {
      // Get rate for person for this particular project
      $db->query("SELECT rate, rateUnitID FROM projectPerson
                  WHERE projectID = ".$this->get_value("projectID")."
                  AND personID = ".$this->get_value("personID"));

      $db->next_record();
      $this->pay_info["project_rate"] = $db->f("rate");
      $this->pay_info["project_rateUnitID"] = $db->f("rateUnitID");

      $rates[$this->get_value("projectID")][$this->get_value("personID")] = array($this->pay_info["project_rate"],$this->pay_info["project_rateUnitID"]);
    }

    // Get external rate
    $this->pay_info["customerBilledDollars"] = $this->get_value("customerBilledDollars");

    // Get duration for this timesheet/timeSheetItem
    $db->query(sprintf("SELECT * FROM timeSheetItem WHERE timeSheetID = %d",$this->get_id()));


    while ($db->next_record()) {
      $this->pay_info["total_duration"] += $db->f("timeSheetItemDuration");
      $this->pay_info["duration"][$db->f("timeSheetItemID")] = $db->f("timeSheetItemDuration");
      $this->pay_info["total_dollars"] += ($db->f("timeSheetItemDuration") * $db->f("rate"));
      $db->f("rate") and $this->pay_info["timeSheetItem_rate"] = $db->f("rate");

      if ($db->f("rate") > 0) {
        $this->pay_info["total_customerBilledDollars"] += ($db->f("timeSheetItemDuration") * $this->pay_info["customerBilledDollars"]);
      }
      $summary_totals[$units[$db->f("timeSheetItemDurationUnitID")]] += $db->f("timeSheetItemDuration");
    }

    // Reorder unit data into months->weeks->days->hours
    if (is_array($summary_totals) && count($summary_totals)) {
      foreach ($units as $v) {
        foreach ($summary_totals as $label => $amount) {
          if ($v == $label) {
            $summary_totals_ordered[$label] = $amount;
          }
        }
      }
      // Load up unit data
      $this->pay_info["summary_unit_totals"] = "";
      if (is_array($summary_totals_ordered) && count($summary_totals_ordered)) {
        foreach ($summary_totals_ordered as $label => $amount) {
          $this->pay_info["summary_unit_totals"] .= $br.$amount." ".$label;
          $br = ", ";
        }
      } 
    }


    if (!isset($this->pay_info["total_dollars"])) {
      $this->pay_info["total_dollars"] = 0;
    }
    if (!isset($this->pay_info["total_duration"])) {
      $this->pay_info["total_duration"] = 0;
    }
    $taxPercent = config::get_config_item("taxPercent");
    $taxPercentDivisor = ($taxPercent/100) + 1;
    $this->pay_info["total_dollars_minus_gst"] = $this->pay_info["total_dollars"] / $taxPercentDivisor;
    $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_customerBilledDollars"] / $taxPercentDivisor;
  }

  function destroyTransactions() {
    $db = new db_alloc;
    $query = sprintf("DELETE FROM transaction where timeSheetID = %d", $this->get_id());
    $db->query($query);
    $db->next_record();
  }

  function createTransactions() {

    // So this will only create transaction if:
    // - The timesheet status is admin
    // - There is a recipient_tfID - that is the money is going to a TF
    $db = new db_alloc;
    $project = $this->get_foreign_object("project");
    $projectName = $project->get_value("projectName");
    $company_tfID = config::get_config_item("cybersourceTfID");
    $cost_centre = $project->get_value("cost_centre_tfID") or $cost_centre = $company_tfID;
    $this->load_pay_info();

    if ($this->get_value("status") != "invoiced") {
      return "ERROR: The Status of the timesheet must be 'ADMIN' in order to Create Transactions.  The status is currently: ".$this->get_value("status");
    } else if ($this->get_value("recipient_tfID") == "") {
      return "ERROR: There is no recipient TF to credit for this timesheet.";
    } else if (!$cost_centre || $cost_centre == 0) {
      return "ERROR: There is no cost centre associated with the project.";
    } else if ($this->pay_info["total_dollars"] < 0) {
      return "ERROR: The dollar amount for this timesheet is ".$this->pay_info["total_dollars"];
    } else {

      $taxName = config::get_config_item("taxName");
      $taxPercent = config::get_config_item("taxPercent");
      $taxPercentDivisor = ($taxPercent/100) + 1;
      $payrollTaxPercent = config::get_config_item("payrollTaxPercent");
      $companyPercent = config::get_config_item("companyPercent");
      $paymentInsurancePercent = config::get_config_item("paymentInsurancePercent");
      $paymentInsurancePercent and $paymentInsurancePercentMult = ($paymentInsurancePercent/100);

      $recipient_tfID = $this->get_value("recipient_tfID");
      $timeSheetRecipients = $project->get_timeSheetRecipients();
      $this->get_value("payment_insurance") and $insur_trans_status = "approved";
      $rtn = array();

      if ($_POST["create_transactions_old"]) {

        // 1. Debit Cost Centre
        $product = "Debit: Cost Centre for ".$projectName." for timesheet id: ".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, -$this->pay_info["total_dollars_minus_gst"], $cost_centre, "timesheet");


        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Credit: Agency Percentage ".$agency_percentage."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"])." for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Credit: ".$percent."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"])." for timesheet id: ".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($percent/100), $company_tfID, "timesheet");


        // 3. Credit Employee TF
        $product = "Credit: Timesheet id: ".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * .665, $recipient_tfID, "timesheet", $insur_trans_status);


        // 4. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Debit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * -$paymentInsurancePercentMult, $recipient_tfID, "insurance", $insur_trans_status);
          $product = "Credit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status);
        }

        
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson 
                     WHERE projectID = ".$this->get_value("projectID")." 
                    AND commissionPercent != 0");
        while ($db->next_record()) {
          $percent_so_far += $db->f("commissionPercent");
          $product = "Credit: Commission ".$db->f("commissionPercent")."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"]);
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($db->f("commissionPercent")/100)
                                                  , $db->f("tfID")
                                                  , "commission");
        }
    
        // 6. Employee gets commission if none were paid previously or the remainder of 5% commission if there is any
        if (!$percent_so_far || $percent_so_far < 5) {
          $percent = 5 - $percent_so_far;
          $product = "Credit: Commission ".sprintf("%0.3f",$percent)."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"]);
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($percent/100)
                                                  , $recipient_tfID
                                                  , "commission");
        } 

        #$rtnmsg = "Created Old Style Transactions. ".$taxName." deducted.";


      // This is just for internal transactions
      } else if ($_POST["create_transactions_default"] && $this->pay_info["total_customerBilledDollars"] == 0) {

        $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_dollars"];

        // 1. Debit Cost Centre
        $product = "Debit: Cost Centre for ".$projectName." for timesheet id: ".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, -$this->pay_info["total_customerBilledDollars_minus_gst"], $cost_centre, "timesheet");

        // 3. Credit Employee TF
        $product = "Credit: Timesheet id: ".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $insur_trans_status);

        // 4. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Debit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * -$paymentInsurancePercentMult, $recipient_tfID, "insurance", $insur_trans_status);
          $product = "Credit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status);
        }
        
        /*
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson where projectID = ".$this->get_value("projectID")." ORDER BY commissionPercent DESC");
        while ($db->next_record()) {
          $product = "Credit: Commission ".$db->f("commissionPercent")."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"]);
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);

          // Suck up the rest of funds if it is a special zero % commission
          if ($db->f("commissionPercent") == 0) { 
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_positive_amount_so_far_minus_insurance();
            $amount < 0 and $amount = 0;
            $product = "Credit: Commission Remaining from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          }

          $rtn[$product] = $this->createTransaction($product
                                                  , $amount
                                                  , $db->f("tfID")
                                                  , "commission");
        }
        */


      } else if ($_POST["create_transactions_default"]) {
        /*  This was previously named "Simple" transactions. Ho ho.
            On the Project page we care about these following variables:
             - Client Billed At $amount eg: $121
             - Through an agency bool     
             - The projectPersons rate for this project eg: $50;

            $121 after gst == $110
            cyber get 28.5% of $110 
            payroll tax
            djk get $50
            commissions 
            payment insurance
            whatever is left of the $110 goes to the timesheet manager
        */
        

        // 1. Debit Cost Centre
        $product = "Debit: Cost Centre for ".$projectName." for timesheet id: ".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, -$this->pay_info["total_customerBilledDollars_minus_gst"], $cost_centre, "timesheet");


        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Credit: Agency Percentage ".$agency_percentage."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"])." for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Credit: ".$percent."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"])." for timesheet id: ".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($percent/100), $company_tfID, "timesheet");


        // 3. Credit Employee TF
        $product = "Credit: Timesheet id: ".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $insur_trans_status);


        // 4. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Debit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * -$paymentInsurancePercentMult, $recipient_tfID, "insurance", $insur_trans_status);
          $product = "Credit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status);
        }

        
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson where projectID = ".$this->get_value("projectID")." ORDER BY commissionPercent DESC");
        while ($db->next_record()) {

          if ($db->f("commissionPercent") > 0) { 
            $product = "Credit: Commission ".$db->f("commissionPercent")."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"]);
            $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");

          // Suck up the rest of funds if it is a special zero % commission
          } else if ($db->f("commissionPercent") == 0) { 
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_positive_amount_so_far_minus_insurance();
            $amount < 0 and $amount = 0;
            config::for_cyber() and $amount = $amount/2; // If it's cyber do a 50/50 split with the commission tf and the company
            $product = "Credit: Commission Remaining from timesheet id: ".$this->get_id().".  Project: ".$projectName;
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
            config::for_cyber() and $rtn[$product] = $this->createTransaction($product, $amount, $company_tfID, "commission"); // 50/50
          }

        }
        #$rtnmsg = "Created New Style Transactions.";
      }

      foreach($rtn as $error=>$v) {
        $v != 1 and $errmsg.= "<br>FAILED: ".$error;
      }
      if ($errmsg) {
        $this->destroyTransactions();
        $rtnmsg.= "<br> <h1>Failed to create transactions...</h1> ".$errmsg;
      }
      return $rtnmsg;
    }
  }

  function get_positive_amount_so_far_minus_insurance() {
    // This is for getting the amount the manager gets. There is probably a better way to do this.
    $db = new db_alloc;
    $db->query("SELECT * FROM transaction 
                WHERE timeSheetID = ".$this->get_id()." AND amount > 0 AND transactionType != 'insurance'");
    while ($db->next_record()) {
      $amount_so_far += $db->f("amount");
    }
    return $amount_so_far;
  }

  function createTransaction($product, $amount, $tfID, $transactionType, $status="") {

    if ($amount == 0) return 1;

    $status or $status = "pending";

    if ($tfID == 0 || !$tfID || !is_numeric($tfID) || !is_numeric($amount)) {
      return "Error -> \$tfID: ".$tfID."  and  \$amount: ".$amount;
    } else {
      $transaction = new transaction;
      $transaction->set_value("product", $product);
      $transaction->set_value("amount", sprintf("%0.2f", $amount));
      $transaction->set_value("status", $status);
      $transaction->set_value("tfID", $tfID);
      $transaction->set_value("dateEntered", date("Y-m-d"));
      $transaction->set_value("transactionDate", date("Y-m-d"));
      $transaction->set_value("invoiceItemID", $this->get_value("invoiceItemID"));
      $transaction->set_value("transactionType", $transactionType);
      $transaction->set_value("timeSheetID", $this->get_id());
      $transaction->save();
      return 1;
    }
  }

  function shootEmail($addr, $msg, $sub, $type, $dummy) {
    
    // New email object wrapper takes care of logging etc.
    $email = new alloc_email($addr,$sub,$msg,$type);

    // REMOVE ME!!
    #$email->ignore_no_email_urls = true;


    if ($dummy) {
      return "Elected not to send email.";
    } else if (!$email->is_valid_url()) {
      return "Almost sent email to: ".$email->to_address;
    } else if (!$email->to_address) {
      return "Could not send email, invalid email address: ".$email->to_address;
    } else if ($email->send()) {
      return "Sent email to: ".$email->to_address;
    } else {
      return "Problem sending email to: ".$email->to_address;
    }
  }

  function get_task_list_dropdown($status,$timeSheetID,$taskID="") {

    if (is_object($this)) {
      $personID = $this->get_value('personID');
      $projectID = $this->get_value('projectID');
    } else if ($timeSheetID) {
      $t = new timeSheet;
      $t->set_id($timeSheetID);    
      $t->select();
      $personID = $t->get_value('personID');
      $projectID = $t->get_value('projectID');
    }

    $options["projectID"] = $projectID;
    $options["personID"] = $personID;
    $options["taskView"] = "byProject";
    $options["return"] = "dropdown_options";
    $options["taskTimeSheetStatus"] = $status;
    $tasks = task::get_task_list($options) or $tasks = array();

    if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $tasks[$taskID] = $t->get_task_name();
    }

    $dropdown_options = get_option("",0);
    $dropdown_options.= get_select_options($tasks, $taskID, 100);
    return "<select name=\"timeSheetItem_taskID\" style=\"width:400px\">".$dropdown_options."</select>";
  }

  function get_timeSheet_list_filter($filter=array()) {
    if ($filter["projectID"]) {
      $sql[] = sprintf("(timeSheet.projectID = '%d')", $filter["projectID"]);
    }
    if ($filter["personID"]) {
      $sql[] = sprintf("(timeSheet.personID = '%d')", $filter["personID"]);
    }
    if ($filter["status"]) {
      $sql[] = sprintf("(timeSheet.status = '%s')", db_esc($filter["status"]));
    }
    if ($filter["dateFrom"]) {
      $sql[] = sprintf("(timeSheet.dateFrom >= '%s')", db_esc($filter["dateFrom"]));
    }

    return $sql;
  }

  function get_timeSheet_list($_FORM) {
    /*
     * This is the definitive method of getting a list of timeSheets that need a sophisticated level of filtering
     * 
     * Display Options:
     *  showHeader
     *  showProject
     *  showProjectLink
     *  showAmount
     *  showDuration
     *  showPerson
     *  showDateFrom
     *  showDateTo
     *  showStatus
     *  
     * Filter Options:
     *   projectID
     *   personID
     *   status
     *   dateFrom
     *
     */

    $filter = timeSheet::get_timeSheet_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";

    $_FORM["return"] or $_FORM["return"] = "html";

    // A header row
    $summary.= timeSheet::get_timeSheet_list_tr_header($_FORM);

    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT timeSheet.*, person.personID, projectName 
          FROM timeSheet 
          LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
          LEFT JOIN person ON timeSheet.personID = person.personID
          LEFT JOIN project ON timeSheet.projectID = project.projectID
          ".$filter."
          GROUP BY timeSheet.timeSheetID
          ORDER BY dateFrom,projectName,surname";

    $debug and print "Query: ".$q;
    $db = new db_alloc();
    $db->query($q);
    $status_array = timeSheet::get_timeSheet_statii();
    $people_array = get_cached_table("person");

    while ($row = $db->next_record()) {
      $print = true;
      $t = new timeSheet;
      $t->read_db_record($db);
      $t->load_pay_info();
     
      $row["amount"] = sprintf("%0.2f",$t->pay_info["total_dollars"]);
      $extra["amountTotal"] += $row["amount"];
      $row["duration"] = $t->pay_info["summary_unit_totals"];
      $row["person"] = $people_array[$row["personID"]]["name"];
      $row["status"] = $status_array[$row["status"]];

      $p = new project();
      $p->read_db_record($db);
      #$row["projectName"] = $p->get_project_name();
      $row["projectLink"] = $t->get_link($p->get_project_name());
      $summary.= timeSheet::get_timeSheet_list_tr($row,$_FORM);
    }

    if ($print && $_FORM["return"] == "html") {
      $summary.= timeSheet::get_timeSheet_list_tr_bottom($extra,$_FORM);
      return "<table class=\"tasks\" border=\"0\" cellspacing=\"0\">".$summary."</table>";

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Time Sheets Found</b></td></tr></table>";
    }
  }

  function get_link($text) {
    global $TPL;
    return "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$this->get_id()."\">".$text."</a>";
  }

  function get_timeSheet_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $summary = "\n<tr>";
      $_FORM["showProject"]       and $summary.= "\n<th class=\"col\">Time Sheet</th>";
      $_FORM["showProjectLink"]   and $summary.= "\n<th class=\"col\">Time Sheet</th>";
      $_FORM["showAmount"]        and $summary.= "\n<th class=\"col\">Amount</th>";
      $_FORM["showDuration"]      and $summary.= "\n<th class=\"col\">Duration</th>";
      $_FORM["showPerson"]        and $summary.= "\n<th class=\"col\">Owner</th>";
      $_FORM["showDateFrom"]      and $summary.= "\n<th class=\"col\">Start Date</th>";
      $_FORM["showDateTo"]        and $summary.= "\n<th class=\"col\">End Date</th>";
      $_FORM["showStatus"]        and $summary.= "\n<th class=\"col\">Status</th>";
      $summary.="\n</tr>";
      return $summary;
    }
  }

  function get_timeSheet_list_tr($row,$_FORM) {
    static $odd_even;
    $odd_even = $odd_even == "even" ? "odd" : "even";

    $summary[] = "<tr class=\"".$odd_even."\">";
    $_FORM["showProject"]         and $summary[] = "  <td class=\"col\">".$row["projectName"]."&nbsp;</td>";
    $_FORM["showProjectLink"]     and $summary[] = "  <td class=\"col\">".$row["projectLink"]."&nbsp;</td>";
    $_FORM["showAmount"]          and $summary[] = "  <td class=\"col\" align=\"right\">".sprintf("$%0.2f",$row["amount"])."&nbsp;</td>";
    $_FORM["showDuration"]        and $summary[] = "  <td class=\"col\">".$row["duration"]."&nbsp;</td>";
    $_FORM["showPerson"]          and $summary[] = "  <td class=\"col\">".$row["person"]."&nbsp;</td>";
    $_FORM["showDateFrom"]        and $summary[] = "  <td class=\"col\">".$row["dateFrom"]."&nbsp;</td>";
    $_FORM["showDateTo"]          and $summary[] = "  <td class=\"col\">".$row["dateTo"]."&nbsp;</td>";
    $_FORM["showStatus"]          and $summary[] = "  <td class=\"col\">".$row["status"]."&nbsp;</td>";
    $summary[] = "</tr>";
     
    $summary = "\n".implode("\n",$summary);
    return $summary;   
  } 

  function get_timeSheet_list_tr_bottom($row,$_FORM) {
    if ($_FORM["showAmountTotal"]) {
      $summary[] = "<tr>";
      $_FORM["showProject"]         and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showProjectLink"]     and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showAmountTotal"]     and $summary[] = "  <td class=\"col grand_total\" align=\"right\">".sprintf("$%0.2f",$row["amountTotal"])."</td>";
      $_FORM["showDuration"]        and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showPerson"]          and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showDateFrom"]        and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showDateTo"]          and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $_FORM["showStatus"]          and $summary[] = "  <td class=\"col\">&nbsp;</td>";
      $summary[] = "</tr>";
      $summary = "\n".implode("\n",$summary);
    }
    return $summary;   
  } 

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array("showHeader"
                      ,"showProject"
                      ,"showProjectLink"
                      ,"showAmount"
                      ,"showAmountTotal"
                      ,"showDuration"
                      ,"showPerson"
                      ,"showDateFrom"
                      ,"showDateTo"
                      ,"showStatus"

                      ,"projectID"
                      ,"personID"
                      ,"status"
                      ,"dateFrom"

                      ,"url_form_action"
                      ,"form_name"
                      ,"dontSave"
                      ,"applyFilter"
                      );

    $_FORM = get_all_form_data($page_vars,$defaults);

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["status"] = "edit";
        $_FORM["personID"] = $current_user->get_id();
      }

    } else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
      $url = $_FORM["url_form_action"];
      unset($_FORM["url_form_action"]);
      $current_user->prefs[$_FORM["form_name"]] = $_FORM;
      $_FORM["url_form_action"] = $url;
    }

    return $_FORM;
  }

  function load_timeSheet_filter($_FORM) {
    global $current_user;

    // display the list of project name.
    $db = new db_alloc();
    $query = sprintf("SELECT * FROM project ORDER by projectName");
    $db->query($query);
    $project_array = get_array_from_db($db, "projectID", "projectName");
    $rtn["show_project_options"] = get_options_from_array($project_array, $_FORM["projectID"], true);

    // display the list of user name.
    if (have_entity_perm("timeSheet", PERM_READ, $current_user, false)) {
      $rtn["show_userID_options"] = get_option(" ", "");
      $rtn["show_userID_options"].= get_select_options(person::get_username_list(), $_FORM["personID"]);
      
    } else {
      $person = new person;
      $person->set_id($current_user->get_id());
      $person->select();
      $person_array = array($current_user->get_id()=>$person->get_username(1));
      $rtn["show_userID_options"].= get_options_from_array($person_array, $_FORM["personID"], true);
    } 

    // display a list of status
    $status_array = timeSheet::get_timeSheet_statii();
    unset($status_array["create"]);

    $rtn["show_status_options"] = get_options_from_array($status_array, $_FORM["status"]);

    // display the date from filter value
    $rtn["dateFrom"] = $_FORM["dateFrom"];
    $rtn["userID"] = $current_user->get_id();

    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }




}  




?>
