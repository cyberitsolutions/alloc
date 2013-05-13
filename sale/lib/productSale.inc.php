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


define("PERM_APPROVE_PRODUCT_TRANSACTIONS", 256);
class productSale extends db_entity {
  public $classname = "productSale";
  public $data_table = "productSale";
  public $key_field = "productSaleID";
  public $data_fields = array("clientID"
                             ,"projectID"
                             ,"personID"
                             ,"tfID"
                             ,"status"
                             ,"productSaleCreatedTime"
                             ,"productSaleCreatedUser"
                             ,"productSaleModifiedTime"
                             ,"productSaleModifiedUser"
                             ,"productSaleDate"
                             ,"extRef"
                             ,"extRefDate"
                             );
  public $permissions = array(PERM_APPROVE_PRODUCT_TRANSACTIONS => "approve product transactions");

  function validate() {
    if ($this->get_value("status") == "admin" || $this->get_value("status") == "finished") {
      $orig = new $this->classname;
      $orig->set_id($this->get_id());
      $orig->select();
      $orig_status = $orig->get_value("status");
      if ($orig_status == "allocate" && $this->get_value("status") == "admin") {

      } else if (!$this->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
        $rtn[] = "Unable to save Product Sale, user does not have correct permissions.";
      }
    }

    if ($this->get_value("extRef")) {
      $q = prepare("SELECT productSaleID FROM productSale WHERE productSaleID != %d AND extRef = '%s'"
                   ,$this->get_id(),$this->get_value("extRef"));
      $db = new db_alloc();
      if ($r = $db->qr($q)) {
        $rtn[] = "Unable to save Product Sale, this external reference number is used in Sale ".$r["productSaleID"];
      }
    }

    return parent::validate($rtn);
  }
 
  function is_owner() {
    $current_user = &singleton("current_user");
    return !$this->get_id()
           || $this->get_value("productSaleCreatedUser") == $current_user->get_id()
           || $this->get_value("personID") == $current_user->get_id();
  } 

  function delete() {
    $db = new db_alloc();
    $query = prepare("SELECT * 
                        FROM productSaleItem 
                       WHERE productSaleID = %d"
                    , $this->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $productSaleItem = new productSaleItem();
      $productSaleItem->read_db_record($db);
      $productSaleItem->delete();
    }
    $this->delete_transactions();
    return parent::delete();
  }

  function translate_meta_tfID($tfID="") {
    // The special -1 and -2 tfID's represent META TF, i.e. calculated at runtime
    // -1 == META: Project TF
    if ($tfID == -1) { 
      if ($this->get_value("projectID")) {
        $project = new project();
        $project->set_id($this->get_value("projectID"));
        $project->select();
        $tfID = $project->get_value("cost_centre_tfID");
      }
      if (!$tfID) {
        alloc_error("Unable to use META: Project TF. Please ensure the project has a TF set, or adjust the transactions.");
      }

    // -2 == META: Salesperson TF
    } else if ($tfID == -2) {
      if ($this->get_value("personID")) {
        $person = new person();
        $person->set_id($this->get_value("personID")); 
        $person->select();
        $tfID = $person->get_value("preferred_tfID");
        if (!$tfID) {
          alloc_error("Unable to use META: Salesperson TF. Please ensure the Saleperson has a Preferred Payment TF.");
        }
      } else {
        alloc_error("Unable to use META: Salesperson TF. No product salesperson set.");
      }
    } else if ($tfID == -3) {
      $tfID = $this->get_value("tfID");
      $tfID or alloc_error("Unable to use META: Sale TF not set.");
    }
    return $tfID;
  }
  
