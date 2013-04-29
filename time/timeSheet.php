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

if (!$current_user->is_employee()) {
  alloc_error("You do not have permission to access time sheets",true);
}


  function show_transaction_list($template_name) {
    global $timeSheet;
    global $TPL;

    $db = new db_alloc();

    $amount_so_far = $timeSheet->get_amount_so_far(true);
    $total_incoming = $timeSheet->pay_info["total_customerBilledDollars"];

    $db->query("SELECT * FROM transaction WHERE timeSheetID = %d AND fromTfID != %d
               ",$timeSheet->get_id(),config::get_config_item("inTfID"));

    while ($row = $db->row()) {
      $has_transactions = true;
      $rows[] = $row;
    }
    $total_allocated = transaction::get_actual_amount_used($rows);
    $TPL["total_allocated"] = page::money($timeSheet->get_value("currencyTypeID"),$total_allocated,"%s%mo %c");
    $TPL["total_dollars"] =   page::money($timeSheet->get_value("currencyTypeID"),$timeSheet->pay_info["total_dollars_not_null"],"%s%m %c");
    // used in js preload_field()
    $TPL["total_remaining"] = page::money($timeSheet->get_value("currencyTypeID"),$total_incoming - $amount_so_far,"%m"); 

    if ($has_transactions || $timeSheet->get_value("status") == "invoiced" || $timeSheet->get_value("status") == "finished") {

      if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS) && $timeSheet->get_value("status") == "invoiced") {
        $p_button = "<input style=\"padding:1px 4px\" type=\"submit\" name=\"p_button\" value=\"P\" title=\"Mark transactions pending\">&nbsp;";
        $a_button = "<input style=\"padding:1px 4px\" type=\"submit\" name=\"a_button\" value=\"A\" title=\"Mark transactions approved\">&nbsp;";
        $r_button = "<input style=\"padding:1px 4px\" type=\"submit\" name=\"r_button\" value=\"R\" title=\"Mark transactions rejected\">&nbsp;";
        $session  = "<input type=\"hidden\" name=\"sessID\" value=\"".$TPL["sessID"]."\">";
        $TPL["p_a_r_buttons"] = "<form action=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\" method=\"post\">".$p_button.$a_button.$r_button.$session."</form>";


        $TPL["create_transaction_buttons"] = "<tr><td colspan=\"8\" align=\"center\" style=\"padding:10px;\">";
        $TPL["create_transaction_buttons"].= "<form action=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\" method=\"post\">";

        $TPL["create_transaction_buttons"].= '
         <button type="submit" name="create_transactions_default" value="1" class="save_button">Create Default Transactions<i class="icon-cogs"></i></button>
        ';

        $TPL["create_transaction_buttons"] .= '
        <button type="submit" name="delete_all_transactions" value="1" class="delete_button">Delete Transactions<i class="icon-trash"></i></button>
        ';

        $TPL["create_transaction_buttons"].= "<input type=\"hidden\" name=\"sessID\" value=\"".$TPL["sessID"]."\"></form></tr></tr>";
      }


      

      include_template($template_name);
    }
  }

  function show_transaction_listR($template_name) {

    global $timeSheet;
    global $TPL;
    $current_user = &singleton("current_user");
    global $percent_array;
    $db = new db_alloc();
    $db->query("SELECT * FROM transaction WHERE timeSheetID = %d",$timeSheet->get_id());

    if ($db->next_record() || $timeSheet->get_value("status") == "invoiced" || $timeSheet->get_value("status") == "finished") {

      $db->query("SELECT * 
                    FROM tf 
                   WHERE tfActive = 1
                      OR tfID = %d 
                      OR tfID = %d 
                ORDER BY tfName"
                ,$db->f("tfID"),$db->f("fromTfID"));

      while ($db->row()) {
        $tf_array[$db->f("tfID")] = $db->f("tfName");
      }
      $status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
      $transactionType_options = transaction::get_transactionTypes();


      if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS) && $timeSheet->get_value("status") == "invoiced") {

        $db->query("SELECT * FROM transaction WHERE timeSheetID = %d ORDER BY transactionID",$timeSheet->get_id());

        while ($db->next_record()) {
          $transaction = new transaction();
          $transaction->read_db_record($db);
          $transaction->set_tpl_values("transaction_");

          $TPL["currency"] = page::money($transaction->get_value("currencyTypeID"),'',"%S");
          $TPL["currency_code"] = page::money($transaction->get_value("currencyTypeID"),'',"%C");
          $TPL["tf_options"] = page::select_options($tf_array, $TPL["transaction_tfID"]);
          $TPL["from_tf_options"] = page::select_options($tf_array, $TPL["transaction_fromTfID"]);
          $TPL["status_options"] = page::select_options($status_options, $transaction->get_value("status"));
          $TPL["transactionType_options"] = page::select_options($transactionType_options, $transaction->get_value("transactionType"));
          $TPL["percent_dropdown"] = page::select_options($percent_array, $empty);
          $TPL["transaction_buttons"] = '
            <button type="submit" name="transaction_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
            <button type="submit" name="transaction_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
          ';
          if ($transaction->get_value("transactionType") == "invoice") {
            $TPL["transaction_transactionType"] = $transaction->get_transaction_type_link();
            $TPL["transaction_fromTfID"] = tf::get_name($transaction->get_value("fromTfID"));
            $TPL["transaction_tfID"] = tf::get_name($transaction->get_value("tfID"));
            $TPL["currency_amount"] = page::money($transaction->get_value("currencyTypeID"),$transaction->get_value("amount"),"%S%mo %c");
            include_template("templates/timeSheetTransactionListViewR.tpl");
          } else {
            include_template($template_name);
          }
        }

      } else {

        // If you don't have perm INVOICE TIMESHEETS then only select 
        // transactions which you have permissions to see. 

        $query = prepare("SELECT * 
                            FROM transaction 
                           WHERE timeSheetID = %d
                        ORDER BY transactionID", $timeSheet->get_id());

        $db->query($query);

        while ($db->next_record()) {
          $transaction = new transaction();
          $transaction->read_db_record($db);
          $transaction->set_tpl_values("transaction_");
          unset($TPL["transaction_amount_pos"]);
          unset($TPL["transaction_amount_neg"]);
          $TPL["currency_amount"] = page::money($transaction->get_value("currencyTypeID"),$transaction->get_value("amount"),"%S%mo %c");
          $TPL["transaction_fromTfID"] = tf::get_name($transaction->get_value("fromTfID"));
          $TPL["transaction_tfID"] = tf::get_name($transaction->get_value("tfID"));
          $TPL["transaction_transactionType"] = $transactionType_options[$transaction->get_value("transactionType")];
          include_template("templates/timeSheetTransactionListViewR.tpl");
        }
      }
    }
  }

  function show_new_transaction($template) {
    global $timeSheet;
    global $TPL;
    global $db;
    global $percent_array;

    if ($timeSheet->get_value("status") == "invoiced" && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
      $tf = new tf();
      $options = $tf->get_assoc_array("tfID","tfName");
      $TPL["tf_options"] = page::select_options($options, $none);

      $transactionType_options = transaction::get_transactionTypes();
      $TPL["transactionType_options"] = page::select_options($transactionType_options);

      $status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
      $TPL["status_options"] = page::select_options($status_options);
      $TPL["transaction_timeSheetID"] = $timeSheet->get_id();
      $TPL["transaction_transactionDate"] = date("Y-m-d");
      $TPL["transaction_product"] = "";
      $TPL["transaction_buttons"] = '
            <button type="submit" name="transaction_save" value="1" class="save_button">Add<i class="icon-plus-sign"></i></button>
      ';
      $TPL["percent_dropdown"] = page::select_options($percent_array, $empty);
      include_template($template);
    }
  }

  function show_main_list() {
    global $timeSheet;
    $current_user = &singleton("current_user");
    if (!$timeSheet->get_id()) return;
    
    $db = new db_alloc();
    $q = prepare("SELECT COUNT(*) AS tally FROM timeSheetItem WHERE timeSheetID = %d AND timeSheetItemID != %d",$timeSheet->get_id(),$_POST["timeSheetItem_timeSheetItemID"]);
    $db->query($q);
    $db->next_record();
    if ($db->f("tally")) {
      include_template("templates/timeSheetItemM.tpl");
    }
  }

  function show_timeSheet_list($template) {
    global $TPL;
    global $timeSheet;
    global $db;
    global $tskDesc;
    global $timeSheetItem;
    global $timeSheetID;

    $db_task = new db_alloc();

    if (is_object($timeSheet) && $timeSheet->get_value("status") == "edit") {
      $TPL["timeSheetItem_buttons"] = '
        <button type="submit" name="timeSheetItem_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="timeSheetItem_edit" value="1">Edit<i class="icon-edit"></i></button>';
    }

    $TPL["currency"] = page::money($timeSheet->get_value("currencyTypeID"),'',"%S");

    $timeUnit = new timeUnit();
    $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
    
    $item_query = prepare("SELECT * from timeSheetItem WHERE timeSheetID=%d", $timeSheetID);
    // If editing a timeSheetItem then don't display it in the list
    $timeSheetItemID = $_POST["timeSheetItemID"] or $timeSheetItemID = $_GET["timeSheetItemID"];
    $timeSheetItemID and $item_query.= prepare(" AND timeSheetItemID != %d",$timeSheetItemID);
    $item_query.= prepare(" GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
    $db->query($item_query);

    if (is_object($timeSheet)) {
      $project = $timeSheet->get_foreign_object("project");
      $row_projectPerson = projectPerson::get_projectPerson_row($project->get_id(), $timeSheet->get_value("personID"));
      $default_rate = array();
      if ($row_projectPerson && $row_projectPerson['rate'] > 0) {
        $default_rate['rate'] = $row_projectPerson['rate'];
        $default_rate['unit'] = $row_projectPerson['rateUnitID'];
      }
    }

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem();
      $timeSheetItem->currency = $timeSheet->get_value("currencyTypeID");
      $timeSheetItem->read_db_record($db);
      $timeSheetItem->set_tpl_values("timeSheetItem_");
      

      $TPL["timeSheet_totalHours"] += $timeSheetItem->get_value("timeSheetItemDuration");

      $TPL["unit"] = $unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];

      $br = "";
      $commentPrivateText = "";

      $text = $timeSheetItem->get_value('description',DST_HTML_DISPLAY);
      if ($timeSheetItem->get_value("commentPrivate")) {
        $commentPrivateText = "<b>[Private Comment]</b> ";
      }
      
      $text and $TPL["timeSheetItem_description"] = "<a href=\"".$TPL["url_alloc_task"]."taskID=".$timeSheetItem->get_value('taskID')."\">".$text."</a>";
      $text && $timeSheetItem->get_value("comment") and $br = "<br>";
      $timeSheetItem->get_value("comment") and $TPL["timeSheetItem_comment"] = $br.$commentPrivateText.page::to_html($timeSheetItem->get_value("comment"));
      $TPL["timeSheetItem_unit_times_rate"] = $timeSheetItem->calculate_item_charge($timeSheet->get_value("currencyTypeID"),$timeSheetItem->get_value("rate"));

      $m = new meta("timeSheetItemMultiplier");
      $tsMultipliers = $m->get_list();
      $timeSheetItem->get_value('multiplier') and $TPL["timeSheetItem_multiplier"] = $tsMultipliers[$timeSheetItem->get_value('multiplier')]['timeSheetItemMultiplierName'];
  
      // Check to see if this tsi is part of an overrun
      $TPL["timeSheetItem_class"] = "panel";
      $TPL["timeSheetItem_status"] = "";
      $row_messages = array();
      if($timeSheetItem->get_value('taskID')) {
        $task = new task();
        $task->set_id($timeSheetItem->get_value('taskID'));
        $task->select();
        if($task->get_value('timeLimit') > 0) {
          $total_billed_time = ($task->get_time_billed(false)) / 3600;    // get_time_billed returns seconds, limit hours is in hours
          if($total_billed_time > $task->get_value('timeLimit')) {
            $row_messages []= "<em class='faint warn nobr'>[ Exceeds Limit ]</em>";
          }
        }
      }

      // Highlight the rate if the project person has a non-zero rate and it doesn't match the item's rate
      if ($default_rate) {
        if ($timeSheetItem->get_value('rate') != $default_rate['rate'] ||
          $timeSheetItem->get_value('timeSheetItemDurationUnitID') != $default_rate['unit']) {
            $row_messages []= "<em class='faint warn nobr'>[ Modified rate ]</em>";
          }
      }

      if ($row_messages) {
        $TPL["timeSheetItem_status"] = implode("<br />", $row_messages);
        $TPL["timeSheetItem_class"] = "panel loud";
      }

      include_template($template);

    }

    $TPL["summary_totals"] = $timeSheet->pay_info["summary_unit_totals"];

  }
  
  function show_new_timeSheet($template) {
    global $TPL;
    global $timeSheet;
    global $timeSheetID;
    $current_user = &singleton("current_user");

    // Don't show entry form for new timeSheet.
    if (!$timeSheetID) {
      return;
    } 


    if (is_object($timeSheet) && $timeSheet->get_value("status") == 'edit' 
    && ($timeSheet->get_value("personID") == $current_user->get_id() || $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS))) {

      $TPL["currency"] = page::money($timeSheet->get_value("currencyTypeID"),'',"%S");
      // If we are editing an existing timeSheetItem
      $timeSheetItem_edit = $_POST["timeSheetItem_edit"] or $timeSheetItem_edit = $_GET["timeSheetItem_edit"];
      $timeSheetItemID = $_POST["timeSheetItemID"] or $timeSheetItemID = $_GET["timeSheetItemID"];
      if ($timeSheetItemID && $timeSheetItem_edit) {
        $timeSheetItem = new timeSheetItem();
        $timeSheetItem->currency = $timeSheet->get_value("currencyTypeID");
        $timeSheetItem->set_id($timeSheetItemID);
        $timeSheetItem->select();
        $timeSheetItem->set_values("tsi_");
        $TPL["tsi_rate"] = $timeSheetItem->get_value("rate",DST_HTML_DISPLAY);
        $taskID = $timeSheetItem->get_value("taskID");
        $TPL["tsi_buttons"] = '
         <button type="submit" name="timeSheetItem_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
         <button type="submit" name="timeSheetItem_save" value="1" class="save_button default">Save Item<i class="icon-ok-sign"></i></button>
         ';

        $timeSheetItemDurationUnitID = $timeSheetItem->get_value("timeSheetItemDurationUnitID");
        $TPL["tsi_commentPrivate"] and $TPL["commentPrivateChecked"] = " checked";

        $TPL["ts_rate_editable"] = $timeSheet->can_edit_rate();

        $timeSheetItemMultiplier = $timeSheetItem->get_value("multiplier");

      // Else default values for creating a new timeSheetItem
      } else {
        $TPL["tsi_buttons"] = '<button type="submit" name="timeSheetItem_save" value="1" class="save_button">Add Item<i class="icon-plus-sign"></i></button>';

        $TPL["tsi_personID"] = $current_user->get_id();
        $timeSheet->load_pay_info();
        $TPL["tsi_rate"] = $timeSheet->pay_info["project_rate"];
	      $timeSheetItemDurationUnitID = $timeSheet->pay_info["project_rateUnitID"];
	      $TPL["ts_rate_editable"] = $timeSheet->can_edit_rate();
      }

      $taskID or $taskID = $_GET["taskID"];

      $TPL["taskListDropdown_taskID"] = $taskID;
      $TPL["taskListDropdown"] = $timeSheet->get_task_list_dropdown("mine",$timeSheet->get_id(),$taskID);
      $TPL["tsi_timeSheetID"] = $timeSheet->get_id();

      $timeUnit = new timeUnit();
      $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
      $TPL["tsi_unit_options"] = page::select_options($unit_array, $timeSheetItemDurationUnitID);
      $timeSheetItemDurationUnitID and $TPL["tsi_unit_label"] = $unit_array[$timeSheetItemDurationUnitID];

      $m = new meta("timeSheetItemMultiplier");
      $tsMultipliers = $m->get_list();

      foreach ($tsMultipliers as $k => $v) {
        $multiplier_array[$k] = $v["timeSheetItemMultiplierName"];
      }
      $TPL["tsi_multiplier_options"] = page::select_options($multiplier_array, $timeSheetItemMultiplier);

      include_template($template);
    }
  }

  function show_comments() {
    global $timeSheetID;
    global $TPL;
    global $timeSheet;
    if ($timeSheetID) {
      $TPL["commentsR"] = comment::util_get_comments("timeSheet",$timeSheetID);
      $TPL["class_new_comment"] = "hidden";
      $TPL["allParties"] = $timeSheet->get_all_parties($timeSheet->get_value("projectID")) or $TPL["allParties"] = array();
      $TPL["entity"] = "timeSheet";
      $TPL["entityID"] = $timeSheet->get_id();
      $p = $timeSheet->get_foreign_object('project');
      $TPL["clientID"] = $p->get_value("clientID");
      $commentTemplate = new commentTemplate();
      $ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"timeSheet"));
      $TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);

      $timeSheetPrintOptions = config::get_config_item("timeSheetPrintOptions");
      $timeSheetPrint = config::get_config_item("timeSheetPrint");
      $ops = array(""=>"Format as...");
      foreach ($timeSheetPrint as $value) {
        $ops[$value] = $timeSheetPrintOptions[$value];
      }
      $TPL["attach_extra_files"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
      $TPL["attach_extra_files"].= "Attach Time Sheet ";
      $TPL["attach_extra_files"].= '<select name="attach_timeSheet">'.page::select_options($ops).'</select><br>';
      include_template("../comment/templates/commentM.tpl");
    }
  }



