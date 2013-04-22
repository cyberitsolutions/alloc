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


class transaction extends db_entity {
  public $classname = "transaction";
  public $data_table = "transaction";
  public $display_field_name = "product";
  public $key_field = "transactionID";
  public $data_fields = array("companyDetails" => array("empty_to_null"=>false)
                             ,"product" => array("empty_to_null"=>false)
                             ,"amount" => array("type"=>"money") 
                             ,"currencyTypeID" 
                             ,"destCurrencyTypeID" 
                             ,"exchangeRate" 
                             ,"status"
                             ,"dateApproved"
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
                             ,"productCostID"
                             ,"transactionRepeatID"
                             ,"transactionGroupID"
                             );

  function save() {

    // These need to be in here instead of validate(), because
    // validate is called after save() and we need these values set for save().
    $this->get_value("currencyTypeID") or $this->set_value("currencyTypeID",config::get_config_item("currency"));
    $this->get_value("destCurrencyTypeID") or $this->set_value("destCurrencyTypeID",config::get_config_item("currency"));

    // The data prior to the save
    $old = $this->all_row_fields;
    if ($old["status"] != $this->get_value("status") && $this->get_value("status") == "approved") {
      $this->set_value("dateApproved",date("Y-m-d"));
      $field_changed = true;
    } else if ($this->get_value("status") != "approved") {
      $this->set_value("dateApproved","");
    }

    if ($old["currencyTypeID"] != $this->get_value("currencyTypeID")) {
      $field_changed = true;
    }
    if ($old["destCurrencyTypeID"] != $this->get_value("destCurrencyTypeID")) {
      $field_changed = true;
    }
    $db = new db_alloc();

    // If there already is an exchange rate set for an approved
    // transaction, then there's no need to update the exchange rate
    if ($this->get_value("exchangeRate") && $this->get_value("dateApproved") && !$field_changed) {

    // Else update the transaction's exchange rate
    } else {
      $this->get_value("transactionCreatedTime")  and $date = format_date("Y-m-d",$this->get_value("transactionCreatedTime"));
      $this->get_value("transactionModifiedTime") and $date = format_date("Y-m-d",$this->get_value("transactionModifiedTime"));
      $this->get_value("transactionDate")         and $date = $this->get_value("transactionDate");
      $this->get_value("dateApproved")            and $date = $this->get_value("dateApproved");

      $er = exchangeRate::get_er($this->get_value("currencyTypeID"), $this->get_value("destCurrencyTypeID"), $date);
      if (!$er) {
        alloc_error("Unable to determine exchange rate for ".$this->get_value("currencyTypeID")." to ".$this->get_value("destCurrencyTypeID")." for date: ".$date);
      } else {
        $this->set_value("exchangeRate",$er);
      }
    }

    return parent::save();

  }

  function validate() {
    $current_user = &singleton("current_user");
    
    $this->get_value("fromTfID") or $err[] = "Unable to save transaction without a Source TF.";
    $this->get_value("fromTfID") && $this->get_value("fromTfID") == $this->get_value("tfID") and $err[] = "Unable to save transaction with Source TF (".tf::get_name($this->get_value("fromTfID")).") being the same as the Destination TF (".tf::get_name($this->get_value("tfID")).") \"".$this->get_value("product")."\"";
    $this->get_value("quantity") or $this->set_value("quantity",1);
    $this->get_value("transactionDate") or $this->set_value("transactionDate",date("Y-m-d"));

    $old = $this->all_row_fields;
    $status = $old["status"] or $status = $this->get_value("status");
    if ($status != "pending" && !$current_user->have_role("admin")) {
      $err[] = "Unable to save transaction unless status is pending.";
    }
    if ($old["status"] == "pending" && $old["status"] != $this->get_value("status") && !$current_user->have_role("admin")) {
      $err[] = "Unable to change transaction status unless you have admin perm.";
    }
    return parent::validate($err);
  }

