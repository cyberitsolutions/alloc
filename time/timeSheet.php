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

require_once("../alloc.php");

if (!$current_user->is_employee()) {
  die("You do not have permission to access time sheets");
}


  function show_transaction_list($template_name) {
    global $timeSheet, $TPL;

    $db = new db_alloc;
    $db->query(sprintf("SELECT * FROM transaction WHERE timeSheetID = %d",$timeSheet->get_id()));

    if ($db->next_record() || $timeSheet->get_value("status") == "invoiced" || $timeSheet->get_value("status") == "finished") {

      if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS) && $timeSheet->get_value("status") == "invoiced") {
        $p_button = "<input type=\"submit\" name=\"p_button\" value=\"P\">";
        $a_button = "<input type=\"submit\" name=\"a_button\" value=\"A\">";
        $r_button = "<input type=\"submit\" name=\"r_button\" value=\"R\">";
        $TPL["p_a_r_buttons"] = "<form action=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\" method=\"post\">".$p_button.$a_button.$r_button."</form>";

        // If cyber is client
        $project = $timeSheet->get_foreign_object("project");
        if (config::for_cyber() && $project->get_value("clientID") == 13) {
          #$cyber_is_client = " (Cyber is client so pick this button!)";
        }


        $TPL["create_transaction_buttons"] = "<tr><td colspan=\"8\" align=\"center\" style=\"padding:10px;\">";
        $TPL["create_transaction_buttons"].= "<form action=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$timeSheet->get_id()."\" method=\"post\">";
        $TPL["create_transaction_buttons"].= "<input type=\"submit\" name=\"create_transactions_default\" value=\"Create Default Transactions\">";
        $TPL["create_transaction_buttons"].= "&nbsp;";
        config::for_cyber() and $TPL["create_transaction_buttons"].= "<input type=\"submit\" name=\"create_transactions_old\" value=\"Create Old Style Transactions".$cyber_is_client."\">&nbsp;";
        $TPL["create_transaction_buttons"].= "<input type=\"submit\" name=\"delete_all_transactions\" value=\"Delete Transactions\" class=\"delete_button\"></td>";
        $TPL["create_transaction_buttons"].= "</form></tr></tr>";
      }

      $db = new db_alloc;
      $db->query("SELECT SUM(amount) as total FROM transaction WHERE transactionType != 'insurance' AND timeSheetID = %d",$timeSheet->get_id());
      $db->next_record();
      $total = $db->f("total");

      if ($total != $timeSheet->pay_info["total_dollars_not_null"]) {
        $start_bad = "<span class=\"bad\">";
        $end_bad = "</span>";
        $extra = " (allocate: ".sprintf("\$%0.2f",$timeSheet->pay_info["total_dollars_not_null"]-$total).")";
      }

      $TPL["amount_msg"] = $start_bad.$extra.$end_bad;

      include_template($template_name);
    }
  }

  function show_transaction_listR($template_name) {

    global $timeSheet, $TPL, $current_user, $percent_array;
    $db = new db_alloc;
    $db->query(sprintf("SELECT * FROM transaction WHERE timeSheetID = %d",$timeSheet->get_id()));

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
          $transaction = new transaction;
          $transaction->read_db_record($db);
          $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");

          $TPL["tf_options"] = page::select_options($tf_array, $TPL["transaction_tfID"]);
          $TPL["from_tf_options"] = page::select_options($tf_array, $TPL["transaction_fromTfID"]);
          $TPL["status_options"] = page::select_options($status_options, $transaction->get_value("status"));
          $TPL["transaction_amount"] = number_format($TPL["transaction_amount"], 2, ".", "");
          $TPL["transactionType_options"] = page::select_options($transactionType_options, $transaction->get_value("transactionType"));
          $TPL["percent_dropdown"] = page::select_options($percent_array, $empty);
          $TPL["transaction_buttons"] = "<input type=\"submit\" name=\"transaction_save\" value=\"Save\">
                                         <input type=\"submit\" name=\"transaction_delete\" value=\"Delete\">";
          include_template($template_name);
        }

      } else {

        // If you don't have perm INVOICE TIMESHEETS then only select 
        // transactions which you have permissions to see. 

        $query = sprintf("SELECT * 
                            FROM transaction 
                           WHERE timeSheetID = %d
                        ORDER BY transactionID", $timeSheet->get_id());

        $db->query($query);

        while ($db->next_record()) {
          $transaction = new transaction;
          $transaction->read_db_record($db,false);
          $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");
          unset($TPL["transaction_amount_pos"]);
          unset($TPL["transaction_amount_neg"]);
          $TPL["transaction_amount"] = "$".number_format($TPL["transaction_amount"], 2);
          $TPL["transaction_fromTfID"] = tf::get_name($transaction->get_value("fromTfID"));
          $TPL["transaction_tfID"] = tf::get_name($transaction->get_value("tfID"));
          $TPL["transaction_transactionType"] = $transactionType_options[$transaction->get_value("transactionType")];
          include_template("templates/timeSheetTransactionListViewR.tpl");
        }
      }
    }
  }

  function show_new_transaction($template) {
    global $timeSheet, $TPL, $db, $percent_array;

    if ($timeSheet->get_value("status") == "invoiced" && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
      $tf = new tf;
      $options = $tf->get_assoc_array("tfID","tfName");
      $TPL["tf_options"] = page::select_options($options, $none);

      $transactionType_options = transaction::get_transactionTypes();
      $TPL["transactionType_options"] = page::select_options($transactionType_options);

      $status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
      $TPL["status_options"] = page::select_options($status_options);
      $TPL["transaction_timeSheetID"] = $timeSheet->get_id();
      $TPL["transaction_transactionDate"] = date("Y-m-d");
      $TPL["transaction_product"] = "";
      $TPL["transaction_buttons"] = "<input type=\"submit\" name=\"transaction_save\" value=\"Add\">";
      $TPL["percent_dropdown"] = page::select_options($percent_array, $empty);
      include_template($template);
    }
  }

  function show_main_list() {
    global $timeSheet, $current_user;
    if (!$timeSheet->get_id()) return;
    
    $db = new db_alloc;
    $q = sprintf("SELECT COUNT(*) AS tally FROM timeSheetItem WHERE timeSheetID = %d AND timeSheetItemID != %d",$timeSheet->get_id(),$_POST["timeSheetItem_timeSheetItemID"]);
    $db->query($q);
    $db->next_record();
    if ($db->f("tally")) {
      include_template("templates/timeSheetItemM.tpl");
    }
  }

  function show_timeSheet_list($template) {
    global $TPL, $timeSheet, $db, $tskDesc;
    global $timeSheetItem, $timeSheetID;

    $db_task = new db_alloc;

    if (is_object($timeSheet) && $timeSheet->get_value("status") == "edit") {
      $TPL["timeSheetItem_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_edit\" value=\"Edit\">";
      $TPL["timeSheetItem_buttons"].= "<input type=\"submit\" name=\"timeSheetItem_delete\" value=\"Delete\" class=\"delete_button\">";
    }

    $timeUnit = new timeUnit;
    $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
    
    $item_query = sprintf("SELECT * from timeSheetItem WHERE timeSheetID=%d", $timeSheetID);
    // If editing a timeSheetItem then don't display it in the list
    $timeSheetItemID = $_POST["timeSheetItemID"] or $timeSheetItemID = $_GET["timeSheetItemID"];
    $timeSheetItemID and $item_query.= sprintf(" AND timeSheetItemID != %d",$timeSheetItemID);
    $item_query.= sprintf(" GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
    $db->query($item_query);

    while ($db->next_record()) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->read_db_record($db,false);
      $timeSheetItem->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheetItem_");

      $TPL["timeSheet_totalHours"] += $timeSheetItem->get_value("timeSheetItemDuration");

      $TPL["unit"] = $unit_array[$timeSheetItem->get_value("timeSheetItemDurationUnitID")];

      $br = "";
      $commentPrivateText = "";

      $text = $timeSheetItem->get_value('description');
      if ($timeSheetItem->get_value("commentPrivate")) {
        $commentPrivateText = "<b>[Private Comment]</b> ";
      }
      
      $text and $TPL["timeSheetItem_description"] = "<a href=\"".$TPL["url_alloc_task"]."taskID=".$timeSheetItem->get_value('taskID')."\">".$text."</a>";
      $text && $timeSheetItem->get_value("comment") and $br = "<br>";
      $timeSheetItem->get_value("comment") and $TPL["timeSheetItem_comment"] = $br.$commentPrivateText.page::to_html($timeSheetItem->get_value("comment"));
      $TPL["timeSheetItem_unit_times_rate"] = sprintf("%0.2f", $timeSheetItem->calculate_item_charge());

      $m = new meta("timeSheetItemMultiplier");
      $tsMultipliers = $m->get_list();
      $timeSheetItem->get_value('multiplier') and $TPL["timeSheetItem_multiplier"] = $tsMultipliers[$timeSheetItem->get_value('multiplier')]['timeSheetItemMultiplierName'];
  
      $TPL["timeSheetItem_rate"] = sprintf("%0.2f",$TPL["timeSheetItem_rate"]);

      // Check to see if this tsi is part of an overrun
      $TPL["timeSheetItem_class"] = "panel";
      $TPL["timeSheetItem_status"] = "";
      if($timeSheetItem->get_value('taskID')) {
        $task = new task;
        $task->set_id($timeSheetItem->get_value('taskID'));
        $task->select();
        if(floatval($task->get_value('timeEstimate')) > 0) {
          $total_billed_time = ($task->get_time_billed(false)) / 3600;    // get_time_billed returns seconds, estimated hours is in hours
          if($total_billed_time > floatval($task->get_value('timeEstimate'))) {
            $TPL["timeSheetItem_class"] = "panel loud";
            $TPL["timeSheetItem_status"] = "<em class='faint warn nobr'>[ Exceeds time estimate ]</em>";
          }
        }
      }

      include_template($template);

    }

    $TPL["summary_totals"] = $timeSheet->pay_info["summary_unit_totals"];

  }
  
  function show_new_timeSheet($template) {
    global $TPL, $timeSheet, $timeSheetID, $current_user;

    // Don't show entry form for new timeSheet.
    if (!$timeSheetID) {
      return;
    } 


    if (is_object($timeSheet) && $timeSheet->get_value("status") == 'edit' 
    && ($timeSheet->get_value("personID") == $current_user->get_id() || $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS))) {

      // If we are editing an existing timeSheetItem
      $timeSheetItem_edit = $_POST["timeSheetItem_edit"] or $timeSheetItem_edit = $_GET["timeSheetItem_edit"];
      $timeSheetItemID = $_POST["timeSheetItemID"] or $timeSheetItemID = $_GET["timeSheetItemID"];
      if ($timeSheetItemID && $timeSheetItem_edit) {
        $timeSheetItem = new timeSheetItem;
        $timeSheetItem->set_id($timeSheetItemID);
        $timeSheetItem->select();
        $timeSheetItem->set_tpl_values(DST_HTML_ATTRIBUTE, "tsi_");
        $taskID = $timeSheetItem->get_value("taskID");
        $TPL["tsi_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_save\" value=\"Save Time Sheet Item\">";
        $TPL["tsi_buttons"].= "<input type=\"submit\" name=\"timeSheetItem_delete\" value=\"Delete\">";

        $timeSheetItemDurationUnitID = $timeSheetItem->get_value("timeSheetItemDurationUnitID");
        $TPL["tsi_commentPrivate"] and $TPL["commentPrivateChecked"] = " checked";

        $timeSheetItemMultiplier = $timeSheetItem->get_value("multiplier");

      // Else default values for creating a new timeSheetItem
      } else {
        $TPL["tsi_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_save\" value=\"Add Time Sheet Item\">";
        $TPL["tsi_personID"] = $current_user->get_id();
        $timeSheet->load_pay_info();
        $TPL["tsi_rate"] = $timeSheet->pay_info["project_rate"];
        $timeSheetItemDurationUnitID = $timeSheet->pay_info["project_rateUnitID"];
      }

      $taskID or $taskID = $_GET["taskID"];

      $TPL["taskListDropdown_taskID"] = $taskID;
      $TPL["taskListDropdown"] = $timeSheet->get_task_list_dropdown("open",$timeSheet->get_id(),$taskID);
      $TPL["tsi_timeSheetID"] = $timeSheet->get_id();

      $timeUnit = new timeUnit;
      $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
      $TPL["tsi_unit_options"] = page::select_options($unit_array, $timeSheetItemDurationUnitID);

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
    global $timeSheetID, $TPL;
    if ($timeSheetID) {
      $options["showEditButtons"] = true;
      $TPL["commentsR"] = comment::util_get_comments("timeSheet",$timeSheetID,$options);
      include_template("templates/timeSheetCommentM.tpl");
    }
  }



// ============ END FUNCTIONS 

global $timeSheet, $timeSheetItem, $timeSheetItemID, $db, $current_user, $TPL;

$timeSheetID = $_POST["timeSheetID"] or $timeSheetID = $_GET["timeSheetID"];


$db = new db_alloc;
$timeSheet = new timeSheet;

if ($timeSheetID) {
  $timeSheet = new timeSheet;
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->set_tpl_values();
} 


// Hack to manually update the Client Billing field
if ($_GET["CB"]) {
  $project = new project;
  $project->set_id($timeSheet->get_value("projectID"));
  $project->select();
  $timeSheet->set_value("customerBilledDollars",sprintf("%s",$project->get_value("customerBilledDollars")));
  $timeSheet->save();
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
    $project = new project;
    $project->set_id($projectID);
    $project->select();

    $projectManagers = $project->get_timeSheetRecipients();

    if (!$timeSheet->get_id()) {
      $timeSheet->set_value("customerBilledDollars",$project->get_value("customerBilledDollars"));
    }
  } else {
    $save_error=true;
    $TPL["message_help"][] = "Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button.";
    $TPL["message"][] = "Please select a Project and then click the Create Time Sheet button.";
  }

  // If it's a Pre-paid project, join this time sheet onto an invoice
  if (is_object($project) && $project->get_id() && $project->get_value("projectType") == "prepaid") {
    $invoiceID = $project->get_prepaid_invoice();

    if (!$invoiceID) {
      $save_error = true;
      $TPL["message"][] = "Unable to find a Pre-paid Invoice for this Project or Client.";
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

  if ($save_error) {
    // don't save or sql will complain
    $url = $TPL["url_alloc_timeSheet"];

  } else if ($timeSheet->save()) {

    if ($add_timeSheet_to_invoiceID) {
      $invoice = new invoice;
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
  $TPL["message_help"] = "Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button.";
}

// THAT'S THE END OF THE BIG SAVE.  



$person = $timeSheet->get_foreign_object("person");
$TPL["timeSheet_personName"] = $person->get_username(1);
$timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");

if (!$timeSheetID) {
  $timeSheet->set_value("personID", $current_user->get_id());
} 


if (($_POST["create_transactions_default"] || $_POST["create_transactions_old"]) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
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

  $query = sprintf("UPDATE transaction SET status = '%s' WHERE timeSheetID = %d", $status, $timeSheet->get_id());
  $db = new db_alloc;
  $db->query($query);
  $db->next_record();

// Take care of the transaction line items on an invoiced timesheet created by admin
} else if (($_POST["transaction_save"] || $_POST["transaction_delete"]) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
  $transaction = new transaction;
  $transaction->read_globals();
  $transaction->read_globals("transaction_");
  if ($_POST["transaction_save"]) {
    if (is_numeric($_POST["percent_dropdown"])) {
      $transaction->set_value("amount", $_POST["percent_dropdown"]);
    }

    $transaction->save();
  } else if ($_POST["transaction_delete"]) {
    $transaction->delete();
  }
}


// display the approved by admin and managers name and date
$person = new person;

if ($timeSheet->get_value("approvedByManagerPersonID")) {
  $person_approvedByManager = new person;
  $person_approvedByManager->set_id($timeSheet->get_value("approvedByManagerPersonID"));
  $person_approvedByManager->select();
  $TPL["timeSheet_approvedByManagerPersonID_username"] = $person_approvedByManager->get_username(1);
  $TPL["timeSheet_approvedByManagerPersonID"] = $timeSheet->get_value("approvedByManagerPersonID");
}

if ($timeSheet->get_value("approvedByAdminPersonID")) {
  $person_approvedByAdmin = new person;
  $person_approvedByAdmin->set_id($timeSheet->get_value("approvedByAdminPersonID"));
  $person_approvedByAdmin->select();
  $TPL["timeSheet_approvedByAdminPersonID_username"] = $person_approvedByAdmin->get_username(1);
  $TPL["timeSheet_approvedByAdminPersonID"] = $timeSheet->get_value("approvedByAdminPersonID");
}

// display the project name.
if ($timeSheet->get_value("status") == 'edit' && !$timeSheet->get_value("projectID")) {
  $query = sprintf("SELECT * FROM project WHERE projectStatus = 'current' ORDER by projectName");
    #.sprintf("  LEFT JOIN projectPerson on projectPerson.projectID = project.projectID ")
    #.sprintf("WHERE projectPerson.personID = '%d' ORDER BY projectName", $current_user->get_id());
} else {
  $query = sprintf("SELECT * FROM project ORDER by projectName");
}

// This needs to be just above the newTimeSheet_projectID logic
$projectID = $timeSheet->get_value("projectID");

// If we are entering the page from a project link: New time sheet
if ($_GET["newTimeSheet_projectID"] && !$projectID) {
  
  $_GET["taskID"] and $tid = "&taskID=".$_GET["taskID"];

  $projectID = $_GET["newTimeSheet_projectID"];
  $db = new db_alloc;
  $q = sprintf("SELECT * FROM timeSheet WHERE status = 'edit' AND personID = %d AND projectID = %d",$current_user->get_id(),$projectID);
  $db->query($q);
  if ($db->next_record()) {
    alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$db->f("timeSheetID").$tid);
  }
}


$db->query($query);
while ($db->row()) {
  $project_array[$db->f("projectID")] = $db->f("projectName");
}
$TPL["timeSheet_projectName"] = $project_array[$projectID];
$TPL["projectID"] = $projectID;
$TPL["taskID"] = $_GET["taskID"];




// Get the project record to determine which button for the edit status.
if ($projectID != 0) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();

  
  $projectManagers = $project->get_timeSheetRecipients();

  if (!$projectManagers) {
    $TPL["managers"] = "N/A";
    $TPL["timeSheet_dateSubmittedToManager"] = "N/A";
    $TPL["timeSheet_approvedByManagerPersonID_username"] = "N/A";
  } else {
    count($projectManagers)>1 and $TPL["manager_plural"] = "s";
    $people = get_cached_table("person");
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
  = client::get_client_and_project_dropdowns_and_links($clientID, $projectID);


$currency = '$';
$TPL["invoice_link"] = $timeSheet->get_invoice_link();
list($amount_used,$amount_allocated) = $timeSheet->get_amount_allocated();
if ($amount_allocated) {
  $TPL["amount_allocated_label"] = "Amount Used / Allocated:";
  $TPL["amount_allocated"] = $currency.sprintf("%0.2f",$amount_allocated);
  $TPL["amount_used"] = $currency.sprintf("%0.2f",$amount_used)." / ";
}


if (!$timeSheet->get_id()) {
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
  $q = sprintf("SELECT * FROM invoiceItem WHERE timeSheetID = %d",$timeSheet->get_id());
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
  $TPL["dont_send_email_checked"] = "";
}

$timeSheet->load_pay_info();


$percent_array = array(""=>"",
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
  $TPL["timeSheet_ChangeStatusButton"] = "<input type=\"submit\" name=\"save\" value=\"Create Time Sheet\"> ";
}

$radio_email = "<input type=\"checkbox\" id=\"dont_send_email\" name=\"dont_send_email\" value=\"1\"".$TPL["dont_send_email_checked"]."> <label for=\"dont_send_email\">Don't send email</label><br>";

$payment_insurance_checked = $timeSheet->get_value("payment_insurance") ? " checked" : "";
$payment_insurance = "<input type=\"checkbox\" name=\"timeSheet_payment_insurance\" value=\"1\"".$payment_insurance_checked.">";

if ($timeSheet->get_value("payment_insurance") == 1) {
  $payment_insurance_label = "Yes";
} else {
  $payment_insurance_label = "No";
}
$TPL["payment_insurance"] = $payment_insurance_label;


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
    if ($projectManagers) {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"delete\" value=\"Delete\" class=\"delete_button\">
          <input type=\"submit\" name=\"save\" value=\"Save\"> 
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet to Manager --&gt;\"> ";
    } else {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"delete\" value=\"Delete\" class=\"delete_button\">
          <input type=\"submit\" name=\"save\" value=\"Save\"> 
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet to Admin --&gt;\"> ";
    }
  $TPL["payment_insurance"] = $payment_insurance;
  }
  break;

case 'manager':
  if (in_array($current_user->get_id(),$projectManagers)
      || ($timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {

    $TPL["timeSheet_ChangeStatusButton"] = "
        <input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">
        <input type=\"submit\" name=\"save\" value=\"Save\">
        <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet to Admin --&gt;\">
        ";
    $TPL["radio_email"] = $radio_email;
  }
  break;

case 'admin':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    if ($projectManagers) {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">
          <input type=\"submit\" name=\"save\" value=\"Save\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet Invoiced --&gt;\">
          ";
    } else {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">
          <input type=\"submit\" name=\"save\" value=\"Save\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet Invoiced --&gt;\">
          ";
    }

    $TPL["radio_email"] = $radio_email;

  }
  break;

case 'invoiced':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = "
        <input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">
        <input type=\"submit\" name=\"save\" value=\"Save\">
        <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Time Sheet Complete -&gt;\">";

    $TPL["radio_email"] = $radio_email;
    

  }
  break;

case 'finished':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = "<input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">";
  }
  break;

}



// Get recipient_tfID

if ($timeSheet->get_value("status") == "edit") {

  $tf_db = new db_alloc;
  $tf_db->query("select preferred_tfID from person where personID = %s",$timeSheet->get_value("personID"));
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
  }

} else {
  $TPL["recipient_tfID_name"] = tf::get_name($timeSheet->get_value("recipient_tfID"));
  $TPL["recipient_tfID"] = $timeSheet->get_value("recipient_tfID");
}


$timeSheet->load_pay_info();
if ($timeSheet->pay_info["total_customerBilledDollars"]) {
  $TPL["total_customerBilledDollars"] = "$".sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars"]);
  config::get_config_item("taxPercent") and $TPL["ex_gst"] = " ($".sprintf("%s",sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars_minus_gst"]))." excl ".config::get_config_item("taxPercent")."% ".config::get_config_item("taxName").")";
}
if ($timeSheet->pay_info["total_dollars"]) {
  $TPL["total_dollars"] = "$".sprintf("%0.2f",$timeSheet->pay_info["total_dollars"]);
}

$TPL["total_units"] = $timeSheet->pay_info["summary_unit_totals"];





if ($timeSheetID) {
  $db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) AS count
        FROM timeSheetItem WHERE timeSheetID=%d ", $timeSheetID));
  $db->next_record();
  if ($db->f("minDate") || $db->f("maxDate")) {
    $TPL["period"] = $db->f("minDate")." to ".$db->f("maxDate");
  }

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

$TPL["allTimeSheetParties"] = $timeSheet->get_all_timeSheet_parties($timeSheet->get_value("projectID")) or $TPL["allTimeSheetParties"] = array();

$commentTemplate = new commentTemplate();
$ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"timeSheet"));
$TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);


include_template("templates/timeSheetFormM.tpl");

?>