// ============ END FUNCTIONS 

global $timeSheet;
global $timeSheetItem;
global $timeSheetItemID;
global $db;
$current_user = &singleton("current_user");
global $TPL;

$timeSheetID = $_POST["timeSheetID"] or $timeSheetID = $_GET["timeSheetID"];


$db = new db_alloc();
$timeSheet = new timeSheet();

if ($timeSheetID) {
  $timeSheet = new timeSheet();
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->set_values();
} 


// Manually update the Client Billing field
if ($_REQUEST["updateCB"] && $timeSheet->get_id() && $timeSheet->can_edit_rate()) {
  $project = new project();
  $project->set_id($timeSheet->get_value("projectID"));
  $project->select();
  $timeSheet->set_value("customerBilledDollars",page::money($project->get_value("currencyTypeID"),$project->get_value("customerBilledDollars"),"%mo"));
  $timeSheet->set_value("currencyTypeID",$project->get_value("currencyTypeID"));
  $timeSheet->save();
}
// Manually update the person's rate
if ($_REQUEST["updateRate"] && $timeSheet->get_id() && $timeSheet->can_edit_rate()) {
  $row_projectPerson = projectPerson::get_projectPerson_row($timeSheet->get_value("projectID"), $timeSheet->get_value("personID"));
  if (!$row_projectPerson) {
    alloc_error("The person has not been added to the project.");
  } else {
    $q = prepare("SELECT timeSheetItemID from timeSheetItem WHERE timeSheetID = %d",$timeSheet->get_id()); 
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $tsi = new timeSheetItem();
      $tsi->set_id($row["timeSheetItemID"]);
      $tsi->select();
      if ($row_projectPerson["rateUnitID"]) {
        $v = $row_projectPerson["rateUnitID"];
      } else {
        $v = "";
      }
      $tsi->set_value("timeSheetItemDurationUnitID", $v);
      $tsi->set_value("rate",page::money($timeSheet->get_value("currencyTypeID"),$row_projectPerson["rate"],"%mo"));
      $tsi->skip_tsi_status_check = true;
      $tsi->save();
    }
  }
}