  function get_productSaleItems() {
    $q = prepare("SELECT * FROM productSaleItem WHERE productSaleID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    $rows = array();
    while($row = $db->row()) {
      $rows[$row["productSaleItemID"]] = $row;
    }
    return $rows;
  }

  function get_amounts() {

    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
    $rtn = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem();
      $productSaleItem->read_row_record($row);
      //$rtn["total_spent"] += $productSaleItem->get_amount_spent();
      //$rtn["total_earnt"] += $productSaleItem->get_amount_earnt();
      //$rtn["total_other"] += $productSaleItem->get_amount_other();
      list($sp,$spcur) = array($productSaleItem->get_value("sellPrice"),$productSaleItem->get_value("sellPriceCurrencyTypeID"));

      $sellPriceCurr[$spcur] += page::money($spcur,$sp,"%m");
      $total_sellPrice += exchangeRate::convert($spcur,$sp);
      $total_margin += $productSaleItem->get_amount_margin();
      $total_unallocated += $productSaleItem->get_amount_unallocated();
    }    

    unset($sep,$label,$show);

    foreach ((array)$sellPriceCurr as $code => $amount) {
      $label.= $sep.page::money($code,$amount,"%s%mo %c");
      $sep = " + ";
      $code != config::get_config_item("currency") and $show = true;
    }
    $show && $label and $sellPrice_label = " (".$label.")";

    $total_sellPrice_plus_gst = add_tax($total_sellPrice);

    $rtn["total_sellPrice"] = page::money(config::get_config_item("currency"),$total_sellPrice,"%s%mo %c").$sellPrice_label;
    $rtn["total_sellPrice_plus_gst"] = page::money(config::get_config_item("currency"),$total_sellPrice_plus_gst,"%s%mo %c").$sellPrice_label;
    $rtn["total_margin"] = page::money(config::get_config_item("currency"),$total_margin,"%s%mo %c");
    $rtn["total_unallocated"] = page::money(config::get_config_item("currency"),$total_unallocated,"%s%mo %c");
    $rtn["total_unallocated_number"] = page::money(config::get_config_item("currency"),$total_unallocated,"%mo");

    $rtn["total_sellPrice_value"] = page::money(config::get_config_item("currency"),$total_sellPrice,"%mo");
    return $rtn;
  }

