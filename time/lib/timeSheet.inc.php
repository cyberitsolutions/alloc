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
    $this->key_field = new db_text_field("timeSheetID");
    $this->data_fields = array("projectID"=>new db_text_field("projectID")
                               , "dateFrom"=>new db_text_field("dateFrom")
                               , "dateTo"=>new db_text_field("dateTo")
                               , "status"=>new db_text_field("status")
                               , "personID"=>new db_text_field("personID")
                               , "approvedByManagerPersonID"=>new db_text_field("approvedByManagerPersonID")
                               , "approvedByAdminPersonID"=>new db_text_field("approvedByAdminPersonID")
                               , "invoiceNum"=>new db_text_field("invoiceNum")
                               , "invoiceItemID"=>new db_text_field("invoiceItemID")
                               , "dateSubmittedToManager"=>new db_text_field("dateSubmittedToManager")
                               , "dateSubmittedToAdmin"=>new db_text_field("dateSubmittedToAdmin")
                               , "billingNote"=>new db_text_field("billingNote")
                               , "payment_insurance"=>new db_text_field("payment_insurance")
                               , "recipient_tfID"=>new db_text_field("recipient_tfID")
      );
    $this->permissions[PERM_TIME_APPROVE_TIMESHEETS] = "Approve";
    $this->permissions[PERM_TIME_INVOICE_TIMESHEETS] = "Invoice";
  }

  function get_timeSheet_statii() {
    return array("create"    => "Create"
                ,"edit"      => "Add Time"
                ,"manager"   => "Project Manager"
                ,"admin"     => "Administrator"
                ,"invoiced"  => "Invoiced"
                ,"paid"      => "Time Sheet Paid"
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

    unset($this->pay_info);
    $db = new db_alloc;

    if (!$this->get_value("projectID") || !$this->get_value("personID")) {
      return false;
    }
    
    // The unit labels
    $timeUnit = new timeUnit;
    $units = array_reverse($timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA"),true);

    // Get rate for person for this particular project
    $db->query("SELECT rate, rateUnitID FROM projectPerson
                WHERE projectID = ".$this->get_value("projectID")."
                AND personID = ".$this->get_value("personID"));

    $db->next_record();
    $this->pay_info["project_rate"] = $db->f("rate");
    $this->pay_info["project_rateUnitID"] = $db->f("rateUnitID");

    // Get external rate for this particular project
    $db->query("SELECT customerBilledDollars FROM project WHERE projectID = ".$this->get_value("projectID"));
    $db->next_record();
    $this->pay_info["project_customerBilledDollars"] = $db->f("customerBilledDollars");

    // Get duration for this timesheet/timeSheetItem
    $db->query(sprintf("SELECT * FROM timeSheetItem WHERE timeSheetID = %d",$this->get_id()));


    while ($db->next_record()) {
      $this->pay_info["total_duration"] += $db->f("timeSheetItemDuration");
      $this->pay_info["duration"][$db->f("timeSheetItemID")] = $db->f("timeSheetItemDuration");
      $this->pay_info["total_dollars"] += ($db->f("timeSheetItemDuration") * $db->f("rate"));

      if ($db->f("rate") > 0) {
        $this->pay_info["total_customerBilledDollars"] += ($db->f("timeSheetItemDuration") * $this->pay_info["project_customerBilledDollars"]);
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
    $taxPercentMult = (100 - $taxPercent)/100;
    $this->pay_info["total_dollars_minus_gst"] = $this->pay_info["total_dollars"] * $taxPercentMult;
    $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_customerBilledDollars"] * $taxPercentMult;
  }

  function destroyTransactions() {
    $db = new db_alloc;
    $query = sprintf("DELETE FROM transaction where timeSheetID = %d", $this->get_id());
    $db->query($query);
    $db->next_record();
  }

  function transactions_are_complex() {
    $simple_or_complex_transaction = $_POST["simple_or_complex_transaction"];
    $project = $this->get_foreign_object("project");

    if ($simple_or_complex_transaction == "simple") {
      return $simple_or_complex_transaction;
    
    } else if ($simple_or_complex_transaction == "none") {
      return $simple_or_complex_transaction;

    } else if (($project->get_value("clientID") != 13) || $simple_or_complex_transaction == "complex") {
      return "complex";
    }

    return "none";
  }

  function createTransactions() {

    // So this will only create transaction if:
    // - The timesheet status is admin
    // - There is a recipient_tfID - that is the money is going to a TF
    $db = new db_alloc;
    $project = $this->get_foreign_object("project");
    $projectName = $project->get_value("projectName");
    $cost_centre = $project->get_value("cost_centre_tfID");
    $this->load_pay_info();

    if ($this->get_value("status") != "admin") {
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
      $taxPercentMult = (100 - $taxPercent)/100;
      $payrollTaxPercent = config::get_config_item("payrollTaxPercent");
      $companyPercent = config::get_config_item("companyPercent");
      $paymentInsurancePercent = config::get_config_item("paymentInsurancePercent");
      $paymentInsurancePercentMult = (100 - $paymentInsurancePercent)/100;

      $cyberIsClient = $project->get_value("clientID") == 13;
      $cyberNotClient = $project->get_value("clientID") != 13;
      $recipient_tfID = $this->get_value("recipient_tfID");
      $cyber_tfID = config::get_config_item("cybersourceTfID");
      $cyberIsCostCentre = $project->get_value("cost_centre_tfID") == $cyber_tfID;
      $cyberNotCostCentre = $project->get_value("cost_centre_tfID") != $cyber_tfID;
      $timeSheetRecipients = $project->get_timeSheetRecipients();
      $this->get_value("payment_insurance") and $insur_trans_status = "approved";
      $rtn = array();

      if ($this->transactions_are_complex() == "complex") {

        // 1. Debit Cost Centre
        $product = "Debit: Cost Centre for ".$projectName." for timesheet id: ".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, -$this->pay_info["total_dollars_minus_gst"], $cost_centre, "timesheet");


        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Credit: Agency Percentage ".$agency_percentage."% of $".$this->pay_info["total_dollars_minus_gst"]." for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Credit: ".$percent."% of $".$this->pay_info["total_dollars_minus_gst"]." for timesheet id: ".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($percent/100), $cyber_tfID, "timesheet");


        // 3. Credit Employee TF
        $product = "Credit: Timesheet id: ".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * .665, $recipient_tfID, "timesheet", $insur_trans_status);


        // 4. Payment Insurance
        if ($this->get_value("payment_insurance")) {
          $product = "Debit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * -$paymentInsurancePercentMult, $recipient_tfID, "insurance", $insur_trans_status);
          $product = "Credit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * $paymentInsurancePercentMult, $cyber_tfID, "insurance", $insur_trans_status);
        }

        
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson 
                     WHERE projectID = ".$this->get_value("projectID")." 
                    AND commissionPercent != 0");
        while ($db->next_record()) {
          $percent_so_far += $db->f("commissionPercent");
          $product = "Credit: Commission ".$db->f("commissionPercent")."% of $".$this->pay_info["total_dollars_minus_gst"];
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($db->f("commissionPercent")/100)
                                                  , $db->f("tfID")
                                                  , "commission");
        }
    
        // 6. Employee gets commission if none were paid previously or the remainder of 5% commission if there is any
        if (!$percent_so_far || $percent_so_far < 5) {
          $percent = 5 - $percent_so_far;
          $product = "Credit: Commission ".sprintf("%0.3f",$percent)."% of $".$this->pay_info["total_dollars_minus_gst"];
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($percent/100)
                                                  , $recipient_tfID
                                                  , "commission");
        } 

        $rtnmsg = "Created Old Style Transactions. ".$taxName." deducted.";

      } else  if ($this->transactions_are_complex() == "simple") {
        /*  This was previously named "Simple" transactions. Ho ho.
            On the Project page we care about these following variables:
             - Customer Billed At $amount eg: $121
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
          $product = "Credit: Agency Percentage ".$agency_percentage."% of $".$this->pay_info["total_customerBilledDollars_minus_gst"]." for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Credit: ".$percent."% of $".$this->pay_info["total_customerBilledDollars_minus_gst"]." for timesheet id: ".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($percent/100), $cyber_tfID, "timesheet");


        // 3. Credit Employee TF
        $product = "Credit: Timesheet id: ".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $insur_trans_status);


        // 4. Payment Insurance
        if ($this->get_value("payment_insurance")) {
          $product = "Debit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * -$paymentInsurancePercentMult, $recipient_tfID, "insurance", $insur_trans_status);
          $product = "Credit: Payment Insurance ".$paymentInsurancePercent."% for timesheet id: ".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * $paymentInsurancePercentMult, $cyber_tfID, "insurance", $insur_trans_status);
        }

        
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson where projectID = ".$this->get_value("projectID")." ORDER BY commissionPercent DESC");
        while ($db->next_record()) {
          $product = "Credit: Commission ".$db->f("commissionPercent")."% of $".$this->pay_info["total_customerBilledDollars_minus_gst"];
          $product.= " from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);

          // Suck up the rest of funds if it is a special zero % commission
          if ($db->f("commissionPercent") == 0) { 
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_positive_amount_so_far_minus_insurance();
            $product = "Credit: Commission Remaining from timesheet id: ".$this->get_id().".  Project: ".$projectName;
          }

          $rtn[$product] = $this->createTransaction($product
                                                  , $amount
                                                  , $db->f("tfID")
                                                  , "commission");
        }

    
        $rtnmsg = "Created New Style Transactions.";
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
    } else if (!$email->is_valid_to_address()) {
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

}  




?>