if ($_POST["save"]
|| $_POST["save_and_new"]
|| $_POST["save_and_returnToList"]
|| $_POST["save_and_returnToProject"]
|| $_POST["save_and_MoveForward"]
|| $_POST["save_and_MoveBack"]) {

  // Saving a record
  $timeSheet->read_globals();
  $timeSheet->read_globals("timeSheet_");

  $projectID = $timeSheet->get_value("projectID");

  if ($projectID != 0) {
    $project = new project();
    $project->set_id($projectID);
    $project->select();

    $projectManagers = $project->get_timeSheetRecipients();

    if (!$timeSheet->get_id()) {
      $timeSheet->set_value("customerBilledDollars",page::money($project->get_value("currencyTypeID"),$project->get_value("customerBilledDollars"),"%mo"));
      $timeSheet->set_value("currencyTypeID",$project->get_value("currencyTypeID"));
    }
  } else {
    $save_error=true;
    $TPL["message_help"][] = "Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button. A manager must add you to the project before you can create time sheets for it.";
    alloc_error("Please select a Project and then click the Create Time Sheet button.");
  }

  // If it's a Pre-paid project, join this time sheet onto an invoice
  if (is_object($project) && $project->get_id() && $project->get_value("projectType") == "Prepaid") {
    $invoiceID = $project->get_prepaid_invoice();

    if (!$invoiceID) {
      $save_error = true;
      alloc_error("Unable to find a Pre-paid Invoice for this Project or Client.");
    } else if (!$timeSheet->get_id()) {
      $add_timeSheet_to_invoiceID = $invoiceID;
    }
  }

  if ($_POST["save_and_MoveForward"]) {
    $msg.= $timeSheet->change_status("forwards");
  } else if ($_POST["save_and_MoveBack"]) {
    $msg.= $timeSheet->change_status("backwards");
  }

  $timeSheet->set_value("billingNote",rtrim($timeSheet->get_value("billingNote")));

  if ($TPL['message'] || $save_error) {
    // don't save or sql will complain
    $url = $TPL["url_alloc_timeSheet"];

  } else if (!$timeSheet->get_value("personID") && $timeSheetID) {
    //if TS ID is set but person ID is not, it's an existing timesheet this
    // user doesn't have access to (and will overwrite). Don't proceed.
    $url = $TPL["url_alloc_timeSheet"];
  } else if (!$TPL['message'] && $timeSheet->save()) {

    if ($add_timeSheet_to_invoiceID) {
      $invoice = new invoice();
      $invoice->set_id($add_timeSheet_to_invoiceID);
      $invoice->add_timeSheet($timeSheet->get_id());
    }
    if ($_POST["save_and_new"]) {
      $url = $TPL["url_alloc_timeSheet"];
    } else if ($_POST["save_and_returnToList"]) {
      $url = $TPL["url_alloc_timeSheetList"];
    } else if ($_POST["save_and_returnToProject"]) {
      $url = $TPL["url_alloc_project"]."projectID=".$timeSheet->get_value("projectID");
    } else {
      $msg = page::htmlentities(urlencode($msg));
      $url = $TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."&msg=".$msg."&dont_send_email=".$_POST["dont_send_email"];
      # Pass the taskID forward if we came from a task
      $url .= "&taskID=".$_POST["taskID"];
    }
    alloc_redirect($url);
    exit();
  }

} else if ($_POST["delete"]) {
  // Deleting a record
  $timeSheet->read_globals();
  $timeSheet->select();
  $timeSheet->delete();
  alloc_redirect($TPL["url_alloc_timeSheetList"]);


} else if ($timeSheetID) {
  // Displaying a record
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
} else {
  // create a new record
  $timeSheet->read_globals();
  $timeSheet->read_globals("timeSheet_");
  $timeSheet->set_value("status", "edit");
  $TPL["message_help"] = "Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button. A manager must add you to the project before you can create time sheets for it.";
}

