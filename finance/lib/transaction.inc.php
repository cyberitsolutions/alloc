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

define("PERM_FINANCE_WRITE_INVOICE_TRANSACTION", 256);
define("PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION", 512);
define("PERM_FINANCE_WRITE_WAGE_TRANSACTION", 1024);
define("PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT", 2048);
define("PERM_FINANCE_WRITE_APPROVED_TRANSACTION", 4096);
define("PERM_FINANCE_CREATE_PENDING_TRANSACTION", 8192);
define("PERM_FINANCE_UPLOAD_EXPENSES_FILE", 16384);
define("PERM_FINANCE_RECONCILIATION_REPORT", 32768);


class transaction extends db_entity {
  public $data_table = "transaction";
  public $display_field_name = "product";
  public $key_field = "transactionID";
  public $data_fields = array("companyDetails" => array("empty_to_null"=>false)
                             ,"product" => array("empty_to_null"=>false)
                             ,"amount" 
                             ,"status"
                             ,"expenseFormID" 
                             ,"invoiceID"
                             ,"invoiceItemID"
                             ,"tfID"
                             ,"fromTfID"
                             ,"projectID"
                             ,"transactionModifiedUser"
                             ,"transactionModifiedTime"
                             ,"transactionCreatedTime"
                             ,"transactionCreatedUser"
                             ,"quantity"
                             ,"transactionDate"
                             ,"transactionType"
                             ,"timeSheetID"
                             ,"productSaleID"
                             ,"productSaleItemID"
                             ,"transactionRepeatID"
                             );

    public $permissions = array(PERM_FINANCE_WRITE_INVOICE_TRANSACTION => "add/update/delete invoice transaction"
                               ,PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION => "add/update/delete free-form transaction"
                               ,PERM_FINANCE_WRITE_WAGE_TRANSACTION => "add/update/delete wage transaction"
                               ,PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT => "create from repeating transactions"
                               ,PERM_FINANCE_WRITE_APPROVED_TRANSACTION => "approve/reject transactions"
                               ,PERM_FINANCE_UPLOAD_EXPENSES_FILE => "upload expenses file"
                               ,PERM_FINANCE_RECONCILIATION_REPORT => "view reconciliation report"
                               ,PERM_FINANCE_CREATE_PENDING_TRANSACTION => "create pending transaction"
                               );


  function check_view_perms() {
    if ($this->get_value("transactionType") == "sale" && $this->get_value("status") == "pending") {
      $this->check_perm(PERM_FINANCE_CREATE_PENDING_TRANSACTION);
      $skip_default = true;
    }
    return $skip_default;
  }

