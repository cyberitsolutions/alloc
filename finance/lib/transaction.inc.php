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
define("PERM_FINANCE_UPLOAD_EXPENSES_FILE", 16384);
define("PERM_FINANCE_RECONCILIATION_REPORT", 32768);

class transaction extends db_entity
{
  var $data_table = "transaction";
  var $display_field_name = "product";

  function transaction() {
    $this->db_entity();
    $this->key_field = new db_field("transactionID");
    $this->data_fields = array("companyDetails"=>new db_field("companyDetails", array("empty_to_null"=>false))
                               , "product"=>new db_field("product", array("empty_to_null"=>false))
                               , "amount"=>new db_field("amount")
                               , "status"=>new db_field("status")
                               , "expenseFormID"=>new db_field("expenseFormID", array("empty_to_null"=>false))
                               , "invoiceID"=>new db_field("invoiceID")
                               , "invoiceItemID"=>new db_field("invoiceItemID")
                               , "tfID"=>new db_field("tfID")
                               , "fromTfID"=>new db_field("fromTfID")
                               , "projectID"=>new db_field("projectID")
                               , "transactionModifiedUser"=>new db_field("transactionModifiedUser")
                               , "transactionModifiedTime"=>new db_field("transactionModifiedTime")
                               , "transactionCreatedTime"=>new db_field("transactionCreatedTime")
                               , "transactionCreatedUser"=>new db_field("transactionCreatedUser")
                               , "quantity"=>new db_field("quantity")
                               , "transactionDate"=>new db_field("transactionDate")
                               , "transactionType"=>new db_field("transactionType")
                               , "timeSheetID"=>new db_field("timeSheetID")
                               , "productSaleItemID"=>new db_field("productSaleItemID")
                               , "transactionRepeatID"=>new db_field("transactionRepeatID")
      );

    $this->permissions[PERM_FINANCE_WRITE_INVOICE_TRANSACTION] = "Add/update/delete invoice transaction";
    $this->permissions[PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION] = "Add/update/delete free-form transaction";
    $this->permissions[PERM_FINANCE_WRITE_WAGE_TRANSACTION] = "Add/update/delete wage transaction";
    $this->permissions[PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT] = "Create from repeating transactions";
    $this->permissions[PERM_FINANCE_WRITE_APPROVED_TRANSACTION] = "Approve/Reject transactions";
    $this->permissions[PERM_FINANCE_UPLOAD_EXPENSES_FILE] = "Upload expenses file";
    $this->permissions[PERM_FINANCE_RECONCILIATION_REPORT] = "View reconciliation report";

    $this->set_value("quantity", 1);
  }

  function check_write_perms() {
    if ($this->get_value("status") != "pending") {
      $this->check_perm(PERM_FINANCE_WRITE_APPROVED_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "invoice") {
      $this->check_perm(PERM_FINANCE_WRITE_INVOICE_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "salary") {
      $this->check_perm(PERM_FINANCE_WRITE_WAGE_TRANSACTION);
    }
  }

  function insert() {
    $this->check_write_perms();
    db_entity::insert();
  }

  function update() {
    $this->check_write_perms();
    db_entity::update();
  }

  function delete() {
    $this->check_write_perms();
    db_entity::delete();
  }

  function save() {
    global $TPL;
    //safety checks
    //The transaction may not be modified if the timesheet or invoice
    //it is attached to has been completed.
    //Special case: transactions attached to expense forms can be modified
    //regardless (because the expense form is finalised before the user gets
    //the chance to click "approve"/"reject")
    if ($this->is_final() && !$this->get_value("expenseFormID")) {
      die("Cannot save transaction, as it has been finalised.");

    } else if (!$this->get_value("fromTfID")) {
      $TPL["message"][] = "Unable to save transaction without a Source TF.";
    
    } else if ($this->get_value("fromTfID") == $this->get_value("tfID")) {
      $TPL["message"][] = "Unable to save transaction with Source TF being the same as the Destination TF.";

    } else {
      return parent::save();
    }
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

    if ($this->get_value("timeSheetID")) {
      $timeSheet = $this->get_foreign_object("timeSheet");
      return $timeSheet->is_owner($person);
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

  function get_transaction_list_filter($_FORM) {

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
    $_FORM["endDate"]         and $sql["endDate"]         = "(".$_FORM["sortTransactions"]." <= '".db_esc($_FORM["endDate"])."')";
    $_FORM["status"]          and $sql["status"]          = "(status = '".db_esc($_FORM["status"])."')";
    $_FORM["transactionType"] and $sql["transactionType"] = "(transactionType = '".db_esc($_FORM["transactionType"])."')";
    return $sql;
  }

  function get_transaction_list($_FORM) {
    global $current_user;


    /*
     * This is the definitive method of getting a list of transactions that need a sophisticated level of filtering
     *
     * Display Options:
     *
     * Filter Options:
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

    $filter = transaction::get_transaction_list_filter($_FORM);
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

  
    // Determine opening balance
    $q = sprintf("SELECT SUM(IF(fromTfID IN (%s),-amount,amount)) AS balance FROM transaction %s", implode(",", $_FORM['tfIDs']), $filter2);
    $debug and print "\n<br>QUERY: ".$q;
    $db = new db_alloc;
    $db->query($q);
    $db->row();
    $running_balance = $db->f("balance");

    $q = sprintf("SELECT *, if(transactionModifiedTime,transactionModifiedTime,transactionCreatedTime) AS transactionSortDate 
                    FROM transaction ".$filter." ".$order_by);
    $debug and print "\n<br>QUERY2: ".$q;
    $db = new db_alloc;
    $db->query($q);
    while ($row = $db->next_record()) {
      #echo "<pre>".print_r($row,1)."</pre>";
      $print = true;
      $i++;
      $t = new transaction;
      if (!$t->read_db_record($db,false)) {
        continue;
      }
  
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
        $summary.= transaction::get_transaction_list_tr($row,$_FORM);
      } else if ($_FORM["return"] == "csv") {
        $csv_headers or $csv_headers = array_keys($row);
        $csv.= $nl.implode(",",array_map('export_escape_csv', $row));
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
    $header_row = transaction::get_transaction_list_tr_header($_FORM);
    $footer_row = transaction::get_transaction_list_tr_footer($_FORM);

    if ($print && $_FORM["return"] == "html") {
      return $header_row.$summary.$footer_row;
    } else if ($print && $_FORM["return"] == "csv") {
      return implode(",",array_map('export_escape_csv', $csv_headers))."\n".$csv; 
    } else if ($print && $_FORM["return"] == "array") {
      return $transactions;
    } 
  }

  function get_transaction_list_tr_header($_FORM) {
    global $TPL;
    $str[] = $TPL["table_list"];
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

  function get_transaction_list_tr_footer($_FORM) {
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

  function get_transaction_list_tr($row) {
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

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array("tfID"
                      ,"tfIDs"
                      ,"tfName"
                      ,"status"
                      ,"startDate"
                      ,"endDate"
                      ,"monthDate"
                      ,"sortTransactions"
                      ,"transactionType"
                      ,"applyFilter"
                      ,"url_form_action"
                      ,"form_name"
                      ,"dontSave"
                      ,"return"
                      );

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

    $rtn["statusOptions"] = get_select_options(array("pending"=>"Pending","approved"=>"Approved","rejected"=>"Rejected"),$_FORM["status"]);

    $transactionTypeOptions = transaction::get_transactionTypes();
    $rtn["transactionTypeOptions"] = get_select_options($transactionTypeOptions,$_FORM["transactionType"]);

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



    return $rtn;
  }



}



?>