// THAT'S THE END OF THE BIG SAVE.  



$person = $timeSheet->get_foreign_object("person");
$TPL["timeSheet_personName"] = $person->get_name();
$timeSheet->set_values("timeSheet_");

if (!$timeSheetID) {
  $timeSheet->set_value("personID", $current_user->get_id());
} 


if ($_POST["create_transactions_default"] && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
  $msg.= $timeSheet->createTransactions();

} else if ($_POST["delete_all_transactions"] && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
  $msg.= $timeSheet->destroyTransactions();
} 




// Take care of saving transactions
if (($_POST["p_button"] || $_POST["a_button"] || $_POST["r_button"]) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {

  if ($_POST["p_button"]) {
    $status = "pending";
  } else if ($_POST["a_button"]) {
    $status = "approved";
  } else if ($_POST["r_button"]) {
    $status = "rejected";
  }

  $query = prepare("UPDATE transaction SET status = '%s' WHERE timeSheetID = %d AND transactionType != 'invoice'", $status, $timeSheet->get_id());
  $db = new db_alloc();
  $db->query($query);
  $db->next_record();

// Take care of the transaction line items on an invoiced timesheet created by admin
} else if (($_POST["transaction_save"] || $_POST["transaction_delete"]) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
  $transaction = new transaction();
  $transaction->read_globals();
  $transaction->read_globals("transaction_");
  if ($_POST["transaction_save"]) {
    if (is_numeric($_POST["percent_dropdown"])) {
      $transaction->set_value("amount", $_POST["percent_dropdown"]);
    }
    $transaction->set_value("currencyTypeID",$timeSheet->get_value("currencyTypeID"));
    $transaction->save();
  } else if ($_POST["transaction_delete"]) {
    $transaction->delete();
  }
}


// display the approved by admin and managers name and date
$person = new person();

if ($timeSheet->get_value("approvedByManagerPersonID")) {
  $person_approvedByManager = new person();
  $person_approvedByManager->set_id($timeSheet->get_value("approvedByManagerPersonID"));
  $person_approvedByManager->select();
  $TPL["timeSheet_approvedByManagerPersonID_username"] = $person_approvedByManager->get_name();
  $TPL["timeSheet_approvedByManagerPersonID"] = $timeSheet->get_value("approvedByManagerPersonID");
}

if ($timeSheet->get_value("approvedByAdminPersonID")) {
  $person_approvedByAdmin = new person();
  $person_approvedByAdmin->set_id($timeSheet->get_value("approvedByAdminPersonID"));
  $person_approvedByAdmin->select();
  $TPL["timeSheet_approvedByAdminPersonID_username"] = $person_approvedByAdmin->get_name();
  $TPL["timeSheet_approvedByAdminPersonID"] = $timeSheet->get_value("approvedByAdminPersonID");
}

// display the project name.
if ($timeSheet->get_value("status") == 'edit' && !$timeSheet->get_value("projectID")) {
  $query = prepare("SELECT * FROM project WHERE projectStatus = 'Current' ORDER by projectName");
    #.prepare("  LEFT JOIN projectPerson on projectPerson.projectID = project.projectID ")
    #.prepare("WHERE projectPerson.personID = '%d' ORDER BY projectName", $current_user->get_id());
} else {
  $query = prepare("SELECT * FROM project ORDER by projectName");
}

// This needs to be just above the newTimeSheet_projectID logic
$projectID = $timeSheet->get_value("projectID");

// If we are entering the page from a project link: New time sheet
if ($_GET["newTimeSheet_projectID"] && !$projectID) {
  
  $_GET["taskID"] and $tid = "&taskID=".$_GET["taskID"];

  $projectID = $_GET["newTimeSheet_projectID"];
  $db = new db_alloc();
  $q = prepare("SELECT * FROM timeSheet WHERE status = 'edit' AND personID = %d AND projectID = %d",$current_user->get_id(),$projectID);
  $db->query($q);
  if ($db->next_record()) {
    alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$db->f("timeSheetID").$tid);
  }
}

