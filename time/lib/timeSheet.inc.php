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
                             ,"recipient_tfID"
                             ,"customerBilledDollars" => array("type"=>"money")
                             ,"currencyTypeID"
                             );
  public $permissions = array(PERM_TIME_APPROVE_TIMESHEETS => "approve"
                             ,PERM_TIME_INVOICE_TIMESHEETS => "invoice");

  function is_owner() {
    $current_user = &singleton("current_user");

    if (!$this->get_id()) {
      return true;
    }

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
      $q = prepare("SELECT * FROM transaction WHERE timeSheetID = %d",$this->get_id());
      $db = new db_alloc();
      $db->query($q);
      while ($db->next_record()) {
        if (is_array($current_user_tfIDs) && (in_array($db->f("tfID"),$current_user_tfIDs) || in_array($db->f("fromTfID"),$current_user_tfIDs))) {
          return true;
        }
      }
    }
  }

  function get_timeSheet_statii() {
    return array("edit"      => "Add Time"
                ,"manager"   => "Manager"
                ,"admin"     => "Administrator"
                ,"invoiced"  => "Invoice"
                ,"finished"  => "Completed"
                );
  } 

  function get_timeSheet_status() {
    $statii = $this->get_timeSheet_statii();
    return $statii[$this->get_value("status")];
  }

  function load_pay_info() {

    /***************************************************************************
     *                                                                         *
     * load_pay_info() loads these vars:                                       *
     * $this->pay_info["project_rate"];	    according to projectPerson table   *
     * $this->pay_info["project_rate_orig"];before the currency transform      *
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
    $db = new db_alloc();

    if (!$this->get_value("projectID") || !$this->get_value("personID")) {
      return false;
    }
    $currency = $this->get_value("currencyTypeID");
    
    // The unit labels
    $timeUnit = new timeUnit();
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
    $db->query(prepare("SELECT SUM(timeSheetItemDuration) AS total_duration, 
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
    $db = new db_alloc();
    $query = prepare("DELETE FROM transaction WHERE timeSheetID = %d AND transactionType != 'invoice'", $this->get_id());
    $db->query($query);
    $db->next_record();
  }

  function createTransactions($status="pending") {

    // So this will only create transaction if:
    // - The timesheet status is admin
    // - There is a recipient_tfID - that is the money is going to a TF
    $db = new db_alloc();
    $project = $this->get_foreign_object("project");
    $projectName = $project->get_value("projectName");
    $personName = person::get_fullname($this->get_value("personID"));
    $company_tfID = config::get_config_item("mainTfID");
    $cost_centre = $project->get_value("cost_centre_tfID") or $cost_centre = $company_tfID;
    $this->fromTfID = $cost_centre;
    $this->load_pay_info();

    if ($this->get_value("status") != "invoiced") {
      return "ERROR: Status of the timesheet must be 'invoiced' to Create Transactions.
              The status is currently: ".$this->get_value("status");

    } else if ($this->get_value("recipient_tfID") == "") {
      return "ERROR: There is no recipient Tagged Fund to credit for this timesheet.
              Go to Tools -> New Tagged Fund, add a new TF and add the owner. Then go
              to People -> Select the user and set their Preferred Payment TF.";

    } else if (!$cost_centre || $cost_centre == 0) {
      return "ERROR: There is no cost centre associated with the project.";
    }

    $taxName = config::get_config_item("taxName");
    $taxPercent = config::get_config_item("taxPercent");
    $taxTfID = config::get_config_item("taxTfID");
    $taxPercentDivisor = ($taxPercent/100) + 1;
    $recipient_tfID = $this->get_value("recipient_tfID");
    $timeSheetRecipients = $project->get_timeSheetRecipients();
    
    $rtn = array();

    // This is just for internal transactions
    if ($_POST["create_transactions_default"] && $this->pay_info["total_customerBilledDollars"] == 0) {
      $this->pay_info["total_customerBilledDollars_minus_gst"] = $this->pay_info["total_dollars"];

      // 1. Credit Employee TF
      $product = "Timesheet #".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
      $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $status);

      // 2. Payment Insurance
      // removed
      

    } else if ($_POST["create_transactions_default"]) {
      /*  This was previously named "Simple" transactions. Ho ho.
          On the Project page we care about these following variables:
           - Client Billed At $amount eg: $121
           - The projectPersons rate for this project eg: $50;

          $121 after gst == $110
          cyber get 28.5% of $110 
          djk get $50
          commissions 
          whatever is left of the $110 goes to the 0% commissions
      */
      
      // 1. Credit TAX/GST Cost Centre
      $product = $taxName." ".$taxPercent."% for timesheet #".$this->get_id();
      $rtn[$product] = $this->createTransaction($product, ($this->pay_info["total_customerBilledDollars"]-$this->pay_info["total_customerBilledDollars_minus_gst"]), $taxTfID, "tax", $status);

      // 3. Credit Employee TF
      $product = "Timesheet #".$this->get_id()." for ".$projectName." (".$this->pay_info["summary_unit_totals"].")";
      $rtn[$product] = $this->createTransaction($product, $this->pay_info["total_dollars"], $recipient_tfID, "timesheet", $status);

      // 4. Credit Project Commissions
      $db->query("SELECT * FROM projectCommissionPerson where projectID = %d ORDER BY commissionPercent DESC"
                 ,$this->get_value("projectID"));

      while ($db->next_record()) {
        if ($db->f("commissionPercent") > 0) { 
          $product = "Commission ".$db->f("commissionPercent")."% of ".$this->pay_info["currency"].$this->pay_info["total_customerBilledDollars_minus_gst"];
          $product.= " from timesheet #".$this->get_id().".  Project: ".$projectName;
          $amount = $this->pay_info["total_customerBilledDollars_minus_gst"]*($db->f("commissionPercent")/100);
          $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission",$status);

        // Suck up the rest of funds if it is a special zero % commission
        } else if ($db->f("commissionPercent") == 0) { 
          $amount = $this->pay_info["total_customerBilledDollars_minus_gst"] - $this->get_amount_so_far();
          $amount < 0 and $amount = 0;

          // If the 0% commission is for the company tf, dump it in the company tf
          if ($db->f("tfID") == $company_tfID) {
            $product = "Commission Remaining from timesheet #".$this->get_id().".  Project: ".$projectName;
            $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
          } else {

            // If it's cyber do a 50/50 split with the commission tf and the company
            if (config::for_cyber()) {
              $amount = $amount/2;
              $product = "Commission Remaining from timesheet #".$this->get_id().".  Project: ".$projectName;
              $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
              $rtn[$product] = $this->createTransaction($product, $amount, $company_tfID, "commission",$status); // 50/50
            } else {
              $product = "Commission Remaining from timesheet #".$this->get_id().".  Project: ".$projectName;
              $rtn[$product] = $this->createTransaction($product, $amount, $db->f("tfID"), "commission");
            }

          }

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

  function get_amount_so_far($include_tax = false) {
    $q = prepare("SELECT SUM(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                    FROM transaction 
               LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
                   WHERE timeSheetID = %d AND transactionType != 'invoice'
                 ",$this->get_id());
    $include_tax or $q.= "AND transactionType != 'tax'";
    $db = new db_alloc();
    $r = $db->qr($q);
    return $r['balance'];
  }

  function createTransaction($product, $amount, $tfID, $transactionType, $status="", $fromTfID=false) {

    if ($amount == 0) return 1;

    $status or $status = "pending";
    $fromTfID or $fromTfID = $this->fromTfID;

    if ($tfID == 0 || !$tfID || !is_numeric($tfID) || !is_numeric($amount)) {
      return "Error -> \$tfID: ".$tfID."  and  \$amount: ".$amount;
    } else {
      $transaction = new transaction();
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

  function shootEmail($email) {
    
    $addr = $email["to"];
    $msg = $email["body"];
    $sub = $email["subject"];
    $type = $email["type"];
    $dummy = $_POST["dont_send_email"];
    
    // New email object wrapper takes care of logging etc.
    $email = new email_send($addr,$sub,$msg,$type);

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
      $t = new timeSheet();
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
      $t = new task();
      $t->set_id($taskID);
      $t->select();
      $tasks[$taskID] = $t->get_id()." ".$t->get_name();
    }

    $dropdown_options = page::select_options((array)$tasks, $taskID, 100);
    return "<select name=\"timeSheetItem_taskID\" style=\"width:400px\"><option value=\"\">".$dropdown_options."</select>";
  }

  function get_list_filter($filter=array()) {
    $current_user = &singleton("current_user");

    // If they want starred, load up the timeSheetID filter element
    if ($filter["starred"]) {
      foreach ((array)$current_user->prefs["stars"]["timeSheet"] as $k=>$v) {
        $filter["timeSheetID"][] = $k;
      }
      is_array($filter["timeSheetID"]) or $filter["timeSheetID"][] = -1;
    }

    // Filter timeSheetID
    $filter["timeSheetID"] and $sql[] = sprintf_implode("timeSheet.timeSheetID = %d",$filter["timeSheetID"]);

    // No point continuing if primary key specified, so return
    if ($filter["timeSheetID"] || $filter["starred"]) {
      return $sql;
    }

    $filter["tfID"]      and $sql[] = sprintf_implode("timeSheet.recipient_tfID = %d", $filter["tfID"]);
    $filter["projectID"] and $sql[] = sprintf_implode("timeSheet.projectID = %d",$filter["projectID"]);
    $filter["taskID"]    and $sql[] = sprintf_implode("timeSheetItem.taskID = %d", $filter["taskID"]);
    $filter["personID"]  and $sql[] = sprintf_implode("timeSheet.personID = %d",$filter["personID"]);
    if ($filter["status"]) { 
      if (is_array($filter["status"]) && count($filter["status"])) {
        foreach ($filter["status"] as $s) {
          if ($s == "rejected") {
            $rejected = true;
          } else {
            $statuses[] = $s;
          }
        }
      } else {
        if ($filter["status"] == "rejected") {
          $rejected = true;
        } else {
          $statuses[] = $filter["status"];
        }
      }
    }

    if ($rejected) {
      $sql[] = prepare("(timeSheet.dateRejected IS NOT NULL OR ".sprintf_implode("timeSheet.status = '%s'",$statuses).")");
    } else if ($statuses) {
      $sql[] = prepare("(timeSheet.dateRejected IS NULL AND ".sprintf_implode("timeSheet.status = '%s'",$statuses).")");
    }

    if ($filter["dateFrom"]) {
      in_array($filter["dateFromComparator"],array("=","!=",">",">=","<","<=")) or $filter["dateFromComparator"] = '=';
      $sql[] = prepare("(timeSheet.dateFrom ".$filter['dateFromComparator']." '%s')",$filter["dateFrom"]);
    }
    if ($filter["dateTo"]) {
      in_array($filter["dateToComparator"],array("=","!=",">",">=","<","<=")) or $filter["dateToComparator"] = '=';
      $sql[] = prepare("(timeSheet.dateTo ".$filter['dateToComparator']." '%s')",$filter["dateTo"]);
    }
    return $sql;
  }

  function get_list($_FORM) {
    /*
     * This is the definitive method of getting a list of timeSheets that need a sophisticated level of filtering
     *
     */
  
    global $TPL;
    $current_user = &singleton("current_user");
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
    $people_array =& get_cached_table("person");

    while ($row = $db->next_record()) {
      $t = new timeSheet();
      if (!$t->read_db_record($db))
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

      if ($t->get_value("status") == "edit" && imp($current_user->prefs["timeSheetHoursWarn"])
      && $t->pay_info["total_duration_hours"] >= $current_user->prefs["timeSheetHoursWarn"]) {
        $row["hoursWarn"] = page::help("This time sheet has gone over ".$current_user->prefs["timeSheetHoursWarn"]." hours.",page::warn());
      }
      if ($t->get_value("status") == "edit" && imp($current_user->prefs["timeSheetDaysWarn"]) 
      && (mktime()-format_date("U",$t->get_value("dateFrom")))/60/60/24 >= $current_user->prefs["timeSheetDaysWarn"]) {
        $row["daysWarn"] = page::help("This time sheet is over ".$current_user->prefs["timeSheetDaysWarn"]." days old.",page::warn());
      }

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

    if (!$_FORM["noextra"]) {
      return array("rows"=>(array)$rows,"extra"=>$extra);
    } else {
      return (array)$rows;
    }
  }

  function get_list_html($rows=array(),$extra=array()) {
    global $TPL;
    $TPL["timeSheetListRows"] = $rows;
    $TPL["extra"] = $extra;
    include_template(dirname(__FILE__)."/../templates/timeSheetListS.tpl");
  }

  function get_transaction_totals() {
  
    $db = new db_alloc();
    $q = prepare("SELECT amount * pow(10,-currencyType.numberToBasic) AS amount,
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
    $sess or $sess = new session();

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
                ,"starred"                        => "Time Sheet that have been starred"
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
                ,"showAllProjects"                => "Show archived and potential projects"
                );
  }

  function load_form_data($defaults=array()) {
    $current_user = &singleton("current_user");

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
    $current_user = &singleton("current_user");

    // display the list of project name.
    $db = new db_alloc();
    if (!$_FORM['showAllProjects']) {
      $filter = "WHERE projectStatus = 'Current' ";
    }
    $query = prepare("SELECT projectID AS value, projectName AS label FROM project $filter ORDER by projectName");
    $rtn["show_project_options"] = page::select_options($query, $_FORM["projectID"],70);

    // display the list of user name.
    if (have_entity_perm("timeSheet", PERM_READ, $current_user, false)) {
      $rtn["show_userID_options"] = page::select_options(person::get_username_list(), $_FORM["personID"]);
      
    } else {
      $person = new person();
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
    $rtn["show_status_options"] = page::select_options($status_array,$_FORM["status"]);

    // display the date from filter value
    $rtn["dateFrom"] = $_FORM["dateFrom"];
    $rtn["dateTo"] = $_FORM["dateTo"];
    $rtn["userID"] = $current_user->get_id();
    $rtn["showFinances"] = $_FORM["showFinances"];
    $rtn["showAllProjects"] = $_FORM["showAllProjects"];

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
                   WHERE timeSheetID = %d ORDER BY iiDate DESC",$this->get_id());
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
      alloc_error("You do not have access to this timesheet.");
    }

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
      //$this->save();
      if (is_array($m)) { 
        return implode("<br>",$m);
      }
    }
  }

  function email_move_status_to_edit($direction,$info) { 
    // is possible to move backwards to "edit", from both "manager" and "admin"
    // requires manager or APPROVE_TIMESHEET permission
    $current_user = &singleton("current_user");
    $project = $this->get_foreign_object("project");
    $projectManagers = $project->get_timeSheetRecipients();
    if ($direction == "backwards") {
      if (!in_array($current_user->get_id(), $projectManagers) &&
        !$this->have_perm(PERM_TIME_APPROVE_TIMESHEETS)) {
          //error, go away
          alloc_error("You do not have permission to change this timesheet.");
      }
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
      $this->set_value("dateRejected", date("Y-m-d"));
    }
    $this->set_value("status", "edit");
    return $msg;
  }

  function email_move_status_to_manager($direction,$info) { 
    $current_user = &singleton("current_user");
    $project = $this->get_foreign_object("project");
    $projectManagers = $project->get_timeSheetRecipients();
    // Can get forwards to "manager" only from "edit"
    if ($direction == "forwards") {
      //forward to manager requires the timesheet to be owned by the current 
      //user or TIME_INVOICE_TIMESHEETS
      //project managers may not do this
      if (!($this->get_value("personID") == $current_user->get_id() || $this->have_perm(PERM_TIME_INVOICE_TIMESHEETS))) {
        alloc_error("You do not have permission to change this timesheet.");
      }
      $this->set_value("dateSubmittedToManager", date("Y-m-d"));
      $this->set_value("dateRejected", "");
      // Check for time overrun
      $overrun_tasks = array();
      $db = new db_alloc();
      $task_id_query = prepare("SELECT DISTINCT taskID FROM timeSheetItem WHERE timeSheetID=%d ORDER BY dateTimeSheetItem, timeSheetItemID", $this->get_id());
      $db->query($task_id_query);
      while($db->next_record()) {
        $task = new task();
        $task->read_db_record($db);
        $task->select();
        if($task->get_value('timeLimit') > 0) {
          $total_billed_time = ($task->get_time_billed(false)) / 3600;
          if($total_billed_time > $task->get_value('timeLimit')) {
            $overrun_tasks[] = sprintf(" * %d %s (limit: %.02f hours, billed so far: %.02f hours)", $task->get_id(), $task->get_value('taskName'), $task->get_value('timeLimit'), $total_billed_time);
          }
        }
        $hasItems = true;
      }

      if (!$hasItems) {
        return alloc_error('Unable to submit time sheet, no items have been added.');
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
      //admin->manager requires APPROVE_TIMESHEETS
      if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
        alloc_error("You do not have permission to change this timesheet.");
      }
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
      $this->set_value("dateRejected", date("Y-m-d"));
    }
    $this->set_value("status", "manager");
    $this->set_value("dateSubmittedToAdmin", "");
    $this->set_value("approvedByAdminPersonID", "");
    return $msg;
  }

  function email_move_status_to_admin($direction,$info) { 
    $current_user = &singleton("current_user");
    $project = $this->get_foreign_object("project");
    $projectManagers = $project->get_timeSheetRecipients();
    // Can get forwards to "admin" from "edit" and "manager"
    if ($direction == "forwards") {
      //3 ways to have permission to do this
      //project manager for the timesheet
      //no project manager and owner of the timesheet
      //the permission flag
      if (!(in_array($current_user->get_id(), $projectManagers) || 
        (empty($projectManagers) && $this->get_value("personID") == $current_user->get_id()) ||
        $this->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {
          //error, go away
        alloc_error("You do not have permission to change this timesheet.");
      }

      $db = new db_alloc();
      $hasItems = $db->qr("SELECT * FROM timeSheetItem WHERE timeSheetID = %d",$this->get_id());
      if (!$hasItems) {
        return alloc_error('Unable to submit time sheet, no items have been added.');
      }

        if ($this->get_value("status") == "manager") { 
          $this->set_value("approvedByManagerPersonID",$current_user->get_id());
          $extra = " Approved By: ".person::get_fullname($current_user->get_id());
        }
        $this->set_value("status", "admin");
        $this->set_value("dateSubmittedToAdmin", date("Y-m-d"));
	      $this->set_value("dateRejected", "");
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
      //requires INVOICE_TIMESHEETS
      if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
        alloc_error("You do not have permission to change this timesheet.");
      }

      $this->set_value("approvedByAdminPersonID", "");
    }
    $this->set_value("status", "admin");
    return $msg;
  }

  function email_move_status_to_invoiced($direction,$info) { 
    $current_user = &singleton("current_user");
    // Can get forwards to "invoiced" from "admin" 
    // requires INVOICE_TIMESHEETS
    if (!$this->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
        //no permission, go away
      alloc_error("You do not have permission to change this timesheet.");
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
        alloc_error("You do not have permission to change this timesheet.");
      }

      //transactions
      $q = prepare("SELECT DISTINCT transaction.transactionDate, transaction.product, transaction.status
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
    if (!$this->have_perm(PERM_TIME_APPROVE_TIMESHEETS)) {
      //no permission, die
      alloc_error("You do not have permission to approve transactions for this timesheet.");
    }

    $db = new db_alloc();
    $q = prepare("UPDATE transaction SET status = 'approved' WHERE timeSheetID = %d AND status = 'pending'",$this->get_id());
    $db->query($q);
  }

  function get_email_vars() {
    $current_user = &singleton("current_user");
    static $rtn;
    if ($rtn) {
      return $rtn;
    }
    // Get vars for the emails below
    $rtn["people_cache"] = $people_cache =& get_cached_table("person");
    $project = $this->get_foreign_object("project");
    $rtn["projectManagers"] = $project->get_timeSheetRecipients();
    $rtn["projectName"] = $project->get_value("projectName");
    $rtn["timeSheet_personID_email"] = $people_cache[$this->get_value("personID")]["emailAddress"];
    $rtn["timeSheet_personID_name"]  = $people_cache[$this->get_value("personID")]["name"];

    $config = new config();
    $rtn["url"] = $config->get_config_item("allocURL")."time/timeSheet.php?timeSheetID=".$this->get_id();

    $rtn["timeSheetAdministrators"] = $config->get_config_item('defaultTimeSheetAdminList');
    $rtn["approvedByManagerPersonID_email"] = $people_cache[$this->get_value("approvedByManagerPersonID")]["emailAddress"];
    $rtn["approvedByManagerPersonID_name"] = $people_cache[$this->get_value("approvedByManagerPersonID")]["name"];
    $rtn["approvedByAdminPersonID_name"] = $people_cache[$this->get_value("approvedByAdminPersonID")]["name"];
    $rtn["current_user_name"] = $people_cache[$current_user->get_id()]["name"];

    return $rtn;
  }

  function add_timeSheetItem($stuff) {
    $current_user = &singleton("current_user");

    $errstr = "Failed to record new time sheet item. ";
    $taskID = $stuff["taskID"];
    $projectID = $stuff["projectID"];
    $duration = $stuff["duration"];
    $comment = $stuff["comment"];
    $emailUID = $stuff["msg_uid"];
    $emailMessageID = $stuff["msg_id"];
    $date = $stuff["date"];
    $unit = $stuff["unit"];
    $multiplier = $stuff["multiplier"];

    if ($taskID) {
      $task = new task();
      $task->set_id($taskID);
      $task->select();
      $projectID = $task->get_value("projectID");
      $extra = " for task ".$taskID;
    }

    $projectID or alloc_error(sprintf($errstr."No project found%s.",$extra));

    $row_projectPerson = projectPerson::get_projectPerson_row($projectID, $current_user->get_id());
    $row_projectPerson or alloc_error($errstr."The person(".$current_user->get_id().") has not been added to the project(".$projectID.").");

    if ($row_projectPerson && $projectID) {

      if ($stuff["timeSheetID"]) {
        $q = prepare("SELECT *
                        FROM timeSheet
                       WHERE status = 'edit'
                         AND personID = %d
                         AND timeSheetID = %d
                    ORDER BY dateFrom
                       LIMIT 1
                  ",$current_user->get_id(),$stuff["timeSheetID"]);
        $db = new db_alloc();
        $db->query($q);
        $row = $db->row();
        $row or alloc_error("Couldn't find an editable time sheet with that ID.");

      } else {
        $q = prepare("SELECT *
                        FROM timeSheet
                       WHERE status = 'edit'
                         AND projectID = %d
                         AND personID = %d
                         AND dateRejected IS NULL
                    ORDER BY dateFrom
                       LIMIT 1
                  ",$projectID, $current_user->get_id());
        $db = new db_alloc();
        $db->query($q);
        $row = $db->row();
      }

      // If no timeSheets add a new one
      if (!$row) {
        $project = new project();
        $project->set_id($projectID);
        $project->select();

        $timeSheet = new timeSheet();
        $timeSheet->set_value("projectID",$projectID);
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

      $timeSheetID or alloc_error($errstr."Couldn't locate an existing, or create a new Time Sheet.");

      // Add new time sheet item
      if ($timeSheetID) {
        $timeSheet = new timeSheet();
        $timeSheet->set_id($timeSheetID);
        $timeSheet->select();

        $tsi = new timeSheetItem();
        $tsi->currency = $timeSheet->get_value("currencyTypeID");
        $tsi->set_value("timeSheetID",$timeSheetID);
        $d = $date or $d = date("Y-m-d");
        $tsi->set_value("dateTimeSheetItem",$d);
        $tsi->set_value("timeSheetItemDuration",$duration);
        $tsi->set_value("timeSheetItemDurationUnitID", $unit);
        if (is_object($task)) {
          $tsi->set_value("description",$task->get_name());
          $tsi->set_value("taskID",sprintf("%d",$taskID));
          $_POST["timeSheetItem_taskID"] = sprintf("%d",$taskID); // this gets used in timeSheetItem->save();
        }
        $tsi->set_value("personID",$current_user->get_id());
        $tsi->set_value("rate",page::money($timeSheet->get_value("currencyTypeID"),$row_projectPerson["rate"],"%mo"));
        $tsi->set_value("multiplier",$multiplier);
        $tsi->set_value("comment",$comment);
        $tsi->set_value("emailUID",$emailUID);
        $tsi->set_value("emailMessageID",$emailMessageID);
        $tsi->save();
        $id = $tsi->get_id();

        $tsi = new timeSheetItem();
        $tsi->set_id($id);
        $tsi->select();
        $ID = $tsi->get_value("timeSheetID");
      }
    }

    if ($ID) {
      return array("status"=>"yay","message"=>$ID);
    } else {
      alloc_error($errstr."Time not added.");
    }
  }

  function get_all_parties($projectID="") {
    $db = new db_alloc();
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
        $p = new person();
        $p->set_id($this->get_value("personID"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_value("firstName")." ".$p->get_value("surname"), "role"=>"assignee", "selected"=>false, "personID"=>$this->get_value("personID"));
      }
      if ($this->get_value("approvedByManagerPersonID")) {
        $p = new person();
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
      $q = prepare("SELECT invoiceID
                      FROM invoiceItem
                     WHERE invoiceItem.timeSheetID = %d
                  ORDER BY invoiceItem.iiDate DESC
                     LIMIT 1
                  ",$this->get_id());
      $db->query($q);
      $row = $db->row();
      $invoiceID = $row["invoiceID"];
      if ($invoiceID) {
        $invoice = new invoice();
        $invoice->set_id($invoiceID);
        $invoice->select();
        $maxAmount = page::money($invoice->get_value("currencyTypeID"),$invoice->get_value("maxAmount"),$fmt);
    
        // Loop through all the other invoice items on that invoice
        $q = prepare("SELECT sum(iiAmount) AS totalUsed FROM invoiceItem WHERE invoiceID = %d",$invoiceID);
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
    $p =& get_cached_table("person");
    return "Time Sheet for ".$project->get_name($_FORM)." by ".$p[$this->get_value("personID")]["name"];
  }

  function update_search_index_doc(&$index) {
    $p =& get_cached_table("person");
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

    $q = prepare("SELECT dateTimeSheetItem, taskID, description, comment, commentPrivate 
                    FROM timeSheetItem 
                   WHERE timeSheetID = %d 
                ORDER BY dateTimeSheetItem ASC",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($r = $db->row()) {
      $desc.= $br.$r["dateTimeSheetItem"]." ".$r["taskID"]." ".$r["description"]."\n";
      $r["comment"] && $r["commentPrivate"] or $desc.= $r["comment"]."\n";
      $br = "\n";
    }

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('project' ,$projectName,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('pid'     ,$this->get_value("projectID"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$person_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$desc,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('status'  ,$this->get_value("status"),"utf-8")); 
    $doc->addField(Zend_Search_Lucene_Field::Text('tf'      ,$tf_field,"utf-8")); 
    $doc->addField(Zend_Search_Lucene_Field::Text('manager' ,$manager_field,"utf-8")); 
    $doc->addField(Zend_Search_Lucene_Field::Text('admin'   ,$admin_field,"utf-8")); 
    $doc->addField(Zend_Search_Lucene_Field::Text('dateManager',str_replace("-","",$this->get_value("dateSubmittedToManager")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateAdmin',str_replace("-","",$this->get_value("dateSubmittedToAdmin")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateFrom',str_replace("-","",$this->get_value("dateFrom")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateTo'  ,str_replace("-","",$this->get_value("dateTo")),"utf-8"));
    $index->addDocument($doc);
  }

  function can_edit_rate() {
    $current_user = &singleton("current_user");
    $db = new db_alloc();
    $row = $db->qr("SELECT can_edit_rate(%d,%d) as allow",$current_user->get_id(),$this->get_value("projectID"));
    return $row["allow"];
  }

  function get_project_id() {
    return $this->get_value("projectID");
  }
}  




?>
