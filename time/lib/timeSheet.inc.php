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
			                       ,"dateRejected" => array("empty_to_null"=>true)
                             ,"billingNote"
                             ,"payment_insurance"
                             ,"recipient_tfID"
                             ,"customerBilledDollars" => array("type"=>"money")
                             ,"currencyTypeID"
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

    $project = $this->get_foreign_object("project");
    $managers = $project->get_timeSheetRecipients() or $managers = array();
    if (in_array($current_user->get_id(), $managers)) {
      return true;
    }

    if ($current_user->have_role("admin")) {
      return true;
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
     * $this->pay_info["currency"]; according to timeSheet table               *
     *                                                                         *
     ***************************************************************************/

    static $rates;
    unset($this->pay_info);
    $db = new db_alloc;

    if (!$this->get_value("projectID") || !$this->get_value("personID")) {
      return false;
    }
    $currency = $this->get_value("currencyTypeID");
    
    // The unit labels
    $timeUnit = new timeUnit;
    $units = array_reverse($timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA"),true);

    if ($rates[$this->get_value("projectID")][$this->get_value("personID")]) {
      list($this->pay_info["project_rate"],$this->pay_info["project_rateUnitID"]) = $rates[$this->get_value("projectID")][$this->get_value("personID")];
    } else {
      // Get rate for person for this particular project
      $db->query("SELECT rate, rateUnitID, project.currencyTypeID
                    FROM projectPerson
               LEFT JOIN project on projectPerson.projectID = project.projectID
                   WHERE projectPerson.projectID = %d
                     AND projectPerson.personID = %d"
                 ,$this->get_value("projectID")
                 ,$this->get_value("personID"));

      $db->next_record();
      $this->pay_info["project_rate"] = page::money($db->f("currencyTypeID"),$db->f("rate"),"%mo");
      $this->pay_info["project_rateUnitID"] = $db->f("rateUnitID");
      $rates[$this->get_value("projectID")][$this->get_value("personID")] = array($this->pay_info["project_rate"],$this->pay_info["project_rateUnitID"]);
    }

    // Get external rate, only load up customerBilledDollars if the field is actually set
    if (imp($this->get_value("customerBilledDollars"))) {
      $this->pay_info["customerBilledDollars"] = page::money($currency,$this->get_value("customerBilledDollars"),"%mo");
    }

    $q = "SELECT * FROM timeUnit ORDER BY timeUnitSequence DESC";
    $db->query($q);
    while ($row = $db->row()) { 
      if ($row["timeUnitSeconds"]) {
        $extra_sql[] = "SUM(IF(timeUnit.timeUnitLabelA = '".$row["timeUnitLabelA"]."',multiplier * timeSheetItemDuration * timeUnit.timeUnitSeconds,0)) /".$row["timeUnitSeconds"]." as ".$row["timeUnitLabelA"];
      }
      $timeUnitRows[] = $row;
    }

    $extra_sql and $sql = ",".implode("\n,",$extra_sql);

    // Get duration for this timesheet/timeSheetItems
    $db->query(sprintf("SELECT SUM(timeSheetItemDuration) AS total_duration, 
                               SUM((timeSheetItemDuration * timeUnit.timeUnitSeconds) / 3600) AS total_duration_hours,
                               SUM((rate * pow(10,-currencyType.numberToBasic)) * timeSheetItemDuration * multiplier) AS total_dollars,
                               SUM((IFNULL(timeSheet.customerBilledDollars,0) * pow(10,-currencyType.numberToBasic)) * timeSheetItemDuration * multiplier) AS total_customerBilledDollars
                               ".$sql."
                          FROM timeSheetItem
                     LEFT JOIN timeUnit ON timeUnit.timeUnitID = timeSheetItem.timeSheetItemDurationUnitID
                     LEFT JOIN timeSheet on timeSheet.timeSheetID = timeSheetItem.timeSheetID
                     LEFT JOIN currencyType on currencyType.currencyTypeID = timeSheet.currencyTypeID
                         WHERE timeSheetItem.timeSheetID = %d"
                       ,$this->get_id()));

    $row = $db->row();
    $this->pay_info = array_merge((array)$this->pay_info, (array)$row);
    $this->pay_info["total_customerBilledDollars"] = page::money($currency,$this->pay_info["total_customerBilledDollars"],"%m");
    $this->pay_info["total_dollars"] = page::money($currency,$this->pay_info["total_dollars"],"%m");

    unset($commar);
    foreach((array)$timeUnitRows as $r) {
      if ($row[$r["timeUnitLabelA"]]!=0) {
        $this->pay_info["summary_unit_totals"].= $commar.($row[$r["timeUnitLabelA"]] + 0)." ".$r["timeUnitLabelA"];
        $commar = ", ";
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
    $this->pay_info["total_dollars_minus_gst"] =               page::money($currency,$this->pay_info["total_dollars"] / $taxPercentDivisor,"%m");
    $this->pay_info["total_customerBilledDollars_minus_gst"] = page::money($currency,$this->pay_info["total_customerBilledDollars"] / $taxPercentDivisor,"%m");
    $this->pay_info["total_dollars_not_null"] = $this->pay_info["total_customerBilledDollars"] or $this->pay_info["total_dollars_not_null"] = $this->pay_info["total_dollars"];
    $this->pay_info["currency"] = page::money($currency,'',"%S");
  }

  function destroyTransactions() {
    $db = new db_alloc;
    $query = sprintf("DELETE FROM transaction where timeSheetID = %d", $this->get_id());
    $db->query($query);
    $db->next_record();
  }

  function createTransactions($status="pending") {

    // So this will only create transaction if:
    // - The timesheet status is admin
    // - There is a recipient_tfID - that is the money is going to a TF
    $db = new db_alloc;
    $project = $this->get_foreign_object("project");
    $projectName = $project->get_value("projectName");
    $personName = person::get_fullname($this->get_value("personID"));
    $company_tfID = config::get_config_item("mainTfID");
    $cost_centre = $project->get_value("cost_centre_tfID") or $cost_centre = $company_tfID;
    $this->fromTfID = $cost_centre;
    $this->load_pay_info();

    if ($this->get_value("status") != "invoiced") {
      return "ERROR: Status of the timesheet must be 'invoiced' to Create Transactions.  The status is currently: ".$this->get_value("status");

    } else if ($this->get_value("recipient_tfID") == "") {
      return "ERROR: There is no recipient Tagged Fund to credit for this timesheet. Go to Tools -> New Tagged Fund, add a new TF and add the owner. Then go to People -> Select the user and set their Preferred Payment TF.";

    } else if (!$cost_centre || $cost_centre == 0) {
      return "ERROR: There is no cost centre associated with the project.";

    } else {
      $taxName = config::get_config_item("taxName");
      $taxPercent = config::get_config_item("taxPercent");
      $taxTfID = config::get_config_item("taxTfID");
      $taxPercentDivisor = ($taxPercent/100) + 1;
      $payrollTaxPercent = config::get_config_item("payrollTaxPercent");
      $companyPercent = config::get_config_item("companyPercent");
      $paymentInsurancePercent = config::get_config_item("paymentInsurancePercent");
      $paymentInsurancePercent and $paymentInsurancePercentMult = ($paymentInsurancePercent/100);

      $recipient_tfID = $this->get_value("recipient_tfID");
      $timeSheetRecipients = $project->get_timeSheetRecipients();
      $insur_trans_status = $status;
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
          $product = "Agency Percentage ".$agency_percentage."% of ".$this->pay_info["currency"].$this->pay_info["total_dollars_minus_gst"]." for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet");
        }
        $percent = $companyPercent - $agency_percentage;
        $product = "Company ".$percent."% of ".$this->pay_info["currency"].$this->pay_info["total_dollars_minus_gst"]." for timesheet #".$this->get_id();
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
          $product = "Commission ".$db->f("commissionPercent")."% of ".$this->pay_info["currency"].$this->pay_info["total_dollars_minus_gst"];
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
          $product = "Commission ".sprintf("%0.3f",$percent)."% of ".$this->pay_info["currency"].$this->pay_info["total_dollars_minus_gst"];
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
        
        // 0. If this time sheet is not attached to an invoice, add an incoming transaction
        $rows = $this->get_invoice_rows();
        if (!$rows) {
          //$product = "Incoming funds for timesheet #".$this->get_id();
          $product = "Time Sheet #".$this->get_id()." for ".$personName.", Project: ".$projectName;
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars"], $cost_centre, "timesheet",$status,config::get_config_item("inTfID"));
        } else {
          foreach ($rows as $row) {
            if ($row["invoiceItemID"]) {
              $ii = new invoiceItem;
              $ii->set_id($row["invoiceItemID"]);
              $ii->select();
              $ii->create_transaction($this->pay_info["total_customerBilledDollars"],$cost_centre, $status);
            }
          }
        }

        // 1. Credit TAX/GST Cost Centre
        $product = $taxName." ".$taxPercent."% for timesheet #".$this->get_id();
        $rtn[$product] = $this->createTransaction($product, ($this->pay_info["total_customerBilledDollars"]-$this->pay_info["total_customerBilledDollars_minus_gst"]), $taxTfID, "tax", $status);

        // 2. Credit Cyber Percentage and do agency percentage if necessary
        $agency_percentage = 0;
        if ($project->get_value("is_agency") && $payrollTaxPercent > 0) {
          $agency_percentage = $payrollTaxPercent;
          $product = "Agency Percentage ".$agency_percentage."% of ".$this->pay_info["currency"].$this->pay_info["total_customerBilledDollars_minus_gst"]." for timesheet #".$this->get_id();
          $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($agency_percentage/100), $recipient_tfID, "timesheet", $status);
        }

        // 3. We only do the companies cut, if the project has a dedicated fund, otherwise we just omit the companies cut
        if ($project->get_value("cost_centre_tfID") && $project->get_value("cost_centre_tfID") != $company_tfID) {
          $percent = $companyPercent - $agency_percentage;
          $product = "Company ".$percent."% of ".$this->pay_info["currency"].$this->pay_info["total_customerBilledDollars_minus_gst"]." for timesheet #".$this->get_id();
          $percent and $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_customerBilledDollars_minus_gst"]*($percent/100), $company_tfID, "timesheet",$status,$project->get_value("cost_centre_tfID"));
        }

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
            $product = "Commission ".$db->f("commissionPercent")."% of ".$this->pay_info["currency"].$this->pay_info["total_customerBilledDollars_minus_gst"];
            $product.= " from timesheet #".$this->get_id().".  Project: ".$projectName;
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission",$status);

          // Suck up the rest of funds if it is a special zero % commission
          } else if ($db->f("commissionPercent") == 0) { 
            $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_positive_amount_so_far_minus_insurance();
            $amount < 0 and $amount = 0;
            config::for_cyber() and $amount = $amount/2; // If it's cyber do a 50/50 split with the commission tf and the company
            $product = "Commission Remaining from timesheet #".$this->get_id().".  Project: ".$projectName;
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
            config::for_cyber() and $rtn[$product] = $this->createTransaction($product, $amount, $company_tfID, "commission",$status); // 50/50
          }

        }
      }

      foreach($rtn as $error=>$v) {
        $v != 1 and $errmsg.= "<br>FAILED: ".$error;
      }
      if ($errmsg) {
        $this->destroyTransactions();
        $rtnmsg.= "<br>Failed to create transactions... ".$errmsg;
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
      $transaction->set_value("amount", $amount);
      $transaction->set_value("status", $status);
      $transaction->set_value("fromTfID", $fromTfID);
      $transaction->set_value("tfID", $tfID);
      $transaction->set_value("transactionDate", date("Y-m-d"));
      $transaction->set_value("transactionType", $transactionType);
      $transaction->set_value("timeSheetID", $this->get_id());
      $transaction->set_value("currencyTypeID", $this->get_value("currencyTypeID"));
      $transaction->save();
      return 1;
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
    $options["return"] = "array";
    $options["taskTimeSheetStatus"] = $status;
    $taskrows = task::get_list($options);
    foreach ((array)$taskrows as $tid => $row) {
      $tasks[$tid] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$row["padding"]).$tid." ".$row["taskName"];
    }

    if ($taskID) {
      $t = new task;
      $t->set_id($taskID);
      $t->select();
      $tasks[$taskID] = $t->get_id()." ".$t->get_name();
    }

    $dropdown_options = page::select_options((array)$tasks, $taskID, 100);
    return "<select name=\"timeSheetItem_taskID\" style=\"width:400px\"><option value=\"\">".$dropdown_options."</select>";
  }

  function get_list_filter($filter=array()) {
    if ($filter["timeSheetID"]) {
      $sql[] = sprintf("(timeSheet.timeSheetID = '%d')", $filter["timeSheetID"]);
    }
    if ($filter["tfID"]) {
      $sql[] = sprintf("(timeSheet.recipient_tfID = '%d')", $filter["tfID"]);
    }
    if ($filter["projectID"]) {
      $sql[] = sprintf("(timeSheet.projectID = '%d')", $filter["projectID"]);
    }
    if ($filter["taskID"]) {
      $sql[] = sprintf("(timeSheetItem.taskID = '%d')", $filter["taskID"]);
    }
    if ($filter["personID"]) {
      $sql[] = sprintf("(timeSheet.personID = '%d')", $filter["personID"]);
    }
    if ($filter["status"]) { 
      if (is_array($filter["status"]) && count($filter["status"])) {
        foreach ($filter["status"] as $s) {
          if ($s == "rejected") {
            $rejected = true;
          } else {
            $statuses[] = db_esc($s);
          }
        }
      } else {
        if ($filter["status"] == "rejected") {
          $rejected = true;
        } else {
          $statuses[] = db_esc($filter["status"]);
        }
      }
    }

    if ($rejected) {
      $sql[] = sprintf("(timeSheet.dateRejected IS NOT NULL OR timeSheet.status in ('%s'))", implode("','",$statuses));
    } else if ($statuses) {
      $sql[] = sprintf("(timeSheet.dateRejected IS NULL AND timeSheet.status in ('%s'))", implode("','",$statuses));
    }

    if ($filter["dateFrom"]) {
      in_array($filter["dateFromComparator"],array("=","!=",">",">=","<","<=")) or $filter["dateFromComparator"] = '=';
      $sql[] = sprintf("(timeSheet.dateFrom %s '%s')",$filter['dateFromComparator'],db_esc($filter["dateFrom"]));
    }
    if ($filter["dateTo"]) {
      in_array($filter["dateToComparator"],array("=","!=",">",">=","<","<=")) or $filter["dateToComparator"] = '=';
      $sql[] = sprintf("(timeSheet.dateTo %s '%s')",$filter['dateToComparator'],db_esc($filter["dateTo"]));
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

    // Used in timeSheetListS.tpl
    $extra["showFinances"] = $_FORM["showFinances"];

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";

    $_FORM["return"] or $_FORM["return"] = "html";

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
      $t = new timeSheet;
      if (!$t->read_db_record($db,false))
        continue;

      $t->load_pay_info();

      if ($_FORM["timeSheetItemHours"] && !parse_operator_comparison($_FORM["timeSheetItemHours"],$t->pay_info["total_duration_hours"])) 
        continue;

      $row["currencyTypeID"] = $t->get_value("currencyTypeID");
      $row["amount"] = $t->pay_info["total_dollars"];
      $amount_tallies[] = array("amount"=>$row["amount"],"currency"=>$row["currencyTypeID"]);
      $extra["amountTotal"] += exchangeRate::convert($row["currencyTypeID"],$row["amount"]);
      $extra["totalHours"] += $t->pay_info["total_duration_hours"];
      $row["totalHours"] += $t->pay_info["total_duration_hours"];
      $row["duration"] = $t->pay_info["summary_unit_totals"];
      $row["person"] = $people_array[$row["personID"]]["name"];
      $row["status"] = $status_array[$row["status"]];
      $row["customerBilledDollars"] = $t->pay_info["total_customerBilledDollars"];
      $extra["customerBilledDollarsTotal"] += exchangeRate::convert($row["currencyTypeID"],$t->pay_info["total_customerBilledDollars"]);
      $billed_tallies[] = array("amount"=>$row["customerBilledDollars"],"currency"=>$row["currencyTypeID"]);

      if ($_FORM["showFinances"]) {
        list($pos,$neg) = $t->get_transaction_totals();
        $row["transactionsPos"] = page::money_print($pos);
        $row["transactionsNeg"] = page::money_print($neg);
        foreach ((array)$pos as $v) {
          $pos_tallies[] = $v;
        }
        foreach ((array)$neg as $v) {
          $neg_tallies[] = $v;
        }
      }

      $p = new project();
      $p->read_db_record($db);
      $row["projectLink"] = $t->get_link($p->get_name($_FORM));
      $rows[$row["timeSheetID"]] = $row;
    }

    $extra["amount_tallies"] = page::money_print($amount_tallies);
    $extra["billed_tallies"] = page::money_print($billed_tallies);
    $extra["positive_tallies"] = page::money_print($pos_tallies);
    $extra["negative_tallies"] = page::money_print($neg_tallies);

    return array("rows"=>(array)$rows,"extra"=>$extra);
  }

  function get_list_html($rows=array(),$extra=array()) {
    global $TPL;
    $TPL["timeSheetListRows"] = $rows;
    $TPL["extra"] = $extra;
    include_template(dirname(__FILE__)."/../templates/timeSheetListS.tpl");
  }

  function get_transaction_totals() {
  
    $db = new db_alloc();
    $q = sprintf("SELECT amount * pow(10,-currencyType.numberToBasic) AS amount,
                         transaction.currencyTypeID as currency
                    FROM transaction 
               LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                   WHERE status = 'approved' 
                     AND timeSheetID = %d
                 ",$this->get_id());
    $db->query($q);
    while($row = $db->row()) {
      if ($row["amount"] > 0) {
        $pos[] = $row;
      } else {
        $neg[] = $row;
      }
    }

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

  function get_list_vars() {
    return array("return"                         => "[MANDATORY] eg: array | html"
                ,"timeSheetID"                    => "Time Sheet that has this ID"
                ,"projectID"                      => "Time Sheets that belong to this Project"
                ,"taskID"                         => "Time Sheets that use this task"
                ,"personID"                       => "Time Sheets for this person"
                ,"status"                         => "Time Sheet status eg: edit | manager | admin | invoiced | finished"
                ,"dateFrom"                       => "Time Sheets from a particular date"
                ,"dateFromComparator"             => "The comparison operator: >, >=, <, <=, =, !="
                ,"dateTo"                         => "Time Sheets to a particular date"
                ,"dateToComparator"               => "The comparison operator: >, >=, <, <=, =, !="
                ,"timeSheetItemHours"             => "Time Sheets that have a certain amount of hours billed eg: '>7 AND <10 OR =4 AND !=8'"
                ,"url_form_action"                => "The submit action for the filter form"
                ,"form_name"                      => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"                       => "Specify that the filter preferences should not be saved this time"
                ,"applyFilter"                    => "Saves this filter as the persons preference"
                ,"showShortProjectLink"           => "Show short Project link"
                ,"showFinances"                   => "Shortcut for displaying the transactions and the totals"
                ,"tfID"                           => "Time sheets that belong to this TF"
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
      $person_array = array($current_user->get_id()=>$person->get_name());
      $rtn["show_userID_options"] = page::select_options($person_array, $_FORM["personID"]);
    } 

    // display a list of status
    $status_array = timeSheet::get_timeSheet_statii();
    unset($status_array["create"]);
    $status_array["rejected"] = 'Rejected';

    if (!$_FORM["status"]) {
      $_FORM["status"][] = 'edit';
    }

    foreach ($status_array as $k=>$v) {
      $rtn["show_status_options"].= $br.$v."<input type='checkbox' name='status[]' value='".$k."'".(in_array($k,$_FORM["status"])?" checked" : "").">";
      $br="&nbsp;&nbsp;&nbsp;";
    }

    // display the date from filter value
    $rtn["dateFrom"] = $_FORM["dateFrom"];
    $rtn["dateTo"] = $_FORM["dateTo"];
    $rtn["userID"] = $current_user->get_id();
    $rtn["showFinances"] = $_FORM["showFinances"];

    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_invoice_link() {
    global $TPL;
    $rows = $this->get_invoice_rows();
    foreach ($rows as $row) {
      $str.= $sp."<a href=\"".$TPL["url_alloc_invoice"]."invoiceID=".$row["invoiceID"]."\">".$row["invoiceNum"]."</a>";
      $sp = "&nbsp;&nbsp;";
    }
    return $str;
  }

  function get_invoice_rows() {
    if (is_object($this) && $this->get_id()) {
      $db = new db_alloc();
      $db->query("SELECT invoice.*, invoiceItemID
                    FROM invoiceItem 
               LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID 
                   WHERE timeSheetID = %s ORDER BY iiDate DESC",$this->get_id());
      while ($row = $db->row()) { 
        $rows[] = $row;
      }
    }
    return (array)$rows;
  }

  function change_status($direction) {
    // access controls are partially disabled for timesheets. Make sure time sheet is really accessible by checking
    // user ID - it's restricted to being NOT NULL in the DB. Not doing this check allows a user to overwrite
    // an existing timesheet with a new one assigned to themself.

    if (!$this->get_value("personID")) {
      die("You do not have access to this timesheet.");
    }

    $project = $this->get_foreign_object("project");
    $info["projectManagers"] = $project->get_timeSheetRecipients();

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
        return implode("<br>",$m);
      }
    }
  }

  function email_move_status_to_edit($direction,$info) { 
    // is possible to move backwards to "edit", from both "manager" and "admin"
    // requires manager or APPROVE_TIMESHEET permission
    global $current_user;
    if ($direction == "backwards") {
      if (!in_array($current_user->get_id(), $info["projectManagers"]) &&
        !$this->have_perm(PERM_TIME_APPROVE_TIMESHEETS)) {
          //error, go away
          die("You do not have permission to change this timesheet.");
      }
      $this->set_value("dateSubmittedToAdmin", "");   
      $this->set_value("approvedByAdminPersonID", "");   
      $this->set_value("dateSubmittedToManager", "");     
      $this->set_value("approvedByManagerPersonID", "");   
      $this->set_value("dateRejected", date("Y-m-d"));
    }
    $this->set_value("status", "edit");
    return $msg;
  }

  function email_move_status_to_manager($direction,$info) { 
    global $current_user;
    // Can get forwards to "manager" only from "edit"
    if ($direction == "forwards") {
      //forward to manager requires the timesheet to be owned by the current 
      //user or TIME_INVOICE_TIMESHEETS
      //project managers may not do this
      if (!($this->get_value("personID") == $current_user->get_id() || $this->have_perm(PERM_TIME_INVOICE_TIMESHEETS))) {
        die("You do not have permission to change this timesheet.");
      }
      $this->set_value("dateSubmittedToManager", date("Y-m-d"));
      $this->set_value("dateRejected", "");

    // Can get backwards to "manager" only from "admin"
    } else if ($direction == "backwards") {
      //admin->manager requires APPROVE_TIMESHEETS
      if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
        die("You do not have permission to change this timesheet.");
      }
      $this->set_value("dateRejected", date("Y-m-d"));
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
      //3 ways to have permission to do this
      //project manager for the timesheet
      //no project manager and owner of the timesheet
      //the permission flag
      if (!(in_array($current_user->get_id(), $info["projectManagers"]) || 
        (empty($info["projectManagers"]) && $this->get_value("personID") == $current_user->get_id()) ||
        $this->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {
          //error, go away
        die("You do not have permission to change this timesheet.");
      }

      if ($this->get_value("status") == "manager") { 
        $this->set_value("approvedByManagerPersonID",$current_user->get_id());
      }
      $this->set_value("status", "admin");
      $this->set_value("dateSubmittedToAdmin", date("Y-m-d"));
      $this->set_value("dateRejected", "");

    // Can get backwards to "admin" from "invoiced" 
    } else {
      //requires INVOICE_TIMESHEETS
      if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
        die("You do not have permission to change this timesheet.");
      }
      $this->set_value("approvedByAdminPersonID", "");
    }
    $this->set_value("status", "admin");
    return $msg;
  }

  function email_move_status_to_invoiced($direction,$info) { 
    global $current_user;
    // Can get forwards to "invoiced" from "admin" 
    // requires INVOICE_TIMESHEETS
    if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
      die("You do not have permission to change this timesheet.");
    }

    if ($info["projectManagers"] 
    && !$this->get_value("approvedByManagerPersonID")) {
      $this->set_value("approvedByManagerPersonID", $current_user->get_id());
    }
    $this->set_value("approvedByAdminPersonID", $current_user->get_id());
    $this->set_value("status", "invoiced");
  }

  function email_move_status_to_finished($direction,$info) {
    if ($direction == "forwards") {
      //requires INVOICE_TIMESHEETS
      if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
        die("You do not have permission to change this timesheet.");
      }
      $this->set_value("status", "finished");
      return $msg;
    } 
  }

  function pending_transactions_to_approved() {
    if (!$this->have_perm(PERM_TIME_APPROVE_TIMESHEETS)) {
      //no permission, die
      die("You do not have permission to approve transactions for this timesheet.");
    }

    $db = new db_alloc();
    $q = sprintf("UPDATE transaction SET status = 'approved' WHERE timeSheetID = %d AND status = 'pending'",$this->get_id());
    $db->query($q);
  }

  function add_timeSheetItem_by_project($projectID, $duration, $comments, $emailUID=null, $date=null) {
    global $current_user;
    return timeSheet::add_timeSheetItem_by_task(null, $duration, $comments, $emailUID, $date, $projectID);
  }

  function add_timeSheetItem_by_task($taskID=null, $duration, $comments, $emailUID=null, $date=null, $projectID=null) {
    global $current_user;

    if ($taskID) {
      $task = new task;
      $task->set_id($taskID);
      $task->select();
      $projectID = $task->get_value("projectID");
    }

    $projectID or $err[] = "No project found.";

    if ($projectID) {
      $q = sprintf("SELECT * 
                      FROM timeSheet 
                     WHERE status = 'edit' 
                       AND projectID = %d
                       AND personID = %d
                  ORDER BY dateFrom
                     LIMIT 1
                ",$projectID, $current_user->get_id());
      $db = new db_alloc();
      $db->query($q);
      $row = $db->row();

      // If no timeSheets add a new one
      if (!$row) {
        $project = new project();
        $project->set_id($projectID);
        $project->select();

        $timeSheet = new timeSheet();
        $timeSheet->set_value("projectID",$projectID);
        $timeSheet->set_value("dateFrom",date("Y-m-d"));
        $timeSheet->set_value("dateTo",date("Y-m-d"));
        $timeSheet->set_value("status","edit");
        $timeSheet->set_value("personID", $current_user->get_id());
        $timeSheet->set_value("recipient_tfID",$current_user->get_value("preferred_tfID"));
        $timeSheet->set_value("customerBilledDollars",page::money($project->get_value("currencyTypeID"),$project->get_value("customerBilledDollars"),"%mo"));
        $timeSheet->set_value("currencyTypeID",$project->get_value("currencyTypeID"));
        $timeSheet->save();
        $timeSheetID = $timeSheet->get_id();

      // Else use the first timesheet we found
      } else {
        $timeSheetID = $row["timeSheetID"];
      }   

      $timeSheetID or $err[] = "Couldn't find or create a Time Sheet.";

      // Add new time sheet item
      if ($timeSheetID) {
        $timeSheet = new timeSheet();
        $timeSheet->set_id($timeSheetID);
        $timeSheet->select();

        $row_projectPerson = projectPerson::get_projectPerson_row($projectID, $current_user->get_id());
        $row_projectPerson or $err[] = "The person has not been added to the project.";

        $tsi = new timeSheetItem();
        $tsi->currency = $timeSheet->get_value("currencyTypeID");
        $tsi->set_value("timeSheetID",$timeSheetID);
        $d = $date or $d = date("Y-m-d");
        $tsi->set_value("dateTimeSheetItem",$d);
        $tsi->set_value("timeSheetItemDuration",$duration);
        $tsi->set_value("timeSheetItemDurationUnitID", $row_projectPerson["rateUnitID"]);
        if (is_object($task)) {
          $tsi->set_value("description",$task->get_name());
          $tsi->set_value("taskID",sprintf("%d",$taskID));
          $_POST["timeSheetItem_taskID"] = sprintf("%d",$taskID); // this gets used in timeSheetItem->save();
        }
        $tsi->set_value("personID",$current_user->get_id());
        $tsi->set_value("rate",$row_projectPerson["rate"]);
        $tsi->set_value("multiplier",1);
        $tsi->set_value("comment",$comments);
        $tsi->set_value("emailUID",$emailUID);
        $str = $tsi->save();
        $str and $err[] = $str;
        $id = $tsi->get_id();

        $tsi = new timeSheetItem();
        $tsi->set_id($id);
        $tsi->select();
        $rtn = $tsi->row();
      }
    }

    if (!$err && $rtn["timeSheetID"]) {
      return array("status"=>"yay","message"=>$rtn["timeSheetID"]);
    } else {
      $rtn["timeSheetID"] or $err[] = "Time not added.";
      return array("status"=>"err","message"=>implode(" ",(array)$err));
    }
  }

  function get_all_parties($projectID="") {
    $db = new db_alloc;
    $interestedPartyOptions = array();

    if (!$projectID && is_object($this)) {
      $projectID = $this->get_value("projectID");
    }

    if ($projectID) {
      $interestedPartyOptions = project::get_all_parties($projectID);
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

  function get_amount_allocated($fmt="%s%mo") {
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
        $maxAmount = page::money($invoice->get_value("currencyTypeID"),$invoice->get_value("maxAmount"),$fmt);
    
        // Loop through all the other invoice items on that invoice
        $q = sprintf("SELECT sum(iiAmount) AS totalUsed FROM invoiceItem WHERE invoiceID = %d",$invoiceID);
        $db->query($q);
        $row2 = $db->row();

        return array(page::money($invoice->get_value("currencyTypeID"),$row2["totalUsed"],$fmt),$maxAmount);

      }
    }
  }

  function has_attachment_permission() {
    return $this->is_owner();
  }

  function get_name($_FORM=array()) {
    $project = new project();
    $project->set_id($this->get_value("projectID"));
    $project->select();
    $p = get_cached_table("person");
    return "Time Sheet for ".$project->get_name($_FORM)." by ".$p[$this->get_value("personID")]["name"];
  }

  function update_search_index_doc(&$index) {
    $p = get_cached_table("person");
    $personID = $this->get_value("personID");
    $person_field = $personID." ".$p[$personID]["username"]." ".$p[$personID]["name"];
    $managerID = $this->get_value("approvedByManagerPersonID");
    $manager_field = $managerID." ".$p[$managerID]["username"]." ".$p[$managerID]["name"];
    $adminID = $this->get_value("approvedByAdminPersonID");
    $admin_field = $adminID." ".$p[$adminID]["username"]." ".$p[$adminID]["name"];
    $tf_field = $this->get_value("recipient_tfID")." ".tf::get_name($this->get_value("recipient_tfID"));

    if ($this->get_value("projectID")) {
      $project = new project();
      $project->set_id($this->get_value("projectID"));
      $project->select();
      $projectName = $project->get_name();
      $projectShortName = $project->get_name(array("showShortProjectLink"=>true));
      $projectShortName && $projectShortName != $projectName and $projectName.= " ".$projectShortName;
    }

    $q = sprintf("SELECT dateTimeSheetItem, taskID, description, comment, commentPrivate 
                    FROM timeSheetItem 
                   WHERE timeSheetID = %d 
                ORDER BY dateTimeSheetItem ASC",$this->get_id());
    $db = new db_alloc;
    $db->query($q);
    while ($r = $db->row()) {
      $desc.= $br.$r["dateTimeSheetItem"]." ".$r["taskID"]." ".$r["description"]."\n";
      $r["comment"] && $r["commentPrivate"] or $desc.= $r["comment"]."\n";
      $br = "\n";
    }

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('project' ,$projectName));
    $doc->addField(Zend_Search_Lucene_Field::Text('pid'     ,$this->get_value("projectID")));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$person_field));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$desc));
    $doc->addField(Zend_Search_Lucene_Field::Text('status'  ,$this->get_value("status"))); 
    $doc->addField(Zend_Search_Lucene_Field::Text('tf'      ,$tf_field)); 
    $doc->addField(Zend_Search_Lucene_Field::Text('insurance',sprintf("%d",$this->get_value("payment_insurance")))); 
    $doc->addField(Zend_Search_Lucene_Field::Text('manager' ,$manager_field)); 
    $doc->addField(Zend_Search_Lucene_Field::Text('admin'   ,$admin_field)); 
    $doc->addField(Zend_Search_Lucene_Field::Text('dateManager',str_replace("-","",$this->get_value("dateSubmittedToManager"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateAdmin',str_replace("-","",$this->get_value("dateSubmittedToAdmin"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateFrom',str_replace("-","",$this->get_value("dateFrom"))));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTo'  ,str_replace("-","",$this->get_value("dateTo"))));
    $index->addDocument($doc);
  }

  function can_edit_rate() {
    global $current_user;

    $can_edit = config::get_config_item("timeSheetEditors");

    // If everyone can edit the rate
    if ($can_edit == "all") {
      return true;
    }

    // If the person is not on the project, then false
    $projectPerson = projectPerson::get_projectPerson_row($this->get_value("projectID"), $this->get_value("personID"));
    if (!$projectPerson) {
      return false;
    }

    // If the rate is not set
    if ($projectPerson['rate'] === "") {
      return true;
    }

    $project = $this->get_foreign_object('project');

    // If rates can be edited by managers and the current user is a manager
    if ($can_edit == "managers" && 
      ($current_user->have_role("manage") || $project->has_project_permission("", array("isManager")))) {
        return true;
    }

    // If the values can be edited provided they're blank at the project level
    if ($can_edit == "none" && $projectPerson['rate'] === "") {
      return true;
    }

    // Fallback since finance admins should be able to do anything
    if ($current_user->have_role("admin") || $current_user->have_role("god")) {
      return true;
    }

    return false;
  }

}  




?>