if ($_GET["newTimeSheet_projectID"] && !$db->qr("SELECT * FROM projectPerson WHERE personID = %d AND projectID = %d",$current_user->get_id(),$_GET["newTimeSheet_projectID"])) {
  alloc_error("You are not a member of the project (id:".page::htmlentities($_GET["newTimeSheet_projectID"])."), please get a manager to add you to the project.");
}

$db->query($query);
while ($db->row()) {
  $project_array[$db->f("projectID")] = $db->f("projectName");
}
$TPL["timeSheet_projectName"] = $project_array[$projectID];
$TPL["timeSheet_projectID"] = $projectID;
$TPL["taskID"] = $_GET["taskID"];




// Get the project record to determine which button for the edit status.
if ($projectID != 0) {
  $project = new project();
  $project->set_id($projectID);
  $project->select();

  
  $projectManagers = $project->get_timeSheetRecipients();

  if (!$projectManagers) {
    $TPL["managers"] = "N/A";
    $TPL["timeSheet_dateSubmittedToManager"] = "N/A";
    $TPL["timeSheet_approvedByManagerPersonID_username"] = "N/A";
  } else {
    count($projectManagers)>1 and $TPL["manager_plural"] = "s";
    $people =& get_cached_table("person");
    foreach ($projectManagers as $pID) {
      $TPL["managers"].= $commar.$people[$pID]["name"];
      $commar = ", ";
    }

  }

  $clientID = $project->get_value("clientID");
  $projectID = $project->get_id();


  // Get client name
  $client = $project->get_foreign_object("client");
  $TPL["clientName"] = $client_link;
  $TPL["clientID"] = $clientID = $client->get_id();
  $TPL["show_client_options"] = $client_link;
}

