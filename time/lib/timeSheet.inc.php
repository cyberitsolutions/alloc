<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

define("PERM_TIME_APPROVE_TIMESHEETS", 256);
define("PERM_TIME_INVOICE_TIMESHEETS", 512);

class timeSheet extends db_entity {
  public $classname = "timeSheet";
  public $data_table = "timeSheet";
  public $display_field_name = "projectID";
  public $key_field = "timeSheetID";
  public $data_fields = array("projectID"
                             ,"dateFrom"
                             ,"dateTo"
                             ,"status"
                             ,"personID"
                             ,"approvedByManagerPersonID"
                             ,"approvedByAdminPersonID"
                             ,"dateSubmittedToManager"
                             ,"dateSubmittedToAdmin"
                             ,"billingNote"
                             ,"payment_insurance"
                             ,"recipient_tfID"
                             ,"customerBilledDollars"
                             );
  public $permissions = array(PERM_TIME_APPROVE_TIMESHEETS => "approve"
                             ,PERM_TIME_INVOICE_TIMESHEETS => "invoice");

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
    } 

    if ($this->get_value("status") == "manager") { 
      $project = $this->get_foreign_object("project");
      $managers = $project->get_timeSheetRecipients() or $managers = array();
      if (in_array($current_user->get_id(), $managers)) {
        return true;
      }
    } 

    // This allows people with transactions on this time sheet who may not
    // actually be this time sheets owner to view this time sheet.
    if ($this->get_value("status") != "edit") { 
      $current_user_tfIDs = $current_user->get_tfIDs();
      $q = sprintf("SELECT * FROM transaction WHERE timeSheetID = %d",$this->get_id());
      $db = new db_alloc();
      $db->query($q);
      while ($db->next_record()) {
        if (is_array($current_user_tfIDs) && in_array($db->f("tfID"),$current_user_tfIDs)) {
          return true;
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
                ,"invoiced"  => "Invoice"
                ,"finished"  => "Completed"
                );
  } 

  function get_timeSheet_status() {
    $statii = $this->get_timeSheet_statii();
    return $statii[$this->get_value("status")];
  }

  function delete() {
    $db = new db_alloc;
    $db->query(sprintf("DELETE FROM timeSheetItem where timeSheetID = %d",$this->get_id()));  
    $db->query(sprintf("DELETE FROM transaction where timeSheetID = %d",$this->get_id()));  
    $db->query(sprintf("DELETE FROM invoiceItem where timeSheetID = %d",$this->get_id()));  
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
     * $this->pay_info["total_dollars_not_null"] tot_custbilled/tot_dollars    *
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
      $db->query("SELECT rate, rateUnitID 
                    FROM projectPerson
                   WHERE projectID = %d
                     AND personID = %d"
                 ,$this->get_value("projectID")
                 ,$this->get_value("personID"));

      $db->next_record();
      $this->pay_info["project_rate"] = $db->f("rate");
      $this->pay_info["project_rateUnitID"] = $db->f("rateUnitID");
      $rates[$this->get_value("projectID")][$this->get_value("personID")] = array($this->pay_info["project_rate"],$this->pay_info["project_rateUnitID"]);
    }

    // Get external rate, only load up customerBilledDollars if the field is actually set
    if ($this->get_value("customerBilledDollars") !== "" && $this->get_value("customerBilledDollars") !== NULL 
    && $this->get_value("customerBilledDollars") !== false) {
      $this->pay_info["customerBilledDollars"] = $this->get_value("customerBilledDollars");
    }

    // Get duration for this timesheet/timeSheetItem
    $db->query(sprintf("SELECT *, (timeSheetItemDuration * timeUnit.timeUnitSeconds) / 3600 AS hours
                          FROM timeSheetItem
                     LEFT JOIN timeUnit ON timeUnit.timeUnitID = timeSheetItem.timeSheetItemDurationUnitID
                         WHERE timeSheetID = %d",$this->get_id()));

    while ($db->next_record()) {
      $this->pay_info["total_duration"] += $db->f("timeSheetItemDuration");
      $this->pay_info["total_duration_hours"] += $db->f("hours");
      $this->pay_info["duration"][$db->f("timeSheetItemID")] = $db->f("timeSheetItemDuration");
      $tsi = new timeSheetItem();
      $tsi->read_db_record($db);
      $this->pay_info["total_dollars"] += $tsi->calculate_item_charge();
      $db->f("rate") and $this->pay_info["timeSheetItem_rate"] = $db->f("rate");

      $this->pay_info["total_customerBilledDollars"] += $tsi->calculate_item_charge($this->pay_info["customerBilledDollars"]);
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
    if (!isset($this->pay_info["total_duration_hours"])) {
      $this->pay_info["total_duration_hours"] = 0;
    }
    $taxPercent = config::get_config_item("taxPercent");
    $taxPercentDivisor = ($taxPercent/100) + 1;
    $this->pay_info["total_dollars_minus_gst"] = $this->pay_info["total_dollars"] / $taxPercentDivisor;
    $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_customerBilledDollars"] / $taxPercentDivisor;
    $this->pay_info["total_dollars_not_null"] = $this->pay_info["total_customerBilledDollars"] or $this->pay_info["total_dollars_not_null"] = $this->pay_info["total_dollars"];
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
    $company_tfID = config::get_config_item("mainTfID");
    $cost_centre = $project->get_value("cost_centre_tfID") or $cost_centre = $company_tfID;
    $this->fromTfID = $cost_centre;
    $this->load_pay_info();

    if ($this->get_value("status") != "invoiced") {
      return "ERROR: Status of the timesheet must be 'invoiced' to Create Transactions.  The status is currently: ".$this->get_value("status");

    } else if ($this->get_value("recipient_tfID") == "") {
      return "ERROR: There is no recipient TF to credit for this timesheet.";

    } else if (!$cost_centre || $cost_centre == 0) {
      return "ERROR: There is no cost centre associated with the project.";

    } else {
      $taxName = config::get_config_item("taxName");
      $taxPercent = config::get_config_item("taxPercent");
      $taxTfID = config::get_config_item("taxTfID") or $taxTfID = $cost_centre;
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

        // 1. Credit TAX/GST Cost Centre
        $product = $taxName." ".$taxPercent."% for timesheet #".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, ($this->pay_info["total_dollars"]-$this->pay_info["total_dollars_minus_gst"]), $taxTfID, "tax");

        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Agency Percentage ".$agency_percentage."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"])." for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Company ".$percent."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"])." for timesheet #".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($percent/100), $company_tfID, "timesheet");


        // 3. Credit Employee TF
        $product = "Timesheet #".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * .665, $recipient_tfID, "timesheet", $insur_trans_status);


        // 4. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Payment Insurance ".$paymentInsurancePercent."% for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status,$recipient_tfID);
        }

        
        // 5. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson 
                     WHERE projectID = ".$this->get_value("projectID")." 
                    AND commissionPercent != 0");
        while ($db->next_record()) {
          $percent_so_far += $db->f("commissionPercent");
          $product = "Commission ".$db->f("commissionPercent")."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"]);
          $product.= " from timesheet #".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($db->f("commissionPercent")/100)
                                                  , $db->f("tfID")
                                                  , "commission"
                                                  );
        }
    
        // 6. Employee gets commission if none were paid previously or the remainder of 5% commission if there is any
        if (!$percent_so_far || $percent_so_far < 5) {
          $percent = 5 - $percent_so_far;
          $product = "Commission ".sprintf("%0.3f",$percent)."% of $".sprintf("%0.2f",$this->pay_info["total_dollars_minus_gst"]);
          $product.= " from timesheet #".$this->get_id().".  Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product
                                                  , $this->pay_info["total_dollars_minus_gst"]*($percent/100)
                                                  , $recipient_tfID
                                                  , "commission"
                                                  );
        } 


      // This is just for internal transactions
      } else if ($_POST["create_transactions_default"] && $this->pay_info["total_customerBilledDollars"] == 0) {

        $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_dollars"];

        // 1. Credit Employee TF
        $product = "Timesheet #".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $insur_trans_status);

        // 2. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Payment Insurance ".$paymentInsurancePercent."% for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status,$recipient_tfID);
        }
        

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
            whatever is left of the $110 goes to the 0% commissions
        */
        
        // 1. Credit TAX/GST Cost Centre
        $product = $taxName." ".$taxPercent."% for timesheet #".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, ($this->pay_info["total_customerBilledDollars"]-$this->pay_info["total_customerBilledDollars_minus_gst"]), $taxTfID, "tax");

        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Agency Percentage ".$agency_percentage."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"])." for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }

        // 3. Do companies cut
        $percent = $companyPercent - $agency_percentage;
        $product = "Company ".$percent."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"])." for timesheet #".$this->get_id();
        $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($percent/100), $company_tfID, "timesheet");

        // 4. Credit Employee TF
        $product = "Timesheet #".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
        $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $insur_trans_status);

        // 5. Payment Insurance
        if ($this->get_value("payment_insurance") && $paymentInsurancePercent) {
          $product = "Payment Insurance ".$paymentInsurancePercent."% for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"] * $paymentInsurancePercentMult, $company_tfID, "insurance", $insur_trans_status,$recipient_tfID);
        }

        // 6. Credit Project Commissions
        $db->query("SELECT * FROM projectCommissionPerson where projectID = ".$this->get_value("projectID")." ORDER BY commissionPercent DESC");
        while ($db->next_record()) {

          if ($db->f("commissionPercent") > 0) { 
            $product = "Commission ".$db->f("commissionPercent")."% of $".sprintf("%0.2f",$this->pay_info["total_customerBilledDollars_minus_gst"]);
            $product.= " from timesheet #".$this->get_id().".  Project: ".$projectName;
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");

          // Suck up the rest of funds if it is a special zero % commission
          } else if ($db->f("commissionPercent") == 0) { 
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_positive_amount_so_far_minus_insurance();
            $amount < 0 and $amount = 0;
            config::for_cyber() and $amount = $amount/2; // If it's cyber do a 50/50 split with the commission tf and the company
            $product = "Commission Remaining from timesheet #".$this->get_id().".  Project: ".$projectName;
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
            config::for_cyber() and $rtn[$product] = $this->createTransaction($product, $amount, $company_tfID, "commission"); // 50/50
          }

        }
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
                WHERE timeSheetID = ".$this->get_id()." AND amount > 0 AND transactionType != 'insurance' AND transactionType != 'tax'");
    while ($db->next_record()) {
      $amount_so_far += $db->f("amount");
    }
    return $amount_so_far;
  }

  function createTransaction($product, $amount, $tfID, $transactionType, $status="", $fromTfID=false) {

    if ($amount == 0) return 1;

    $status or $status = "pending";
    $fromTfID or $fromTfID = $this->fromTfID;

    if ($tfID == 0 || !$tfID || !is_numeric($tfID) || !is_numeric($amount)) {
      return "Error -> \$tfID: ".$tfID."  and  \$amount: ".$amount;
    } else {
      $transaction = new transaction;
      $transaction->set_value("product", $product);
      $transaction->set_value("amount", sprintf("%0.2f", $amount));
      $transaction->set_value("status", $status);
      $transaction->set_value("fromTfID", $fromTfID);
      $transaction->set_value("tfID", $tfID);
      $transaction->set_value("transactionDate", date("Y-m-d"));
      $transaction->set_value("transactionType", $transactionType);
      $transaction->set_value("timeSheetID", $this->get_id());
      $transaction->save();
      return 1;
    }
  }

  function shootEmail($email) {
    
    $addr = $email["to"];
    $msg = $email["body"];
    $sub = $email["subject"];
    $type = $email["type"];
    $dummy = $_POST["dont_send_email"];
    
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
    $tasks = task::get_list($options) or $tasks = array();

    if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $tasks[$taskID] = $t->get_id()." ".$t->get_task_name();
    }

    $dropdown_options = page::select_options($tasks, $taskID, 100);
    return "<select name=\"timeSheetItem_taskID\" style=\"width:400px\"><option value=\"\">".$dropdown_options."</select>";
  }

  function get_list_filter($filter=array()) {
    if ($filter["projectID"]) {
      $sql[] = sprintf("(timeSheet.projectID = '%d')", $filter["projectID"]);
    }
    if ($filter["taskID"]) {
      $sql[] = sprintf("(timeSheetItem.taskID = '%d')", $filter["taskID"]);
    }
    if ($filter["personID"]) {
      $sql[] = sprintf("(timeSheet.personID = '%d')", $filter["personID"]);
    }
    if ($filter["status"] && is_array($filter["status"]) && count($filter["status"])) {
      $sql[] = sprintf("(timeSheet.status in ('%s'))", implode("','",$filter["status"]));
    } else if ($filter["status"]) {
      $sql[] = sprintf("(timeSheet.status = '%s')", db_esc($filter["status"]));
    }
    if ($filter["dateFrom"]) {
      $sql[] = sprintf("(timeSheet.dateFrom >= '%s')", db_esc($filter["dateFrom"]));
    }
    if ($filter["dateTo"]) {
      $sql[] = sprintf("(timeSheet.dateFrom <= '%s')", db_esc($filter["dateTo"]));
    }
    return $sql;
  }

  function get_list($_FORM) {
    /*
     * This is the definitive method of getting a list of timeSheets that need a sophisticated level of filtering
     *
     */
  
    global $TPL;
    $_FORM["showShortProjectLink"] and $_FORM["showProjectLink"] = true;
    $filter = timeSheet::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";

    $_FORM["return"] or $_FORM["return"] = "html";

    // A header row
    $summary.= timeSheet::get_list_tr_header($_FORM);

    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT timeSheet.*, person.personID, projectName, projectShortName
          FROM timeSheet 
          LEFT JOIN person ON timeSheet.personID = person.personID
          LEFT JOIN project ON timeSheet.projectID = project.projectID
          LEFT JOIN timeSheetItem ON timeSheet.timeSheetID = timeSheetItem.timeSheetID 
          ".$filter."
          GROUP BY timeSheet.timeSheetID
          ORDER BY dateFrom,projectName,timeSheet.status,surname";

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
      $extra["totalHours"] += $t->pay_info["total_duration_hours"];
      $row["duration"] = $t->pay_info["summary_unit_totals"];
      $row["person"] = $people_array[$row["personID"]]["name"];
      $row["status"] = $status_array[$row["status"]];
      $row["customerBilledDollars"] = $t->pay_info["total_customerBilledDollars"];
      $extra["customerBilledDollarsTotal"] += $t->pay_info["total_customerBilledDollars"];

      if ($_FORM["showTransactionsNeg"] || $_FORM["showTransactionsPos"]) {
        list($row["transactionsPos"],$row["transactionsNeg"]) = $t->get_transaction_totals();
        $extra["transactionsPosTotal"] += $row["transactionsPos"];
        $extra["transactionsNegTotal"] += $row["transactionsNeg"];
      }

      $p = new project();
      $p->read_db_record($db);
      #$row["projectName"] = $p->get_project_name();
      $row["projectLink"] = $t->get_link($p->get_project_name($_FORM["showShortProjectLink"]));
      $summary.= timeSheet::get_list_tr($row,$_FORM);
      $rows[$row["timeSheetID"]] = $row;
    }

    if ($print && $_FORM["return"] == "array") {
      return $rows;

    } else if ($print && $_FORM["return"] == "html") {
      $summary.= timeSheet::get_list_tr_bottom($extra,$_FORM);
      return "<table class=\"list sortable\">".$summary."</table>";

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Time Sheets Found</b></td></tr></table>";
    }
  }

  function get_transaction_totals() {
  
    $db = new db_alloc();
    $q = sprintf("SELECT SUM(amount) AS total FROM transaction WHERE amount>0 AND status = 'approved' AND timeSheetID = %d",$this->get_id());
    $db->query($q);
    $row = $db->row();
    $pos = $row["total"];

    $q = sprintf("SELECT SUM(amount) AS total FROM transaction WHERE amount<0 AND status = 'approved' AND timeSheetID = %d",$this->get_id());
    $db->query($q);
    $row = $db->row();
    $neg = $row["total"];

    return array($pos,$neg);
  }

  function get_url() {
    global $sess;
    $sess or $sess = new Session;

    $url = "time/timeSheet.php?timeSheetID=".$this->get_id();

    if ($sess->Started()) {
      $url = $sess->url(SCRIPT_PATH.$url);

    // This for urls that are emailed
    } else {
      static $prefix;
      $prefix or $prefix = config::get_config_item("allocURL");
      $url = $prefix.$url;
    }
    return $url;
  }

  function get_link($text=false) {
    $text or $text = $this->get_id();
    return "<a href=\"".$this->get_url()."\">".$text."</a>";
  }

  function get_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $summary = "\n<tr>";
      $_FORM["showTimeSheetID"]   and $summary.= "\n<th class=\"sorttable_numeric\">ID</th>";
      $_FORM["showProject"]       and $summary.= "\n<th>Project</th>";
      $_FORM["showProjectLink"]   and $summary.= "\n<th>Project</th>";
      $_FORM["showPerson"]        and $summary.= "\n<th>Owner</th>";
      $_FORM["showDateFrom"]      and $summary.= "\n<th>Start Date</th>";
      $_FORM["showDateTo"]        and $summary.= "\n<th>End Date</th>";
      $_FORM["showStatus"]        and $summary.= "\n<th>Status</th>";
      $_FORM["showDuration"]      and $summary.= "\n<th>Duration</th>";
      $_FORM["showAmount"]        and $summary.= "\n<th class=\"right\">Amount</th>";
      $_FORM["showCustomerBilledDollars"] and $summary.= "\n<th class=\"right\">Customer Billed</th>";
      $_FORM["showTransactionsPos"] and $summary.= "\n<th class=\"right\">Sum $ &gt;0</th>";
      $_FORM["showTransactionsNeg"] and $summary.= "\n<th class=\"right\">Sum $ &lt;0</th>";
      $summary.="\n</tr>";
      return $summary;
    }
  }

  function get_list_tr($row,$_FORM) {
    $summary[] = "<tr>";
    $_FORM["showTimeSheetID"]     and $summary[] = "  <td>".$row["timeSheetID"]."&nbsp;</td>";
    $_FORM["showProject"]         and $summary[] = "  <td>".$row["projectName"]."&nbsp;</td>";
    $_FORM["showProjectLink"]     and $summary[] = "  <td>".$row["projectLink"]."&nbsp;</td>";
    $_FORM["showPerson"]          and $summary[] = "  <td>".$row["person"]."&nbsp;</td>";
    $_FORM["showDateFrom"]        and $summary[] = "  <td>".$row["dateFrom"]."&nbsp;</td>";
    $_FORM["showDateTo"]          and $summary[] = "  <td>".$row["dateTo"]."&nbsp;</td>";
    $_FORM["showStatus"]          and $summary[] = "  <td>".$row["status"]."&nbsp;</td>";
    $_FORM["showDuration"]        and $summary[] = "  <td>".$row["duration"]."&nbsp;</td>";
    $_FORM["showAmount"]          and $summary[] = "  <td align=\"right\">".sprintf("$%0.2f",$row["amount"])."&nbsp;</td>";
    $_FORM["showCustomerBilledDollars"]  and $summary[] = "  <td align=\"right\">".sprintf("$%0.2f",$row["customerBilledDollars"])."&nbsp;</td>";
    $_FORM["showTransactionsPos"]  and $summary[] = "  <td align=\"right\">".sprintf("$%0.2f",$row["transactionsPos"])."&nbsp;</td>";
    $_FORM["showTransactionsNeg"]  and $summary[] = "  <td align=\"right\">".sprintf("$%0.2f",$row["transactionsNeg"])."&nbsp;</td>";
    $summary[] = "</tr>";
     
    $summary = "\n".implode("\n",$summary);
    return $summary;   
  } 

  function get_list_tr_bottom($row,$_FORM) {
    if ($_FORM["showAmountTotal"]) {
      $summary[] = "<tfoot>";
      $summary[] = "<tr>";
      $_FORM["showProject"]         and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showProjectLink"]     and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showPerson"]          and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showDateFrom"]        and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showDateTo"]          and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showStatus"]          and $summary[] = "  <td>&nbsp;</td>";
      $_FORM["showDuration"]        and $summary[] = "  <td class=\"grand_total left\">".sprintf("%0.2f", $row["totalHours"])." hours</td>";
      $_FORM["showAmountTotal"]     and $summary[] = "  <td class=\"grand_total right\">".sprintf("$%0.2f",$row["amountTotal"])."</td>";
      $_FORM["showCustomerBilledDollarsTotal"]     and $summary[] = "  <td class=\"grand_total right\">".sprintf("$%0.2f",$row["customerBilledDollarsTotal"])."</td>";
      $_FORM["showTransactionsPos"] and $summary[] = "  <td class=\"grand_total right\">".sprintf("$%0.2f",$row["transactionsPosTotal"])."</td>";
      $_FORM["showTransactionsNeg"] and $summary[] = "  <td class=\"grand_total right\">".sprintf("$%0.2f",$row["transactionsNegTotal"])."</td>";
      $summary[] = "</tr>";
      $summary[] = "</tfoot>";
      $summary = "\n".implode("\n",$summary);
    }
    return $summary;   
  } 

  function get_list_vars() {
    return array("return"                         => "[MANDATORY] eg: array | html"
                ,"projectID"                      => "Time Sheets that belong to this Project"
                ,"taskID"                         => "Time Sheets that use this task"
                ,"personID"                       => "Time Sheets for this person"
                ,"status"                         => "Time Sheet status eg: edit | manager | admin | invoiced | finished"
                ,"dateFrom"                       => "Time Sheets from a particular date"
                ,"dateTo"                         => "Time Sheets to a particular date"
                ,"url_form_action"                => "The submit action for the filter form"
                ,"form_name"                      => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"                       => "Specify that the filter preferences should not be saved this time"
                ,"applyFilter"                    => "Saves this filter as the persons preference"
                ,"showHeader"                     => "A descriptive html header row"
                ,"showProject"                    => "The Time Sheets Project"
                ,"showProjectLink"                => "Show a link to the Time Sheets Project"
                ,"showShortProjectLink"           => "Show short Project link"
                ,"showAmount"                     => "Show the total to the engineer of the time sheet"
                ,"showAmountTotal"                => "Put a footer row on the html showing the totals"
                ,"showCustomerBilledDollars"      => "Show the total that the customer is billed for this time sheet"
                ,"showCustomerBilledDollarsTotal" => "Put the grand total of customer billed in the footer"
                ,"showTransactionsPos"            => "Sum of transactions > 0 [OBSOLETE]"
                ,"showTransactionsPosTotal"       => "Put the grand total of sum transactions > 0 in the footer [OBSOLETE]"
                ,"showTransactionsNeg"            => "Sum of transactions < 0 [OBSOLETE]"
                ,"showTransactionsNegTotal"       => "Put the grand total of sum transactions < 0 in the footer [OBSOLETE]"
                ,"showDuration"                   => "The time length of the Time Sheet"
                ,"showPerson"                     => "The owner of the Time Sheet"
                ,"showDateFrom"                   => "The start date of the Time Sheet"
                ,"showDateTo"                     => "The end date of the Time Sheet"
                ,"showStatus"                     => "The Time Sheet status"
                );
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array_keys(timeSheet::get_list_vars());

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
    $query = sprintf("SELECT projectID AS value, projectName AS label FROM project ORDER by projectName");
    $rtn["show_project_options"] = page::select_options($query, $_FORM["projectID"],70);

    // display the list of user name.
    if (have_entity_perm("timeSheet", PERM_READ, $current_user, false)) {
      $rtn["show_userID_options"] = page::select_options(person::get_username_list(), $_FORM["personID"]);
      
    } else {
      $person = new person;
      $person->set_id($current_user->get_id());
      $person->select();
      $person_array = array($current_user->get_id()=>$person->get_username(1));
      $rtn["show_userID_options"] = page::select_options($person_array, $_FORM["personID"]);
    } 

    // display a list of status
    $status_array = timeSheet::get_timeSheet_statii();
    unset($status_array["create"]);

    $rtn["show_status_options"] = page::select_options($status_array, $_FORM["status"]);

    // display the date from filter value
    $rtn["dateFrom"] = $_FORM["dateFrom"];
    $rtn["dateTo"] = $_FORM["dateTo"];
    $rtn["userID"] = $current_user->get_id();

    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_invoice_link() {
    global $TPL;
    if (is_object($this) && $this->get_id()) {
      $db = new db_alloc();
      $db->query("SELECT invoice.* 
                    FROM invoiceItem 
               LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID 
                   WHERE timeSheetID = %s",$this->get_id());
      while ($row = $db->next_record()) { 
        $str.= $sp."<a href=\"".$TPL["url_alloc_invoice"]."invoiceID=".$row["invoiceID"]."\">".$row["invoiceNum"]."</a>";
        $sp = "&nbsp;&nbsp;";
      }
      return $str;
    }
  }

  function change_status($direction) {
    $info = $this->get_email_vars();
    if (is_array($info["projectManagers"]) && count($info["projectManagers"])) {
      $steps["forwards"]["edit"] = "manager";
      $steps["backwards"]["admin"] = "manager";
    } else {
      $steps["forwards"]["edit"] = "admin";
      $steps["backwards"]["admin"] = "edit";
    }
    $steps["forwards"][""] = "edit";
    $steps["forwards"]["manager"] = "admin";
    $steps["forwards"]["admin"] = "invoiced";
    $steps["forwards"]["invoiced"] = "finished";
    $steps["forwards"]["finished"] = "";
    $steps["backwards"]["finished"] = "invoiced";
    $steps["backwards"]["invoiced"] = "admin";
    $steps["backwards"]["manager"] = "edit";
    $status = $this->get_value("status");
    $newstatus = $steps[$direction][$status];
    if ($newstatus) {
      $m = $this->{"email_move_status_to_".$newstatus}($direction,$info);
      $this->save();
      if (is_array($m)) { 
        return implode("<br/>",$m);
      }
    }
  }

  function email_move_status_to_edit($direction,$info) { 
    // is possible to move backwards to "edit", from both "manager" and "admin"
    global $current_user;
    if ($direction == "backwards") {
      $email = array();
      $email["type"] = "timesheet_reject";
      $email["to"] = $info["timeSheet_personID_email"];
      $email["subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_timeSheetFromManager"), "timeSheet", $this->get_id());
      $email["body"] = <<<EOD
         To: {$info["timeSheet_personID_name"]}
 Time Sheet: {$info["url"]}
For Project: {$info["projectName"]}
Rejected By: {$info["people_cache"][$current_user->get_id()]["name"]}

EOD;
      $this->get_value("billingNote") 
      and $email["body"].= "Billing Note: ".$this->get_value("billingNote");
      $msg[] = $this->shootEmail($email);

      $this->set_value("dateSubmittedToAdmin", "");   
      $this->set_value("approvedByAdminPersonID", "");   
      $this->set_value("dateSubmittedToManager", "");     
      $this->set_value("approvedByManagerPersonID", "");   
    }
    $this->set_value("status", "edit");
    return $msg;
  }

  function email_move_status_to_manager($direction,$info) { 
    global $current_user;
    // Can get forwards to "manager" only from "edit"
    if ($direction == "forwards") {
      $this->set_value("dateSubmittedToManager", date("Y-m-d"));
      // Check for time overrun
      $overrun_tasks = array();
      $db = new db_alloc();
      $task_id_query = sprintf("SELECT DISTINCT taskID FROM timeSheetItem WHERE timeSheetID=%d ORDER BY dateTimeSheetItem, timeSheetItemID", $this->get_id());
      $db->query($task_id_query);
      while($db->next_record()) {
        $task = new task;
        $task->read_db_record($db, false);
        $task->select();
        if(floatval($task->get_value('timeEstimate')) > 0) {
          $total_billed_time = ($task->get_time_billed(false)) / 3600;
          if($total_billed_time > floatval($task->get_value('timeEstimate'))) {
            $overrun_tasks[] = sprintf(" * %d %s (estimated: %.02f hours, billed so far: %.02f hours)", $task->get_id(), $task->get_value('taskName'), floatval($task->get_value('timeEstimate')), $total_billed_time);
          }
        }
      }
      if(count($overrun_tasks)) {
        $overrun_notice = "\n\nThe following tasks billed on this timesheet have exceeded their time estimates:\n";
        $overrun_notice .= implode("\n", $overrun_tasks);
      }
      foreach ($info["projectManagers"] as $pm) {
        $email = array();
        $email["type"] = "timesheet_submit";
        $email["to"] = $info["people_cache"][$pm]["emailAddress"];
        $email["subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_timeSheetToManager"), "timeSheet", $this->get_id());
        $email["body"] = <<<EOD
  To Manager: {$info["people_cache"][$pm]["name"]}
  Time Sheet: {$info["url"]}
Submitted By: {$info["timeSheet_personID_name"]}
 For Project: {$info["projectName"]}

A timesheet has been submitted for your approval. If it is satisfactory,
submit the timesheet to the Administrator. If not, make it editable again for
re-submission.$overrun_notice

EOD;
        $this->get_value("billingNote") and 
        $email["body"].= "\n\nBilling Note: ".$this->get_value("billingNote");
        $msg[] = $this->shootEmail($email);
      }
    // Can get backwards to "manager" only from "admin"
    } else if ($direction == "backwards") {
      $email = array();
      $email["type"] = "timesheet_reject";
      $email["to"] = $info["approvedByManagerPersonID_email"];
      $email["subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_timeSheetFromAdministrator"), "timeSheet", $this->get_id());
      $email["body"] = <<<EOD
  To Manager: {$info["approvedByManagerPersonID_name"]}
  Time Sheet: {$info["url"]}
Submitted By: {$info["timeSheet_personID_name"]}
 For Project: {$info["projectName"]}
 Rejected By: {$info["people_cache"][$current_user->get_id()]["name"]}

EOD;
      $this->get_value("billingNote") 
      and $email["body"].= "Billing Note: ".$this->get_value("billingNote");
      $msg[] = $this->shootEmail($email);
    }
    $this->set_value("status", "manager");
    $this->set_value("dateSubmittedToAdmin", "");
    $this->set_value("approvedByAdminPersonID", "");
    return $msg;
  }

  function email_move_status_to_admin($direction,$info) { 
    global $current_user;
    // Can get forwards to "admin" from "edit" and "manager"
    if ($direction == "forwards") {
        if ($this->get_value("status") == "manager") { 
          $this->set_value("approvedByManagerPersonID",$current_user->get_id());
          $extra = " Approved By: ".person::get_fullname($current_user->get_id());
        }
        $this->set_value("status", "admin");
        $this->set_value("dateSubmittedToAdmin", date("Y-m-d"));
        foreach($info["timeSheetAdministrators"] as $adminID)  {
          $email = array();
          $email["type"] = "timesheet_submit";
          $email["to"] = $info["people_cache"][$adminID]["emailAddress"];
          $email["subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_timeSheetToAdministrator"), "timeSheet", $this->get_id());
          $email["body"] = <<<EOD
    To Admin: {$info["admin_name"]}
  Time Sheet: {$info["url"]}
Submitted By: {$info["timeSheet_personID_name"]}
 For Project: {$info["projectName"]}
{$extra}

A timesheet has been submitted for your approval. If it is not
satisfactory, make it editable again for re-submission.

EOD;
          $this->get_value("billingNote") 
          and $email["body"].= "Billing Note: ".$this->get_value("billingNote");
          $msg[] = $this->shootEmail($email);
        }

    // Can get backwards to "admin" from "invoiced" 
    } else {
      $this->set_value("approvedByAdminPersonID", "");
    }
    $this->set_value("status", "admin");
    return $msg;
  }

  function email_move_status_to_invoiced($direction,$info) { 
    global $current_user;
    // Can get forwards to "invoiced" from "admin" 
    if ($info["projectManagers"] 
    && !$this->get_value("approvedByManagerPersonID")) {
      $this->set_value("approvedByManagerPersonID", $current_user->get_id());
    }
    $this->set_value("approvedByAdminPersonID", $current_user->get_id());
    $this->set_value("status", "invoiced");
  }

  function email_move_status_to_finished($direction,$info) {
    if ($direction == "forwards") {
      //transactions
      $q = sprintf("SELECT DISTINCT transaction.transactionDate, transaction.product, transaction.status
                      FROM transaction
                      JOIN tf ON tf.tfID = transaction.tfID OR tf.tfID = transaction.fromTfID
                RIGHT JOIN tfPerson ON tfPerson.personID = %d AND tfPerson.tfID = tf.tfID
                     WHERE transaction.timeSheetID = %d
                   ", $this->get_value('personID'), $this->get_id());
      $db = new db_alloc();
      $db->query($q);

      //the email itself
      $email = array();
      $email["type"] = "timesheet_finished";
      $email["to"] = $info["timeSheet_personID_email"];
      $email["subject"] = commentTemplate::populate_string(config::get_config_item("emailSubject_timeSheetCompleted"), "timeSheet", $this->get_id());
      $email["body"] = <<<EOD
         To: {$info["timeSheet_personID_name"]}
 Time Sheet: {$info["url"]}
For Project: {$info["projectName"]}

Your timesheet has been completed by {$info["current_user_name"]}.

EOD;

      if($db->num_rows() > 0) {
        $email["body"] .= "Transaction summary:\n";
        $status_ops = array("pending" => "Pending", "approved" => "Approved", "rejected" => "Rejected");
        while($db->next_record()) {
          $email["body"] .= $db->f("transactionDate") . " for " . $db->f("product") . ": " . $status_ops[$db->f("status")] . "\n";
        }
      }
      $msg[] = $this->shootEmail($email);
      $this->set_value("status", "finished");
      return $msg;
    } 
  }

  function pending_transactions_to_approved() {
    $db = new db_alloc();
    $q = sprintf("UPDATE transaction SET status = 'approved' WHERE timeSheetID = %d AND status = 'pending'",$this->get_id());
    $db->query($q);
  }

  function get_email_vars() {
    global $current_user;
    static $rtn;
    if ($rtn) {
      return $rtn;
    }
    // Get vars for the emails below
    $rtn["people_cache"] = $people_cache = get_cached_table("person");
    $project = $this->get_foreign_object("project");
    $rtn["projectManagers"] = $project->get_timeSheetRecipients();
    $rtn["projectName"] = $project->get_value("projectName");
    $rtn["timeSheet_personID_email"] = $people_cache[$this->get_value("personID")]["emailAddress"];
    $rtn["timeSheet_personID_name"]  = $people_cache[$this->get_value("personID")]["name"];

    $config = new config;
    $rtn["url"] = $config->get_config_item("allocURL")."time/timeSheet.php?timeSheetID=".$this->get_id();

    $rtn["timeSheetAdministrators"] = $config->get_config_item('defaultTimeSheetAdminList');
    $rtn["approvedByManagerPersonID_email"] = $people_cache[$this->get_value("approvedByManagerPersonID")]["emailAddress"];
    $rtn["approvedByManagerPersonID_name"] = $people_cache[$this->get_value("approvedByManagerPersonID")]["name"];
    $rtn["approvedByAdminPersonID_name"] = $people_cache[$this->get_value("approvedByAdminPersonID")]["name"];
    $rtn["current_user_name"] = $people_cache[$current_user->get_id()]["name"];
    
    return $rtn;
  }

  function add_timeSheetItem_by_task($taskID, $duration, $comments) {
    global $current_user;

    $task = new task;
    $task->set_id($taskID);
    if ($task->select() && $task->get_value("projectID")) {
      $q = sprintf("SELECT * 
                      FROM timeSheet 
                     WHERE status = 'edit' 
                       AND projectID = %d
                       AND personID = %d
                ",$task->get_value("projectID"), $current_user->get_id());
      $db = new db_alloc();
      $db->query($q);
      $row = $db->row();

      // If no timeSheets add a new one
      if (!$row) {
        $project = new project();
        $project->set_id($task->get_value("projectID"));
        $project->select();

        $timeSheet = new timeSheet();
        $timeSheet->set_value("projectID",$task->get_value("projectID"));
        $timeSheet->set_value("dateFrom",date("Y-m-d"));
        $timeSheet->set_value("dateTo",date("Y-m-d"));
        $timeSheet->set_value("status","edit");
        $timeSheet->set_value("personID", $current_user->get_id());
        $timeSheet->set_value("recipient_tfID",$current_user->get_value("preferred_tfID"));
        $timeSheet->set_value("customerBilledDollars",$project->get_value("customerBilledDollars"));
        $timeSheet->save();
        $timeSheetID = $timeSheet->get_id();

      // Else use the first timesheet we found
      } else {
        $timeSheetID = $row["timeSheetID"];
      }   

      // Add new time sheet item
      if ($timeSheetID) {
        $row_projectPerson = projectPerson::get_projectPerson_row($task->get_value("projectID"), $current_user->get_id());

        $tsi = new timeSheetItem();
        $tsi->set_value("timeSheetID",$timeSheetID);
        $tsi->set_value("dateTimeSheetItem",date("Y-m-d"));
        $tsi->set_value("timeSheetItemDuration",$duration);
        $tsi->set_value("timeSheetItemDurationUnitID", $row_projectPerson["rateUnitID"]);
        $tsi->set_value("description",$task->get_value("taskName"));
        $tsi->set_value("personID",$current_user->get_id());
        $tsi->set_value("taskID",sprintf("%d",$taskID));
        $tsi->set_value("rate",$row_projectPerson["rate"]);
        $tsi->set_value("multiplier",1);
        $tsi->set_value("comment",$comments);
        $_POST["timeSheetItem_taskID"] = sprintf("%d",$taskID); // this gets used in timeSheetItem->save();
        $tsi->save();
      }
  
      return $timeSheetID;
    }
  }

  function get_all_timeSheet_parties() {
    $db = new db_alloc;
    $interestedPartyOptions = array();

    $projectID = $this->get_value("projectID");

    if ($projectID) {
      // Get primary client contact from Project page
      $q = sprintf("SELECT projectClientName,projectClientEMail FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $interestedPartyOptions[$db->f("projectClientEMail")] = array("name"=>$db->f("projectClientName"),"external"=>1);

      // Get all other client contacts from the Client pages for this Project
      $q = sprintf("SELECT clientID FROM project WHERE projectID = %d",$projectID);
      $db->query($q);
      $db->next_record();
      $clientID = $db->f("clientID");
      $q = sprintf("SELECT clientContactName, clientContactEmail, clientContactID 
                      FROM clientContact 
                     WHERE clientID = %d",$clientID);
      $db->query($q);
      while ($db->next_record()) {
        $interestedPartyOptions[$db->f("clientContactEmail")] = array("name"=>$db->f("clientContactName"),"external"=>1,"clientContactID"=>$db->f("clientContactID"));
      }
      // Get all the project people for this tasks project
      $q = sprintf("SELECT emailAddress, firstName, surname, person.personID
                     FROM projectPerson 
                LEFT JOIN person on projectPerson.personID = person.personID 
                    WHERE projectPerson.projectID = %d AND person.personActive = 1 ",$projectID);
      $db->query($q);
      while ($db->next_record()) {
        $interestedPartyOptions[$db->f("emailAddress")] = array("name"=>$db->f("firstName")." ".$db->f("surname"),"personID"=>$db->f("personID"));
      }
    }

    $extra_interested_parties = config::get_config_item("defaultInterestedParties") or $extra_interested_parties=array();
    foreach ($extra_interested_parties as $name => $email) {
      $interestedPartyOptions[$email] = array("name"=>$name);
    }

    if (is_object($this)) {
      if ($this->get_value("personID")) {
        $p = new person;
        $p->set_id($this->get_value("personID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"assignee", "selected"=>false, "personID"=>$this->get_value("personID"));
      }
      if ($this->get_value("approvedByManagerPersonID")) {
        $p = new person;
        $p->set_id($this->get_value("approvedByManagerPersonID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"manager", "selected"=>true, "personID"=>$this->get_value("approvedByManagerPersonID"));
      }
      $this_id = $this->get_id();
    }
    // return an aggregation of the current task/proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("timeSheet",$this_id,$interestedPartyOptions);
    return $interestedPartyOptions;
  }

  function send_emails($selected_option, $type="", $body="", $from=array()) {
    global $current_user;

    $recipients = comment::get_email_recipients($selected_option,$from);
    list($to_address,$bcc,$successful_recipients) = comment::get_email_recipient_headers($recipients, $from);

    if ($successful_recipients) {
      $email = new alloc_email();
      $bcc && $email->add_header("Bcc",$bcc);
      $from["references"] && $email->add_header("References",$from["references"]);
      $from["in-reply-to"] && $email->add_header("In-Reply-To",$from["in-reply-to"]);
      $email->set_to_address($to_address);

      $from_name = $from["name"] or $from_name = $current_user->get_username(1);

      $hash = $from["hash"];

      if ($hash && config::get_config_item("allocEmailKeyMethod") == "headers") {
        $email->set_message_id($hash);
      } else if ($hash && config::get_config_item("allocEmailKeyMethod") == "subject") {
        $email->set_message_id();
        $subject_extra = "{Key:".$hash."}";
      }

      $project = $this->get_foreign_object("project");

      $subject = "Time Sheet Comment: ".$this->get_id()." ".$project->get_project_name(1)." ".$subject_extra;
      $email->set_subject($subject);
      $email->set_body($body);
      $email->set_message_type($type);

      if (defined("ALLOC_DEFAULT_FROM_ADDRESS") && ALLOC_DEFAULT_FROM_ADDRESS) {
        $email->set_reply_to("All parties via ".ALLOC_DEFAULT_FROM_ADDRESS);
        $email->set_from($from_name." via ".ALLOC_DEFAULT_FROM_ADDRESS);
      } else {
        $f = $current_user->get_from() or $f = config::get_config_item("allocEmailAdmin");
        $email->set_reply_to($f);
        $email->set_from($f);
      }

      if ($from["commentID"]) {
        $files = get_attachments("comment",$from["commentID"]);
        if (is_array($files)) {
          foreach ($files as $file) {
            $email->add_attachment($file["path"]);
          }
        }
      }

      if ($email->send(false)) {
        return $successful_recipients;
      }
    }
  }

  function get_amount_allocated() {
    // Return total amount used and total amount allocated
    if (is_object($this) && $this->get_id()) {
      $db = new db_alloc();
      // Get most recent invoiceItem that this time sheet belongs to.
      $q = sprintf("SELECT invoiceID
                      FROM invoiceItem
                     WHERE invoiceItem.timeSheetID = %d
                  ORDER BY invoiceItem.iiDate DESC
                     LIMIT 1
                  ",$this->get_id());
      $db->query($q);
      $row = $db->row();
      $invoiceID = $row["invoiceID"];
      if ($invoiceID) {
        $invoice = new invoice;
        $invoice->set_id($invoiceID);
        $invoice->select();
        $maxAmount = $invoice->get_value("maxAmount");
      }
    
      // Loop through all the other invoice items on that invoice
      $q = sprintf("SELECT sum(iiAmount) AS totalUsed FROM invoiceItem WHERE invoiceID = %d",$invoiceID);
      $db->query($q);
      $row2 = $db->row();

      return array($row2["totalUsed"],$maxAmount);

    }
  }


}  




?>