  function is_owner($person = "") {
    $current_user = &singleton("current_user");
    if ($person == "") {
      $person = $current_user;
    }


    if ($this->get_value("expenseFormID")) {
      $expenseForm = $this->get_foreign_object("expenseForm");
      return $expenseForm->is_owner($person);
    }
    if ($this->get_value("timeSheetID")) {
      $timeSheet = $this->get_foreign_object("timeSheet");
      return $timeSheet->is_owner($person);
    }
    if ($this->get_value("productSaleItemID")) {
      $productSaleItem = $this->get_foreign_object("productSaleItem");
      return $productSaleItem->is_owner();
    }

    $toTf = new tf();
    $toTf->set_id($this->get_value('tfID'));
    $toTf->select();

    $fromTf = new tf();
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
                ,'sale'=>'Sale'
                ,'tax'=>$taxName);
  }

  function get_transactionStatii() {
    return array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
  }

  function get_url() {
    global $sess;
    $sess or $sess = new session();

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

  function get_transaction_link($_FORM=array()) {
    $_FORM["return"] or $_FORM["return"] = "html";
    $rtn = "<a href=\"".$this->get_url()."\">";
    $rtn.= $this->get_name($_FORM);
    $rtn.= "</a>";
    return $rtn;
  }

  function get_name($_FORM=array()) {
    if ($_FORM["return"] == "html") {
      return $this->get_value("product",DST_HTML_DISPLAY);
    } else {
      return $this->get_value("product");
    }
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
        $invoice->get_id() and $str = "<a href=\"".$invoice->get_url()."\">".$transactionTypes[$type]." ".$invoice->get_value("invoiceNum")."</a>";

    // Transaction is from an expenseform
    } else if ($type == "expense") {
      $expenseForm = $this->get_foreign_object("expenseForm");
      if ($expenseForm->get_id() && $expenseForm->have_perm(PERM_READ_WRITE)) {
        $str = "<a href=\"".$expenseForm->get_url()."\">".$transactionTypes[$type]." ".$this->get_value("expenseFormID")."</a>";
      } 
      
    // Had to rewrite this so that people who had transactions on other peoples timesheets 
    // could see their own transactions, but not the other persons timesheet.
    } else if ($type == "timesheet" && $this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet();
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $str = "<a href=\"".$timeSheet->get_url()."\">".$transactionTypes[$type]." ".$this->get_value("timeSheetID")."</a>";

    } else if (($type == "commission" || $type == "tax") && $this->get_value("timeSheetID")) {
      $timeSheet = new timeSheet();
      $timeSheet->set_id($this->get_value("timeSheetID"));
      $str = "<a href=\"".$timeSheet->get_url()."\">".$transactionTypes[$type]." (Time Sheet ".$this->get_value("timeSheetID").")</a>";

    } else {
      $str = $transactionTypes[$type];
    }

    if ($this->get_value("transactionGroupID")) {
      $str.= " <a href=\"".$TPL["url_alloc_transactionGroup"]."transactionGroupID=".$this->get_value("transactionGroupID")."\">Group ".$this->get_value("transactionGroupID")."</a>";
    }

    return $str;
  }

  function reduce_tfs($_FORM) {
    if ($_FORM["tfName"]) {
      $q = prepare("SELECT * FROM tf WHERE tfName = '%s'",$_FORM["tfName"]);
      $db = new db_alloc();
      $db->query($q);
      $db->next_record();
      $tfIDs[] = $db->f("tfID");
    }
    if ($_FORM["tfID"]) {
      $tfIDs[] = $_FORM["tfID"];
    }
    if ($_FORM["tfIDs"]) {
      $tfIDs = array_merge((array)$tfIDs,(array)$_FORM["tfIDs"]);
    }
    return tf::get_permitted_tfs($tfIDs);
  }

  function get_list_filter($_FORM) {
    $current_user = &singleton("current_user");

    if (is_array($_FORM["tfIDs"]) && count($_FORM["tfIDs"])) {
      $sql["tfIDs"] = sprintf_implode("transaction.tfID = %d or transaction.fromTfID = %d",$_FORM["tfIDs"],$_FORM["tfIDs"]);
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

    $_FORM["startDate"]       and $sql["startDate"]       = prepare("(%s >= '%s')",$_FORM["sortTransactions"],$_FORM["startDate"]);
    $_FORM["startDate"]       and $sql["prevBalance"]     = prepare("(%s < '%s')",$_FORM["sortTransactions"],$_FORM["startDate"]);
    $_FORM["endDate"]         and $sql["endDate"]         = prepare("(%s < '%s')",$_FORM["sortTransactions"],$_FORM["endDate"]);
    $_FORM["status"]          and $sql["status"]          = prepare("(status = '%s')",$_FORM["status"]);
    $_FORM["transactionType"] and $sql["transactionType"] = prepare("(transactionType = '%s')",$_FORM["transactionType"]);

    $_FORM["fromTfID"]        and $sql["fromTfID"]        = prepare("(fromTfID=%d)",$_FORM["fromTfID"]);
    $_FORM["expenseFormID"]   and $sql["expenseFormID"]   = prepare("(expenseFormID=%d)",$_FORM["expenseFormID"]);
    $_FORM["transactionID"]   and $sql["transactionID"]   = prepare("(transactionID=%d)",$_FORM["transactionID"]);
    $_FORM["product"]         and $sql["product"]         = prepare("(product LIKE \"%%%s%%\")",$_FORM["product"]);
    $_FORM["amount"]          and $sql["amount"]          = prepare("(amount = '%s')",$_FORM["amount"]);

    return $sql;
  }

  function get_list($_FORM) {
    $current_user = &singleton("current_user");
    global $TPL;

    /*
     * This is the definitive method of getting a list of transactions that need a sophisticated level of filtering
     *
     */

    $_FORM["tfIDs"] = transaction::reduce_tfs($_FORM);

    // Non-admin users must specify a valid TF
    if (!$current_user->have_role("admin") && !$_FORM["tfIDs"]) {
      return;
    }

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

  
    // Determine opening balance
    if (is_array($_FORM['tfIDs']) && count($_FORM['tfIDs'])) {
      $q = prepare("SELECT SUM( IF(fromTfID IN (%s),-amount,amount) * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                      FROM transaction 
                 LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
                    ".$filter2, $_FORM['tfIDs']);
      $debug and print "\n<br>QUERY: ".$q;
      $db = new db_alloc();
      $db->query($q);
      $db->row();
      $_FORM["opening_balance"] = $db->f("balance");
      $running_balance = $db->f("balance");
    }

    $q = "SELECT *, 
                 (amount * pow(10,-currencyType.numberToBasic)) as amount1,
                 (amount * pow(10,-currencyType.numberToBasic) * exchangeRate) as amount2,
                 if(transactionModifiedTime,transactionModifiedTime,transactionCreatedTime) AS transactionSortDate,
                 tf1.tfName as fromTfName,
                 tf2.tfName as tfName
            FROM transaction 
       LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
       LEFT JOIN tf tf1 ON transaction.fromTfID = tf1.tfID
       LEFT JOIN tf tf2 ON transaction.tfID = tf2.tfID
         ".$filter." 
         ".$order_by;

    $debug and print "\n<br>QUERY2: ".$q;
    $db = new db_alloc();
    $db->query($q);
    $for_cyber = config::for_cyber();
    while ($row = $db->next_record()) {
      #echo "<pre>".print_r($row,1)."</pre>";
      $i++;
      $t = new transaction();
      if (!$t->read_db_record($db))
        continue;

      $print = true;
  
      // If the destination of this TF is not the current TfID, then invert the $amount
      $amount = $row["amount2"];
      if (!in_array($row["tfID"],(array)$_FORM["tfIDs"])) {
        $amount = -$amount;
        $row["amount1"] = -$row["amount1"];
      }

      $row["amount"] = $amount;
      $row["transactionURL"] = $t->get_url();
      $row["transactionName"] = $t->get_name($_FORM);
      $row["transactionLink"] = $t->get_transaction_link($_FORM);
      $row["transactionTypeLink"] = $t->get_transaction_type_link() or $row["transactionTypeLink"] = $row["transactionType"];
      $row["transactionSortDate"] = format_date("Y-m-d",$row["transactionSortDate"]); 

      $row["fromTfIDLink"] = "<a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$row["fromTfID"]."\">".page::htmlentities($row["fromTfName"])."</a>";
      $row["tfIDLink"] = "<a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$row["tfID"]."\">".page::htmlentities($row["tfName"])."</a>";

      if ($t->get_value("status") == "approved") {
        $running_balance += $amount;
        $row["running_balance"] = page::money(config::get_config_item("currency"),$running_balance,"%m %c");
      }
 
      if ($amount > 0) {
        $row["amount_positive"] = page::money($row["currencyTypeID"],$row["amount1"],"%m %c");
        $total_amount_positive += $amount;
      } else {
        $row["amount_negative"] = page::money($row["currencyTypeID"],$row["amount1"],"%m %c");
        $total_amount_negative += $amount;
      }

      // Cyber only hackery for ext ref field on product sales
      if ($for_cyber && $row["productSaleID"]) {
        $ps = new productSale();
        $ps->set_id($row["productSaleID"]);
        if ($ps->select()) {
          $ps->get_value("extRef") and $row["product"].= " (Ext ref: ".$ps->get_value("extRef").")";
        }
      }

      $transactions[$row["transactionID"]] = $row;
    }

    $_FORM["total_amount_positive"] = page::money(config::get_config_item("currency"),$total_amount_positive,"%s%m %c");
    $_FORM["total_amount_negative"] = page::money(config::get_config_item("currency"),$total_amount_negative,"%s%m %c");
    $_FORM["running_balance"] =       page::money(config::get_config_item("currency"),$running_balance,"%s%m %c");

    return array("totals"=>$_FORM, "rows"=>(array)$transactions);
  }

  function arr_to_csv($rows=array()) {
       
    $csvHeaders = array("transactionID"
                       ,"transactionType"
                       ,"fromTfID"
                       ,"tfID"
                       ,"transactionDate"
                       ,"transactionSortDate"
                       ,"product"
                       ,"status"
                       ,"currencyTypeID"
                       ,"exchangeRate"
                       ,"destCurrencyTypeID"
                       ,"amount_positive"
                       ,"amount_negative"
                       ,"running_balance");
    
    foreach ((array)$rows as $row) {
      $csv_data = array();
      foreach($csvHeaders as $header) {  
        $csv_data[] = $row[$header];
      }
      $csv.= $nl.implode(",",array_map('export_escape_csv', $csv_data));
      $nl = "\n";
    }
    return implode(",",array_map('export_escape_csv', $csvHeaders))."\n".$csv;
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
                ,"transactionType"   => "Eg: invoice | expense | salary | commission | timesheet | adjustment | tax | sale"
                ,"applyFilter"       => "Saves this filter as the persons preference"
                ,"url_form_action"   => "The submit action for the filter form"
                ,"form_name"         => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"          => "Specify that the filter preferences should not be saved this time"
                ,"fromTfID"          => "Transactions that have a source of this TF"
                ,"expenseFormID"     => "Transactions for a particular Expense Form"
                ,"transactionID"     => "A Transaction by ID"
                ,"product"           => "Transactions with a description like *something* (fuzzy)"
                ,"amount"            => "Get Transactions that are for a certain amount"
                );
  }

  function load_form_data($defaults=array()) {
    $current_user = &singleton("current_user");

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

    // Fiddle $_FORM["monthDate"]. It may be a real date (2010-11-23) so change the last 2 chars to "01".
    $_FORM["monthDate"] = substr_replace($_FORM["monthDate"], "01", 8);

    // If this month is January, go from last Feb to this Feb
    $m = date("m") + 1;
    $y = date("Y");

    // jump back a year iff it's December now
    if ($m == 13)
    	$m = 1;
    else
        $y -= 1;

    $label_monthDate = date($display_format);

    for ($j = 0;$j < 13;$j++) {

      $label = date($display_format,mktime(0,0,0,$m,1,$y));
      $monthDate = date("Y-m-d",mktime(0,0,0,$m,1,$y));

      $bold = false;
      if ($monthDate == format_date("Y-m-d",$_FORM["monthDate"])) {
        $bold = true;
      } 

      $link = $TPL["url_alloc_transactionList"]."tfID=".$_FORM["tfID"]."&monthDate=".$monthDate."&applyFilter=true";
      $bold and $rtn["month_links"] .= "<b>";
      $rtn["month_links"] .= $sp."<a href=\"".$link."\">".$label."</a>";
      $bold and $rtn["month_links"] .= "</b>";
      $sp = "&nbsp;&nbsp;";

      if ($m == 12) {
          $m = 1;
          $y += 1;
      } else {
          $m += 1;
      }
    }

    if ($_FORM["sortTransactions"] == "transactionSortDate") {
      $rtn["checked_transactionSortDate"] = " checked";
    } else {
      $rtn["checked_transactionDate"] = " checked";
    }

    $tf = new tf();
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

    return $sum;
    
    # for debugging
    #$rows[] = array("amount"=>"20","fromTfID"=>"alla","tfID"=>"twb");
    #$rows[] = array("amount"=>"17","fromTfID"=>"twb","tfID"=>"alla");
    #$rows[] = array("amount"=>"2","fromTfID"=>"alla","tfID"=>"pete");
    #$rows[] = array("amount"=>"-4","fromTfID"=>"pete","tfID"=>"alla");
    #$rows[] = array("amount"=>"200","fromTfID"=>"zebra","tfID"=>"ghost");
    #echo "<br>SUM: ".transaction::get_actual_amount_used($rows);
  }

  function get_next_transactionGroupID() {
    $q = "SELECT coalesce(max(transactionGroupID)+1,1) as newNum FROM transaction";
    $db = new db_alloc();
    $db->query($q);
    $db->row();
    return $db->f("newNum");
  }  


}




?>