list($client_select, $client_link, $project_select, $project_link)
  = client::get_client_and_project_dropdowns_and_links($clientID, $projectID, true);


$TPL["invoice_link"] = $timeSheet->get_invoice_link();
list($amount_used,$amount_allocated) = $timeSheet->get_amount_allocated();
if ($amount_allocated) {
  $TPL["amount_allocated_label"] = "Amount Used / Allocated:";
  $TPL["amount_allocated"] = $amount_allocated;
  $TPL["amount_used"] = $amount_used." / ";
}


if (!$timeSheet->get_id() || $timeSheet->get_value("status") == "edit") {
  $TPL["show_project_options"] = $project_select;
  $TPL["show_client_options"] = $client_select;

} else {
  $TPL["show_project_options"] = $project_link;
  $TPL["show_client_options"] = $client_link;
}


if (is_object($timeSheet) && $timeSheet->get_id() && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS) && !$timeSheet->get_invoice_link() && $timeSheet->get_value("status") != "finished") {

  $p = $timeSheet->get_foreign_object("project");  
  $ops["invoiceStatus"] = "edit";
  $ops["clientID"] = $p->get_value("clientID");
  $ops["return"] = "dropdown_options";
  $invoice_list = invoice::get_list($ops);
  $q = prepare("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$timeSheet->get_id());
  $db = new db_alloc();
  $db->query($q);
  $row = $db->row();
  $sel_invoice = $row["invoiceID"];
  #$TPL["attach_to_invoice_button"] = "<select name=\"attach_to_invoiceID\">";
  #$TPL["attach_to_invoice_button"].= "<option value=\"create_new\">Create New Invoice</option>";
  #$TPL["attach_to_invoice_button"].= page::select_options($invoice_list,$sel_invoice)."</select>";
  #$TPL["attach_to_invoice_button"].= "<input type=\"submit\" name=\"attach_transactions_to_invoice\" value=\"Add to Invoice\"> ";
}

// msg passed in url and print it out pretty..
$msg = $msg or $msg = $_GET["msg"] or $msg = $_POST["msg"];
$msg and $TPL["message_good"][] = $msg;


global $percent_array;
if ($_POST["dont_send_email"]) {
  $TPL["dont_send_email_checked"] = " checked";
} else {
  // if this is the invoice -> completed step it should be checked by default
  if ($timeSheet->get_value("status") == 'invoiced')
    $TPL["dont_send_email_checked"] = " checked";
  else
    $TPL["dont_send_email_checked"] = "";
}

$timeSheet->load_pay_info();


$percent_array = array(""=>"Calculate %",
                       "A"=>"Standard",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 1)=>"100%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.715)=>"71.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.665)=>"66.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.615)=>"61.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.285)=>"28.5%",
                       "B"=>"Agency",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.765)=>"76.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.715)=>"71.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.665)=>"66.5%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.235)=>"23.5%",
                       "C"=>"Commission",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.050)=>"5.0%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.025)=>"2.5%",
                       "D"=>"Old Rates", 
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.772)=>"77.2%", 
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.722)=>"72.2%",
                       sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.228)=>"22.8%");