  function check_write_perms() {
    if ($this->get_value("transactionType") == "sale" && $this->get_value("status") == "pending") {
      $this->check_perm(PERM_FINANCE_CREATE_PENDING_TRANSACTION);
      $skip_default = true;
    }
    if ($this->get_value("status") != "pending") {
      $this->check_perm(PERM_FINANCE_WRITE_APPROVED_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "invoice") {
      $this->check_perm(PERM_FINANCE_WRITE_INVOICE_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "salary") {
      $this->check_perm(PERM_FINANCE_WRITE_WAGE_TRANSACTION);
    }
    return $skip_default;
  }

  function check_create_perms() {
    $skip_default = $this->check_write_perms();
    if (!$skip_default) {
      parent::check_create_perms();
    }
  }

  function check_update_perms() {
    $skip_default = $this->check_write_perms();
    if (!$skip_default) {
      parent::check_update_perms();
    }
  }

  function check_delete_perms() {
    $skip_default = $this->check_write_perms();
    if (!$skip_default) {
      parent::check_delete_perms();
    }
  }

  function check_read_perms() {
    $skip_default = $this->check_view_perms();
    if (!$skip_default) {
      parent::check_read_perms();
    }
  }

  function validate() {
    //The transaction may not be modified if the timesheet or invoice
    //it is attached to has been completed.
    //Special case: transactions attached to expense forms can be modified
    //regardless (because the expense form is finalised before the user gets
    //the chance to click "approve"/"reject")
    $this->is_final() && !$this->get_value("expenseFormID") and $err[] = "Cannot save transaction. Transaction has been finalised.";
    $this->get_value("fromTfID") or $err[] = "Unable to save transaction without a Source TF.";
    $this->get_value("fromTfID") == $this->get_value("tfID") and $err[] = "Unable to save transaction with Source TF (".$this->get_value("fromTfID").") being the same as the Destination TF (".$this->get_value("tfID").")";
    $this->get_value("quantity") or $this->set_value("quantity",1);
    return $err;
  }

  function is_final() {
    if ($this->get_value("expenseFormID")) {
      $expenseForm = new expenseForm;
      $expenseForm->set_id($this->get_value("expenseFormID"));
      $expenseForm->select();
      if ($expenseForm->get_value("expenseFormFinalised")) {
        return true;
      }
    } else if ($this->get_value("invoiceItemID")) {
      $invoiceItem = new invoiceItem;
      $invoiceItem->set_id($this->get_value("invoiceItemID"));
      $invoiceItem->select();
      $invoice = new invoice;
      $invoice->set_id($invoiceItem->get_value("invoiceID"));
      $invoice->select();
      if ($invoice->get_value("invoiceStatus") == "finished") {
        return true;
      }
    } else if ($this->get_value("timeSheetID")) {
      $ts = new timeSheet;
      $ts->set_id($this->get_value("timeSheetID"));
      $ts->select();
      if ($ts->get_value("status") == "finished") {
        return true;
      }
    }
    return false;
  }

  function is_owner($person = "") {
    global $current_user;
    if ($person == "") {
      $person = $current_user;
    }

    if($person->have_role("employee") && $this->get_value('expenseFormID') && $this->get_value('fromTfID') == config::get_config_item('expenseFormTfID')) {
      // employees have implicit ownership of the expenseFormTfID for expense forms
      return true;
    }

    if ($this->get_value("timeSheetID")) {
      $timeSheet = $this->get_foreign_object("timeSheet");
      return $timeSheet->is_owner($person);
    }
    if ($this->get_value("productSaleItemID")) {
      $productSaleItem = $this->get_foreign_object("productSaleItem");
      return $productSaleItem->is_owner();
    }

    $toTf = new tf;
    $toTf->set_id($this->get_value('tfID'));
    $toTf->select();

    $fromTf = new tf;
    $fromTf->set_id($this->get_value('fromTfID'));
    $fromTf->select();

    return ($toTf->is_owner($person) || $fromTf->is_owner($person));
  }

  function get_transactionTypes() {
    $taxName = config::get_config_item("taxName") or $taxName = "Tax";
    return array('invoice'=>'Invoice'
                ,'expense'=>'Expense'
                ,'salary'=>'Salary'
                ,'commission'=>'Commission'
                ,'timesheet'=>'Time Sheet'
                ,'adjustment'=>'Adjustment'
                ,'insurance'=>'Insurance'
                ,'tax'=>$taxName);
  }

  function get_transactionStatii() {
    return array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
  }

  function get_url() {
    global $sess;
    $sess or $sess = new Session;

    $url = "finance/transaction.php?transactionID=".$this->get_id();

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

  function get_transaction_link() {
    $rtn = "<a href=\"".$this->get_url()."\">";
    $rtn.= $this->get_transaction_name();
    $rtn.= "</a>";
    return $rtn;
  }

  function get_transaction_name() {
    $rtn = $this->get_value("product");
    return $rtn;
  }

  function get_transaction_type_link() {
    global $TPL;
    $type = $this->get_value("transactionType");
    $transactionTypes = transaction::get_transactionTypes();
    
    // Transaction stems from an invoice
    if ($type == "invoice") {
        $invoice = $this->get_foreign_object("invoice");
        if (!$invoice->get_id()) {
          $invoiceItem = $this->get_foreign_object("invoiceItem");
          $invoice = $invoiceItem->get_foreign_object("invoice");
        }
        $str = "<a href=\"".$invoice->get_url()."\">".$transactionTypes[$type]." ".$invoice->get_value("invoiceNum")."</a>";
      
    // Transaction is from an expenseform
    } else if ($type == "expense") {
      $expenseForm = $this->get_foreign_object("expenseForm");
      if ($expenseForm->get_id() && $expenseForm->have_perm(PERM_READ_WRITE)) {
        $str = "<a href=\"".$expenseForm->get_url()."\">".$transactionTypes[$type]." ".$this->get_value("expenseFormID")."</a>";
      } 
      
    // Had to rewrite this so that people who had transactions on other peoples timesheets 
    // could see their own transactions, but not the other persons timesheet.
    } else if ($type == "timesheet" && $this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet;
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $str = "<a href=\"".$timeSheet->get_url()."\">".$transactionTypes[$type]." ".$this->get_value("timeSheetID")."</a>";

    } else if (($type == "insurance" || $type == "commission" || $type == "tax") && $this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet;
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $str = "<a href=\"".$timeSheet->get_url()."\">".$transactionTypes[$type]." (Time Sheet ".$this->get_value("timeSheetID").")</a>";
    } else {
      return $transactionTypes[$type];
    }
    return $str;
  }

  function get_list_filter($_FORM) {

    if (is_array($_FORM["tfIDs"]) && count($_FORM["tfIDs"])) {
      foreach ($_FORM["tfIDs"] as $tfID) {
        $str.= $commar.db_esc($tfID);
        $commar=",";
      }
      $sql["tfIDs"] = sprintf("(tfID in (%s) or fromTfID in (%s))",$str,$str);
    }

    if ($_FORM["monthDate"]) {
      $_FORM["startDate"] = format_date("Y-m-",$_FORM["monthDate"])."01";
      $t = format_date("U",$_FORM["monthDate"]);
      $_FORM["endDate"] = date("Y-m-d",mktime(1,1,1,date("m",$t)+1,1,date("Y",$t)));
    }

    $_FORM["sortTransactions"] or $_FORM["sortTransactions"] = "transactionDate";

    if ($_FORM["sortTransactions"] == "transactionSortDate") {
      $_FORM["sortTransactions"] = "if(transactionModifiedTime,transactionModifiedTime,transactionCreatedTime)";
    }

    $_FORM["startDate"]       and $sql["startDate"]       = "(".$_FORM["sortTransactions"]." >= '".db_esc($_FORM["startDate"])."')";
    $_FORM["startDate"]       and $sql["prevBalance"]     = "(".$_FORM["sortTransactions"]." < '".db_esc($_FORM["startDate"])."')";
    $_FORM["endDate"]         and $sql["endDate"]         = "(".$_FORM["sortTransactions"]." < '".db_esc($_FORM["endDate"])."')";
    $_FORM["status"]          and $sql["status"]          = "(status = '".db_esc($_FORM["status"])."')";
    $_FORM["transactionType"] and $sql["transactionType"] = "(transactionType = '".db_esc($_FORM["transactionType"])."')";

    $_FORM["fromTfID"]        and $sql["fromTfID"]        = sprintf("(fromTfID=%d)",db_esc($_FORM["fromTfID"]));
    $_FORM["expenseFormID"]   and $sql["expenseFormID"]   = sprintf("(expenseFormID=%d)",db_esc($_FORM["expenseFormID"]));
    $_FORM["transactionID"]   and $sql["transactionID"]   = sprintf("(transactionID=%d)",db_esc($_FORM["transactionID"]));
    $_FORM["product"]         and $sql["product"]         = sprintf("(product LIKE \"%%%s%%\")",db_esc($_FORM["product"]));
    $_FORM["amount"]          and $sql["amount"]          = sprintf("(amount = '%s')",db_esc($_FORM["amount"]));

    return $sql;
  }

  function get_list($_FORM) {
    global $current_user;

    /*
     * This is the definitive method of getting a list of transactions that need a sophisticated level of filtering
     *
     */

    if ($_FORM["tfName"]) {
      $q = sprintf("SELECT * FROM tf WHERE tfName = '%s'",db_esc($_FORM["tfName"]));
      $db = new db_alloc();
      $db->query($q);
      $db->next_record();
      $_FORM["tfIDs"][] = $db->f("tfID");
    }

    if ($_FORM["tfID"]) {
      $_FORM["tfIDs"][] = $_FORM["tfID"];
    }

    $_FORM["tfIDs"] or $_FORM["tfIDs"] = array();

    $filter = transaction::get_list_filter($_FORM);
    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    $_FORM["return"] or $_FORM["return"] = "html";

    $filter["prevBalance"] and $filter2[] = $filter["prevBalance"];
    $filter["tfIDs"]       and $filter2[] = $filter["tfIDs"];
    $filter2               and $filter2[] = " (status = 'approved') ";
    unset($filter["prevBalance"]);

    if (is_array($filter2) && count($filter2)) {
      $filter2 = " WHERE ".implode(" AND ",$filter2);
    }
    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $_FORM["sortTransactions"] or $_FORM["sortTransactions"] = "transactionDate";
    $order_by = "ORDER BY ".$_FORM["sortTransactions"];

    $_FORM["csvHeaders"] or $_FORM["csvHeaders"] = array("transactionID", "transactionType", "fromTfID", "tfID", "transactionDate", "transactionSortDate", "product", "status", "amount_positive", "amount_negative", "running_balance");

  
    // Determine opening balance
    if (is_array($_FORM['tfIDs'] && count($_FORM['tfIDs']))) {
      $q = sprintf("SELECT SUM(IF(fromTfID IN (%s),-amount,amount)) AS balance FROM transaction %s", implode(",", $_FORM['tfIDs']), $filter2);
      $debug and print "\n<br>QUERY: ".$q;
      $db = new db_alloc;
      $db->query($q);
      $db->row();
      $running_balance = $db->f("balance");
    }

    $q = "SELECT *, if(transactionModifiedTime,transactionModifiedTime,transactionCreatedTime) AS transactionSortDate 
            FROM transaction 
         ".$filter." 
         ".$order_by;

    $debug and print "\n<br>QUERY2: ".$q;
    $db = new db_alloc;
    $db->query($q);
    while ($row = $db->next_record()) {
      #echo "<pre>".print_r($row,1)."</pre>";
      $i++;
      $t = new transaction;
      if (!$t->read_db_record($db,false)) {
        continue;
      }
      $print = true;
  
      // If the destination of this TF is not the current TfID, then invert the $amount
      $amount = $t->get_value("amount");
      if (!in_array($row["tfID"],$_FORM["tfIDs"])) {
        $amount = -$amount;
      }

      $row["amount"] = $amount;
      $row["transactionURL"] = $t->get_url();
      $row["transactionName"] = $t->get_transaction_name($_FORM);
      $row["transactionLink"] = $t->get_transaction_link($_FORM);
      $row["transactionTypeLink"] = $t->get_transaction_type_link() or $row["transactionTypeLink"] = $row["transactionType"];
      $row["transactionSortDate"] = format_date("Y-m-d",$row["transactionSortDate"]); 

      $tf = new tf;
      $tf->set_id($t->get_value("fromTfID"));
      $tf->select();
      $row["fromTfIDLink"] = $tf->get_link();

      $tf = new tf;
      $tf->set_id($t->get_value("tfID"));
      $tf->select();
      $row["tfIDLink"] = $tf->get_link();

      if ($t->get_value("status") == "approved") {
        $running_balance += $amount;
        $row["running_balance"] = sprintf("%0.2f",$running_balance);
      }
 
      if ($amount > 0) {
        $row["amount_positive"] = sprintf("%0.2f",$amount);
        $total_amount_positive += sprintf("%0.2f",$amount);
      } else {
        $row["amount_negative"] = sprintf("%0.2f",$amount);
        $total_amount_negative += $amount;
      }


      if ($_FORM["return"] == "html") {
        $row["object"] = $t;
        $summary.= transaction::get_list_tr($row,$_FORM);

      } else if ($_FORM["return"] == "csv") {
        $csv_data = array();
        foreach($_FORM["csvHeaders"] as $header) {  //suck out the data in the right order
          $csv_data[] = $row[$header];
        }
        $csv.= $nl.implode(",",array_map('export_escape_csv', $csv_data));
        $nl = "\n";

      } else if ($_FORM["return"] == "array") {
        #$row["object"] = $t; // this is really too large to return via soap
        $transactions[$row["transactionID"]] = $row;
      }
    }
    $_FORM["total_amount_positive"] = sprintf("%0.2f",$total_amount_positive);
    $_FORM["total_amount_negative"] = sprintf("%0.2f",$total_amount_negative);
    $_FORM["running_balance"] = sprintf("%0.2f",$running_balance);

    // A header row
    $header_row = transaction::get_list_tr_header($_FORM);
    $footer_row = transaction::get_list_tr_footer($_FORM);

    if ($print && $_FORM["return"] == "html") {
      return $header_row.$summary.$footer_row;

    } else if ($print && $_FORM["return"] == "csv") {
      return implode(",",array_map('export_escape_csv', $_FORM["csvHeaders"]))."\n".$csv;

    } else if ($print && $_FORM["return"] == "array") {
      return $transactions;
    } 
  }

  function get_list_tr_header($_FORM) {
    global $TPL;
    $str[] = "<table class=\"list sortable\">";
    $str[] = "<tr>";
    $str[] = "  <th width=\"1%\">ID</th>";
    $str[] = "  <th width=\"1%\">Type</th>";
    $str[] = "  <th width=\"1%\">Source TF</th>";
    $str[] = "  <th width=\"1%\">Dest TF</th>";
    $str[] = "  <th width=\"1%\">Date</th>";
    $str[] = "  <th width=\"1%\">Modified</th>";
    $str[] = "  <th>Product</th>";
    $str[] = "  <th width=\"1%\">Status</th>";
    $str[] = "  <th class=\"right\" width=\"1%\">Credit</th>";
    $str[] = "  <th class=\"right\" width=\"1%\">Debit</th>";
    $str[] = "  <th class=\"right\" width=\"1%\">Balance</th>";
    $str[] = "</tr>";
    return implode("\n",$str);
  }

  function get_list_tr_footer($_FORM) {
    $str[] = "<tfoot>";
    $str[] = "<tr>";
    $str[] = "  <td colspan=\"8\">&nbsp;</td>";
    $str[] = "  <td class=\"grand_total nobr right\">".$_FORM["total_amount_positive"]."&nbsp;</td>";
    $str[] = "  <td class=\"grand_total nobr right\">".$_FORM["total_amount_negative"]."&nbsp;</td>";
    $str[] = "  <td class=\"grand_total nobr right transaction-approved\">".$_FORM["running_balance"]."&nbsp;</td>";
    $str[] = "</tr>";
    $str[] = "</tfoot>";
    $str[] = "</table>";
    return implode("\n",$str);
  }

  function get_list_tr($row) {
    global $TPL;
    $str[] = "<tr class=\"".$row["class"]."\">";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\"><a href=\"".$TPL["url_alloc_transaction"]."transactionID=".$row["transactionID"]."\">".$row["transactionID"]."</a></td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["transactionTypeLink"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["fromTfIDLink"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["tfIDLink"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["transactionDate"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["transactionSortDate"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]."\">".$row["product"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr\">".$row["status"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr right\">".$row["amount_positive"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr right\">".$row["amount_negative"]."&nbsp;</td>";
    $str[] = "  <td class=\"transaction-".$row["status"]." nobr right\">".$row["running_balance"]."&nbsp;</td>";
    $str[] = "</tr>";
    return implode("\n",$str);
  }

  function get_list_vars() {
  
    return array("return"            => "[MANDATORY] eg: html | csv | array"
                ,"tfID"              => "Transactions that are for this TF"
                ,"tfIDs"             => "Transactions that are for this array of TF's"
                ,"tfName"            => "Transactions that are for this TF name"
                ,"status"            => "Transaction status eg: pending | rejected | approved"
                ,"startDate"         => "Transactions with dates after this start date eg: 2002-07-07"
                ,"endDate"           => "Transactions with dates before this end date eg: 2007-07-07"
                ,"monthDate"         => "Transactions for a particular month, by date, eg july: 2008-07-07"
                ,"sortTransactions"  => "Sort transactions eg: transactionSortDate | transactionDate"
                ,"transactionType"   => "Eg: invoice | expense | salary | commission | timesheet | adjustment | insurance | tax | sale"
                ,"applyFilter"       => "Saves this filter as the persons preference"
                ,"url_form_action"   => "The submit action for the filter form"
                ,"form_name"         => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"          => "Specify that the filter preferences should not be saved this time"
                ,"fromTfID"          => "Transactions that have a source of this TF"
                ,"expenseFormID"     => "Transactions for a particular Expense Form"
                ,"transactionID"     => "A Transaction by ID"
                ,"product"           => "Transactions with a description like *something* (fuzzy)"
                ,"amount"            => "Get Transactions that are for a certain amount"
                ,"csvHeaders"        => "An array of columns to include in the output, when generating CSV"
                );
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array_keys(transaction::get_list_vars());
  
    $_FORM = get_all_form_data($page_vars,$defaults);

    #echo "<pre>".print_r($_FORM,1)."</pre>";

    #if (!$_FORM["applyFilter"]) {
    #  $_FORM = $current_user->prefs[$_FORM["form_name"]];
    #  if (!isset($current_user->prefs[$_FORM["form_name"]])) {
    #    #$_FORM["personID"] = $current_user->get_id();
    #    list($_FORM["startDate"], $_FORM["endDate"]) = transaction::get_statement_start_and_end_dates(date("m"),date("Y"));
    #  }

    #} else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
    #  $url = $_FORM["url_form_action"];
    #  unset($_FORM["url_form_action"]);
    #  $current_user->prefs[$_FORM["form_name"]] = $_FORM;
    #  $_FORM["url_form_action"] = $url;
    #}

    return $_FORM;
  }

  function load_transaction_filter($_FORM) {
    global $TPL;

    $rtn["statusOptions"] = page::select_options(array(""=>"","pending"=>"Pending","approved"=>"Approved","rejected"=>"Rejected"),$_FORM["status"]);

    $transactionTypeOptions = transaction::get_transactionTypes();
    $rtn["transactionTypeOptions"] = page::select_options($transactionTypeOptions,$_FORM["transactionType"]);

    $rtn["startDate"] = $_FORM["startDate"];
    $rtn["endDate"] = $_FORM["endDate"];

    if ($_FORM["monthDate"]) {
      $rtn["startDate"] = format_date("Y-m-",$_FORM["monthDate"])."01";
      $t = format_date("U",$_FORM["monthDate"]);
      $rtn["endDate"] = date("Y-m-d",mktime(1,1,1,date("m",$t)+1,1,date("Y",$t)));
    }

    $display_format = "M";

    $m = date("m");
    $y = date("Y");
    $label_monthDate = date($display_format);

    while ($i < 12) {
      $i++;

      $label = date($display_format,mktime(0,0,0,$i,1,$y));
      $monthDate = date("Y-m-d",mktime(0,0,0,$i,1,$y));

      $bold = false;
      if ($label == format_date($display_format,$_FORM["monthDate"])) {
        $bold = true;
      } 

      $link = $TPL["url_alloc_transactionList"]."tfID=".$_FORM["tfID"]."&monthDate=".$monthDate."&applyFilter=true";
      $bold and $rtn["month_links"] .= "<b>";
      $rtn["month_links"] .= $sp."<a href=\"".$link."\">".$label."</a>";
      $bold and $rtn["month_links"] .= "</b>";
      $sp = "&nbsp;&nbsp;";
    }

    if ($_FORM["sortTransactions"] == "transactionSortDate") {
      $rtn["checked_transactionSortDate"] = " checked";
    } else {
      $rtn["checked_transactionDate"] = " checked";
    }

    $tf = new tf;
    $options = $tf->get_assoc_array("tfID","tfName");
    $rtn["tfOptions"] = page::select_options($options, $_FORM["tfID"]);
    $rtn["fromTfOptions"] = page::select_options($options, $_FORM["fromTfID"]);
    $rtn["transactionID"] = $_FORM["transactionID"];
    $rtn["expenseFormID"] = $_FORM["expenseFormID"];
    $rtn["product"] = $_FORM["product"];
    $rtn["amount"] = $_FORM["amount"];

    return $rtn;
  }

  function get_actual_amount_used($rows=array()) {

    /*
     *  The purpose of this function is to turn the below three transactions
     *  not into their sum of $48 but into the amount used, which is $10.
     *
     *  Amount   Source     Dest
     *  $20       A    ->    B   -->  A -20   B 20
     *  $18       B    ->    C   -->  B 2     C 18
     *  $10       C    ->    A   -->  C 8     A -10
     *
     *  So, actually:
     *
     *  A gets $-10
     *  B gets $2
     *  C gets $8
     *
     *  So i.e. -10 +10 the amount actually *used* is ten dollars.
     *
     *  This function is useful for ensuring that if say a time sheet has
     *  100 dollars to be allocated, that no more than that limit is spent.
     *
     */

    $rows or $rows = array();
    $tallies or $tallies = array();
    foreach ($rows as $k => $row) {
      $tallies[$row["fromTfID"]] -= $row["amount"];
      $tallies[$row["tfID"]] += $row["amount"];
    }

    foreach ($tallies as $tfID => $amount) {
      $amount >0 and $sum+=$amount;
    }

    return sprintf("%0.2f",$sum);
    
    # for debugging
    #$rows[] = array("amount"=>"20","fromTfID"=>"alla","tfID"=>"twb");
    #$rows[] = array("amount"=>"17","fromTfID"=>"twb","tfID"=>"alla");
    #$rows[] = array("amount"=>"2","fromTfID"=>"alla","tfID"=>"pete");
    #$rows[] = array("amount"=>"-4","fromTfID"=>"pete","tfID"=>"alla");
    #$rows[] = array("amount"=>"200","fromTfID"=>"zebra","tfID"=>"ghost");
    #echo "<br>SUM: ".transaction::get_actual_amount_used($rows);
  }


}




?>