  function create_transactions() {
    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem();
      $productSaleItem->read_row_record($row);
      $productSaleItem->create_transactions();
    }
  }

  function delete_transactions() {
    $rows = $this->get_productSaleItems();
    $rows or $rows = array();
  
    foreach ($rows as $row) {
      $productSaleItem = new productSaleItem();
      $productSaleItem->read_row_record($row);
      $productSaleItem->delete_transactions();
    }
  }
 
  function move_forwards() {
    $current_user = &singleton("current_user");
    global $TPL;
    $status = $this->get_value("status");
    $db = new db_alloc();


    if ($this->get_value("clientID")) {
      $c = $this->get_foreign_object("client");
      $extra = " for ".$c->get_value("clientName");
      $taskDesc[] = "";
    }

    $taskname1 = "Sale ".$this->get_id().": raise an invoice".$extra;
    $taskname2 = "Sale ".$this->get_id().": place an order to the supplier";
    $taskname3 = "Sale ".$this->get_id().": pay the supplier";
    $taskname4 = "Sale ".$this->get_id().": deliver the goods / action the work";
    $cyberadmin = 59;


    $taskDesc[] = "Sale items:";
    $taskDesc[] = "";
    foreach((array)$this->get_productSaleItems() as $psiID => $psi_row) {
      $p = new product();
      $p->set_id($psi_row["productID"]);
      $taskDesc[] = "  * ".$psi_row["quantity"]." x "
                    .page::money($psi_row["sellPriceCurrencyTypeID"],$psi_row["sellPrice"],"%S%mo")
                    ." ".$p->get_name();
      $hasItems = true;
    }

    if (!$hasItems) {
      return alloc_error("No sale items have been added.");
    }

    $amounts = $this->get_amounts();
    $taskDesc[] = "";
    $taskDesc[] = "Total: ".$amounts["total_sellPrice"];
    $taskDesc[] = "Total inc ".config::get_config_item("taxName").": ".$amounts["total_sellPrice_plus_gst"];
    $taskDesc[] = "";
    $taskDesc[] = "Refer to the sale in alloc for up-to-date information:";
    $taskDesc[] = config::get_config_item("allocURL")."sale/productSale.php?productSaleID=".$this->get_id();

    $taskDesc = implode("\n",$taskDesc);

    if ($status == "edit") {
      $this->set_value("status", "allocate");
      
      $items = $this->get_productSaleItems();
      foreach ($items as $r) {
        $psi = new productSaleItem();
        $psi->set_id($r["productSaleItemID"]);
        $psi->select();
        if (!$db->qr("SELECT transactionID FROM transaction WHERE productSaleItemID = %d",$psi->get_id())) {
          $psi->create_transactions();
        }
      }

    } else if ($status == "allocate") {
      $this->set_value("status", "admin");

      // 1. from salesperson to admin
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskName = '%s'",$cyberadmin,$taskname1);
      if (config::for_cyber() && !$db->qr($q)) {
        $task = new task();
        $task->set_value("projectID",$cyberadmin); // Cyber Admin Project
        $task->set_value("taskName",$taskname1);
        $task->set_value("managerID",$this->get_value("personID")); // salesperson
        $task->set_value("personID",67); // Cyber Support people (jane)
        $task->set_value("priority",3);
        $task->set_value("taskTypeID","Task");
        $task->set_value("taskDescription",$taskDesc);
        $task->set_value("dateTargetStart",date("Y-m-d"));
        $task->set_value("dateTargetCompletion",date("Y-m-d",date("U")+(60*60*24*7)));
        $task->save();
        $TPL["message_good"][] = "Task created: ".$task->get_id()." ".$task->get_value("taskName");

        $p1 = new person();
        $p1->set_id($this->get_value("personID"));
        $p1->select();
        $p2 = new person();
        $p2->set_id(67);
        $p2->select();
        $recipients[$p1->get_value("emailAddress")] = array("name"=>$p1->get_name(),"addIP"=>true,"internal"=>true);
        $recipients[$p2->get_value("emailAddress")] = array("name"=>$p2->get_name(),"addIP"=>true,"internal"=>true);

        $comment = $p2->get_name().",\n\n".$taskname1."\n\n".$taskDesc;
        $commentID = comment::add_comment("task", $task->get_id(), $comment, "task", $task->get_id());
        $emailRecipients = comment::add_interested_parties($commentID, null, $recipients);

        // Re-email the comment out, including any attachments
        if (!comment::send_comment($commentID,$emailRecipients)) {
          alloc_error("Email failed to send.");
        } else {
          $TPL["message_good"][] = "Emailed task comment to ".$p1->get_value("emailAddress").", ".$p2->get_value("emailAddress").".";
        }

      }

    } else if ($status == "admin" && $this->have_perm(PERM_APPROVE_PRODUCT_TRANSACTIONS)) {
      $this->set_value("status", "finished");
      if ($_REQUEST["changeTransactionStatus"]) {
        $rows = $this->get_productSaleItems();
        foreach ($rows as $row) {
          $ids[] = $row["productSaleItemID"];
        }
        if ($ids) {
          $q = prepare("UPDATE transaction SET status = '%s' WHERE productSaleItemID in (%s)",$_REQUEST["changeTransactionStatus"],$ids);
          $db = new db_alloc();
          $db->query($q);
        }
      }

      // 2. from admin to salesperson
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskName = '%s'",$cyberadmin,$taskname2);
      if (config::for_cyber() && !$db->qr($q)) {
        $task = new task();
        $task->set_value("projectID",$cyberadmin); // Cyber Admin Project
        $task->set_value("taskName",$taskname2);
        $task->set_value("managerID",67); // Cyber Support people (jane)
        $task->set_value("personID",$this->get_value("personID")); // salesperson
        $task->set_value("priority",3);
        $task->set_value("taskTypeID","Task");
        $task->set_value("taskDescription",$taskDesc);
        $task->set_value("dateTargetStart",date("Y-m-d"));
        $task->set_value("dateTargetCompletion",date("Y-m-d",date("U")+(60*60*24*7)));
        $task->save();

        $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskName = '%s'"
                    ,$cyberadmin,$taskname1);
        $rai_row = $db->qr($q);
        if ($rai_row) {
          $task->add_pending_tasks($rai_row["taskID"]);
        }

        $order_the_hardware_taskID = $task->get_id();
        $TPL["message_good"][] = "Task created: ".$task->get_id()." ".$task->get_value("taskName");

        $task->add_notification(3,1,"Task ".$task->get_id()." ".$taskname2,"Task status moved from pending to open."
                               ,array(array("field"=>"metaPersonID","who"=>-2)));
      }

      // 3. from salesperson to admin
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskName = '%s'",$cyberadmin,$taskname3);
      if (config::for_cyber() && !$db->qr($q)) {
        $task = new task();
        $task->set_value("projectID",$cyberadmin); // Cyber Admin Project
        $task->set_value("taskName",$taskname3);
        $task->set_value("managerID",$this->get_value("personID")); // salesperson
        $task->set_value("personID",67); // Cyber Support people (jane)
        $task->set_value("priority",3);
        $task->set_value("taskTypeID","Task");
        $task->set_value("taskDescription",$taskDesc);
        $task->set_value("dateTargetStart",date("Y-m-d"));
        $task->set_value("dateTargetCompletion",date("Y-m-d",date("U")+(60*60*24*7)));
        $task->save();
        $task->add_pending_tasks($order_the_hardware_taskID);
        $pay_the_supplier_taskID = $task->get_id();
        $TPL["message_good"][] = "Task created: ".$task->get_id()." ".$task->get_value("taskName");

        $task->add_notification(3,1,"Task ".$task->get_id()." ".$taskname3,"Task status moved from pending to open."
                               ,array(array("field"=>"metaPersonID","who"=>-2)));
      }

      // 4. from admin to salesperson
      $q = prepare("SELECT * FROM task WHERE projectID = %d AND taskName = '%s'",$cyberadmin,$taskname4);
      if (config::for_cyber() && !$db->qr($q)) {
        $task = new task();
        $task->set_value("projectID",$cyberadmin); // Cyber Admin Project
        $task->set_value("taskName",$taskname4);
        $task->set_value("managerID",67); // Cyber Support people
        $task->set_value("personID",$this->get_value("personID")); // salesperson
        $task->set_value("priority",3);
        $task->set_value("taskTypeID","Task");
        $task->set_value("taskDescription",$taskDesc);
        $task->set_value("dateTargetStart",date("Y-m-d"));
        $task->set_value("dateTargetCompletion",date("Y-m-d",date("U")+(60*60*24*7)));
        $task->save();
        $task->add_pending_tasks($pay_the_supplier_taskID);
        $TPL["message_good"][] = "Task created: ".$task->get_id()." ".$task->get_value("taskName");

        $task->add_notification(3,1,"Task ".$task->get_id()." ".$taskname4,"Task status moved from pending to open."
                               ,array(array("field"=>"metaPersonID","who"=>-2)));
      }
    }
  }

  function get_transactions($productSaleItemID=false) {
    $rows = array();
    $query = prepare("SELECT transaction.*
                            ,productCost.productCostID  as pc_productCostID
                            ,productCost.amount         as pc_amount
                            ,productCost.isPercentage   as pc_isPercentage
                            ,productCost.currencyTypeID as pc_currency
                        FROM transaction 
                   LEFT JOIN productCost on transaction.productCostID = productCost.productCostID
                       WHERE productSaleID = %d
                         AND productSaleItemID = %d
                    ORDER BY transactionID"
                    ,$this->get_id()
                    ,$productSaleItemID);
    $db = new db_alloc();
    $db->query($query);
    while ($row = $db->row()) {
      if ($row["transactionType"] == "tax") {
        $row["saleTransactionType"] = "tax";
      } else if ($row["pc_productCostID"]) {
        $row["saleTransactionType"] = $row["pc_isPercentage"] ? "aPerc" : "aCost";
      } else if (!$done && $row["transactionType"] == "sale" && !$row["productCostID"]) {
        $done = true;
        $row["saleTransactionType"] = "sellPrice";
      }
      $rows[] = $row;
    }
    return $rows;
  }

  function move_backwards() {
    $current_user = &singleton("current_user");

    if ($this->get_value("status") == "finished" && $current_user->have_role("admin")) {
      $this->set_value("status", "admin");

    } else if ($this->get_value("status") == "admin" && $current_user->have_role("admin")) {
      $this->set_value("status", "allocate");

    } else if ($this->get_value("status") == "allocate") {
      $this->set_value("status", "edit");
    }
  }

  function get_list_filter($filter=array()) {
    $current_user = &singleton("current_user");

    // If they want starred, load up the productSaleID filter element
    if ($filter["starred"]) {
      foreach ((array)$current_user->prefs["stars"]["productSale"] as $k=>$v) {
        $filter["productSaleID"][] = $k;
      }
      is_array($filter["productSaleID"]) or $filter["productSaleID"][] = -1;
    }

    // Filter productSaleID
    $filter["productSaleID"] and $sql[] = sprintf_implode("productSale.productSaleID = %d", $filter["productSaleID"]);

    // No point continuing if primary key specified, so return
    if ($filter["productSaleID"] || $filter["starred"]) {
      return $sql;
    }

    $id_fields = array("clientID","projectID","personID","tfID","productSaleCreatedUser","productSaleModifiedUser");
    foreach($id_fields as $f) {
      $filter[$f] and $sql[] = sprintf_implode("productSale.".$f." = %d", $filter[$f]);
    }

    $filter["status"] and $sql[] = sprintf_implode("productSale.status = '%s'", $filter["status"]);
    
    return $sql;
  }

  function get_list($_FORM=array()) {

    $filter = productSale::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "\n<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "\n<pre>filter: ".print_r($filter,1)."</pre>";

    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }

    $f.= " ORDER BY IFNULL(productSaleDate,productSaleCreatedTime)";

    $db = new db_alloc();
    $query = prepare("SELECT productSale.*, project.projectName, client.clientName
                        FROM productSale 
                   LEFT JOIN client ON productSale.clientID = client.clientID
                   LEFT JOIN project ON productSale.projectID = project.projectID
                    ".$f);
    $db->query($query);
    $statii = productSale::get_statii();
    $people =& get_cached_table("person");
    $rows = array();
    while ($row = $db->next_record()) {
      $productSale = new productSale();
      $productSale->read_db_record($db);
      $row["amounts"] = $productSale->get_amounts();
      $row["statusLabel"] = $statii[$row["status"]];
      $row["salespersonLabel"] = $people[$row["personID"]]["name"];
      $row["creatorLabel"] = $people[$row["productSaleCreatedUser"]]["name"];
      $row["productSaleLink"] = $productSale->get_link();
      $rows[] = $row;
    }

    return (array)$rows;
  }

  function get_link($row=array()) {
    global $TPL;
    if (is_object($this)) {
      return "<a href=\"".$TPL["url_alloc_productSale"]."productSaleID=".$this->get_id()."\">".$this->get_id()."</a>";
    } else {
      return "<a href=\"".$TPL["url_alloc_productSale"]."productSaleID=".$row["productSaleID"]."\">".$row["productSaleID"]."</a>";
    }
  }

  function get_statii() {
    return array("create"=>"Create", "edit"=>"Add Sale Items", "allocate" =>"Allocate", "admin"=>"Administrator", "finished"=>"Completed");
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
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_name(), "selected"=>true, "personID"=>$this->get_value("personID"));
      }
      if ($this->get_value("productSaleCreatedUser")) {
        $p = new person();
        $p->set_id($this->get_value("productSaleCreatedUser"));
        $p->select();
        $p->get_value("emailAddress") and $interestedPartyOptions[$p->get_value("emailAddress")] = array("name"=>$p->get_name(), "selected"=>true, "personID"=>$this->get_value("productSaleCreatedUser"));
      }
      $this_id = $this->get_id();
    }
    // return an aggregation of the current proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("productSale",$this_id,$interestedPartyOptions);
    return $interestedPartyOptions;
  }

  function get_list_vars() {
    return array("return"                         => "[MANDATORY] eg: array | html"
                ,"productSaleID"                  => "Sale that has this ID"
                ,"starred"                        => "Sale that have been starred"
                ,"clientID"                       => "Sales that belong to this Client"
                ,"projectID"                      => "Sales that belong to this Project"
                ,"personID"                       => "Sales for this person"
                ,"status"                         => "Sale status eg: edit | allocate | admin | finished"
                ,"url_form_action"                => "The submit action for the filter form"
                ,"form_name"                      => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"                       => "Specify that the filter preferences should not be saved this time"
                ,"applyFilter"                    => "Saves this filter as the persons preference"
                );
  }

  function load_form_data($defaults=array()) {
    $current_user = &singleton("current_user");

    $page_vars = array_keys(productSale::get_list_vars());

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

  function load_productSale_filter($_FORM) {
    $current_user = &singleton("current_user");

    // display the list of project name.
    $db = new db_alloc();
    if (!$_FORM['showAllProjects']) {
      $filter = "WHERE projectStatus = 'Current' ";
    }
    $query = prepare("SELECT projectID AS value, projectName AS label FROM project $filter ORDER by projectName");
    $rtn["show_project_options"] = page::select_options($query, $_FORM["projectID"],70);

    // display the list of user name.
    if (have_entity_perm("productSale", PERM_READ, $current_user, false)) {
      $rtn["show_userID_options"] = page::select_options(person::get_username_list(), $_FORM["personID"]);
      
    } else {
      $person = new person();
      $person->set_id($current_user->get_id());
      $person->select();
      $person_array = array($current_user->get_id()=>$person->get_name());
      $rtn["show_userID_options"] = page::select_options($person_array, $_FORM["personID"]);
    } 

    // display a list of status
    $status_array = productSale::get_statii();
    unset($status_array["create"]);

    $rtn["show_status_options"] = page::select_options($status_array,$_FORM["status"]);

    // display the date from filter value
    $rtn["showAllProjects"] = $_FORM["showAllProjects"];

 
    $options["clientStatus"] = array("Current");
    $options["return"] = "dropdown_options";
    $ops = client::get_list($options);
    $ops = array_kv($ops,"clientID","clientName");
    $rtn["clientOptions"] = page::select_options($ops,$_FORM["clientID"]);

    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }

  function get_list_html($rows=array(),$_FORM=array()) {
    global $TPL;
    $TPL["productSaleListRows"] = $rows;
    $_FORM["taxName"] = config::get_config_item("taxName");
    $TPL["_FORM"] = $_FORM;
    include_template(dirname(__FILE__)."/../templates/productSaleListS.tpl");
  }
}


?>