// display the buttons to move timesheet forward and backward.

if (!$timeSheet->get_id()) {
  $TPL["timeSheet_ChangeStatusButton"] = '<button type="submit" name="save" value="1" class="save_button">Create Time Sheet<i class="icon-ok-sign"></i></button>';
}

$radio_email = "<input type=\"checkbox\" id=\"dont_send_email\" name=\"dont_send_email\" value=\"1\"".$TPL["dont_send_email_checked"]."> <label for=\"dont_send_email\">Don't send email</label><br>";

$statii = timeSheet::get_timeSheet_statii();

if (!$projectManagers) {
  unset($statii["manager"]);
}

foreach ($statii as $s => $label) {
  unset($pre,$suf);// prefix and suffix
  $status = $timeSheet->get_value("status");
  if (!$timeSheet->get_id()) {
    $status = "create";
  } 
  
  if ($s == $status) {
    $pre = "<b>";
    $suf = "</b>";
  }
  $TPL["timeSheet_status_text"].= $sep.$pre.$label.$suf;
  $sep = "&nbsp;&nbsp;|&nbsp;&nbsp;";
}


switch ($timeSheet->get_value("status")) {

case 'edit':
  if (($timeSheet->get_value("personID") == $current_user->get_id() || $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) && ($timeSheetID)) {

    $destlabel = "Admin";
    $projectManagers and $destlabel = "Manager";
    $TPL["timeSheet_ChangeStatusButton"] = '
        <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">Time Sheet to '.$destlabel.'<i class="icon-arrow-right"></i></button>';

  }
  break;

case 'manager':
  if (in_array($current_user->get_id(),$projectManagers)
      || ($timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {

    $TPL["timeSheet_ChangeStatusButton"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">Time Sheet to Admin<i class="icon-arrow-right"></i></button>';

    $TPL["radio_email"] = $radio_email;
  }
  break;

case 'admin':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">Time Sheet to Invoiced<i class="icon-arrow-right"></i></button>';

    $TPL["radio_email"] = $radio_email;

  }
  break;

case 'invoiced':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">Time Sheet Complete<i class="icon-arrow-right"></i></button>';

    $TPL["radio_email"] = $radio_email;
  }
  break;

case 'finished':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>';

  }
  break;

}



// Get recipient_tfID

if ($timeSheet->get_value("status") == "edit") {

  $tf_db = new db_alloc();
  $tf_db->query("select preferred_tfID from person where personID = %d",$timeSheet->get_value("personID"));
  $tf_db->next_record();

  if ($preferred_tfID = $tf_db->f("preferred_tfID")) {

    $tf_db->query("SELECT * 
                     FROM tfPerson 
                    WHERE personID = %d 
                      AND tfID = %d"
                 ,$timeSheet->get_value("personID"), $preferred_tfID);

    if ($tf_db->next_record()) {        // The person has a preferred TF, and is a tfPerson for it too
      $TPL["recipient_tfID_name"] = tf::get_name($tf_db->f("tfID"));
      $TPL["recipient_tfID"] = $tf_db->f("tfID");
    }
  } else {
    $TPL["recipient_tfID_name"] = "No Preferred Payment TF nominated.";
    $TPL["recipient_tfID"] = "";
    $TPL["recipient_tfID_class"] = "bad";
  }

} else {
  $TPL["recipient_tfID_name"] = tf::get_name($timeSheet->get_value("recipient_tfID"));
  $TPL["recipient_tfID"] = $timeSheet->get_value("recipient_tfID");
}


$timeSheet->load_pay_info();
if ($timeSheet->pay_info["total_customerBilledDollars"]) {
  $TPL["total_customerBilledDollars"] = page::money($timeSheet->get_value("currencyTypeID"),$timeSheet->pay_info["total_customerBilledDollars"],"%s%m %c");
  config::get_config_item("taxPercent") and 

$TPL["ex_gst"] = " (".$timeSheet->pay_info["currency"].$timeSheet->pay_info["total_customerBilledDollars_minus_gst"]." excl ".config::get_config_item("taxPercent")."% ".config::get_config_item("taxName").")";


}
if ($timeSheet->pay_info["total_dollars"]) {
  $TPL["total_dollars"] = page::money($timeSheet->get_value("currencyTypeID"),$timeSheet->pay_info["total_dollars"],"%s%m %c");
}

$TPL["total_units"] = $timeSheet->pay_info["summary_unit_totals"];



if ($timeSheetID) {
  $TPL["period"] = $timeSheet->get_value("dateFrom")." to ".$timeSheet->get_value("dateTo");

  if ($timeSheet->get_value("status") == "edit" && $db->f("count") == 0) {
    $TPL["message_help"][] = "Enter Time Sheet Items and click the Add Time Sheet Item Button.";

  } else if ($timeSheet->get_value("status") == "edit" && $db->f("count") > 0) {
    $TPL["message_help"][] = "When finished adding Time Sheet Line Items, click the To Manager/Admin button to submit this Time Sheet.";
  }

}

if ($timeSheetID) {
  $TPL["main_alloc_title"] = "Time Sheet " . $timeSheet->get_id() . " - ".APPLICATION_NAME;
} else {
  $TPL["main_alloc_title"] = "New Time Sheet - ".APPLICATION_NAME;
}

$TPL["taxName"] = config::get_config_item("taxName");
$TPL["ts_rate_editable"] = $timeSheet->can_edit_rate();

$TPL["is_manager"] = $timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS);
$TPL["is_admin"] = $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS);

include_template("templates/timeSheetFormM.tpl");

?>
