<?php
require_once("alloc.inc");

if (!$current_user->is_employee()) {
  die("You do not have permission to access time sheets");
}



function show_transaction_list($template_name) {
  global $timeSheet, $TPL, $current_user, $percent_array;
  $db = new db_alloc;

  if ($timeSheet->get_value("status") == "invoiced") {

    $db->query("SELECT * FROM tf ORDER BY tfName");
    $tf_array = get_array_from_db($db, "tfID", "tfName");
    $status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
    $transactionType_options = array("expense", "invoice", "salary", "commission", "timesheet", "adjustment", "insurance");


    if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {

      $p_button = "<input type=\"submit\" name=\"p_button\" value=\"P\">";
      $a_button = "<input type=\"submit\" name=\"a_button\" value=\"A\">";
      $r_button = "<input type=\"submit\" name=\"r_button\" value=\"R\">";

      $db->query("SELECT SUM(amount) as total FROM transaction WHERE timeSheetID = ".$timeSheet->get_id());
      $db->next_record();
      $total = $db->f("total");

      if (sprintf("%0.2f",$total) == 0) {
        $msg = "(balanced!)";
      } else {
        $total = -$total;
        $msg = "(allocate: ".sprintf("\$%0.2f)",$total);
      }
      echo $TPL["table_box"];
      echo "<th colspan=\"7\">Transactions</th></tr><tr><td colspan=\"5\"></td>";
      echo "<td></td><td>&nbsp;</td></tr>";
      echo "<tr><td>Date</td><td>Product</td>";
      echo "<td>TF</td><td>Amount ".$msg."</td><td>Type</td>";
      echo "<td><form action=\"".$TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheet->get_id()."\" method=\"post\">".$p_button.$a_button.$r_button."</form></td>";
      echo "<td>&nbsp;</td></tr>";

      $db->query("SELECT * from transaction where timeSheetID = ".$timeSheet->get_id()." order by transactionID");

      while ($db->next_record()) {
        $transaction = new transaction;
        $transaction->read_db_record($db);
        $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");
        $TPL["tf_options"] = get_options_from_array($tf_array, $TPL["transaction_tfID"], true, 35);
        $TPL["status_options"] = get_options_from_array($status_options, $transaction->get_value("status"));
        $TPL["transaction_amount"] = number_format($TPL["transaction_amount"], 2, ".", "");
        $TPL["transactionType_options"] = get_options_from_array($transactionType_options, $transaction->get_value("transactionType"), false);
        $TPL["percent_dropdown"] = get_options_from_array($percent_array, $empty, true, 15);
        $TPL["transaction_buttons"] = "<input type=\"submit\" name=\"transaction_save\" value=\"Save\">
                                              <input type=\"submit\" name=\"transaction_delete\" value=\"Delete\">";
        include_template($template_name);
      }

    } else {

      echo $TPL["table_box"];
      echo "<tr><th colspan=\"7\">Transactions</th></tr><tr><td>Date</td><td>Product</td>";
      echo "<td>TF</td><td>Amount</td><td>Type</td><td>Status</td><td>&nbsp;</td></tr>";

      // If you don't have perm INVOICE TIMESHEETS then only select 
      // transactions which you have permissions to see. 

      $query = sprintf("SELECT * FROM transaction, tfPerson 
          WHERE transaction.tfID = tfPerson.tfID 
          AND tfPerson.personID = %d 
          ORDER BY transactionID", $current_user->get_id());

      $db->query($query);

      while ($db->next_record()) {
        $transaction = new transaction;
        $transaction->read_db_record($db);
        $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");
        $TPL["transaction_amount"] = "$".number_format($TPL["transaction_amount"], 2);
        $TPL["transaction_tfID"] = get_tf_name($transaction->get_value("tfID"));
        include_template("templates/timeSheetTransactionListViewR.tpl");
      }

      // Have to finish table here because normally the table would get
      // finished in the show new transaction function, but that won't be
      // called if you don't have perm INVOICE_TIMESHEETS. 

      echo "</table><br>";
    }
  }
}


function show_new_transaction($template) {
  global $timeSheet, $TPL, $db, $percent_array;

  if ($timeSheet->get_value("status") == "invoiced" && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $db->query("SELECT * FROM tf ORDER BY tfName");
    $tf_array = get_array_from_db($db, "tfID", "tfName");
    $TPL["tf_options"] = get_options_from_array($tf_array, $none, true, 35);

    $transactionType_options = array("expense", "invoice", "salary", "commission", "timesheet", "adjustment", "insurance");
    $TPL["transactionType_options"] = get_options_from_array($transactionType_options, $none, false);

    $status_options = array("pending"=>"Pending", "approved"=>"Approved", "rejected"=>"Rejected");
    $TPL["status_options"] = get_options_from_array($status_options, $none);

    $TPL["invoiceItemID"] = $timeSheet->get_value("invoiceItemID");

    $TPL["transaction_timeSheetID"] = $timeSheet->get_id();
    $TPL["transaction_transactionDate"] = date("Y-m-d");
    $TPL["transaction_product"] = "";
    $TPL["transaction_buttons"] = "<input type=\"submit\" name=\"transaction_save\" value=\"Add\">";
    $TPL["percent_dropdown"] = get_options_from_array($percent_array, $empty, true, 15);

    include_template($template);
    echo "</table><br>";
  }
}





function show_main_list() {
  global $timeSheet, $current_user;

  if (!$timeSheet->get_id()) return;
  
  $db = new db_alloc;
  $q = sprintf("SELECT COUNT(*) AS tally FROM timeSheetItem WHERE timeSheetID = %d",$timeSheet->get_id());
  $db->query($q);
  $db->next_record();
  if (!$db->f("tally")) return;

  include_template("templates/timeSheetItemM.tpl");
}




function show_invoice_details() {
  global $timeSheet, $TPL, $db, $timeSheetID, $projectID, $current_user;

  if (($timeSheet->get_value("status") == 'admin' || $timeSheet->get_value("status") == 'invoiced')
      && $timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS)) {

    $timeSheet->load_pay_info();
    $totalPrice = $timeSheet->pay_info["total_dollars"];

    // this is a just-in-case clause. So entries that are close 
    // but for some reason or another aren't coming up.  Just making
    // it a teeny bit flexible.
    $upperTotalPrice = ceil($totalPrice) + 1;
    $lowerTotalPrice = floor($totalPrice) - 1;


    // iiQuantity * iiUnitPrice == iiAmount
    // get invoice drop down list;
    $query = "SELECT invoiceItem.*, 
      invoiceItem.invoiceItemID as iiiiID, 
    invoiceItem.iiMemo 	   as iiiiMemo, 
    invoice.invoiceNum 	   as iiiNum, 	
    invoice.invoiceName	   as iiName, 
    timeSheet.invoiceItemID
      FROM invoiceItem, invoice, timeSheet
      WHERE invoiceItem.invoiceID = invoice.invoiceID
      AND invoiceItem.iiAmount >= ".$lowerTotalPrice."
      AND invoiceItem.iiAmount <= ".$upperTotalPrice."
      AND invoiceItem.invoiceItemID != timeSheet.invoiceItemID";

    if ($timeSheet->get_value("invoiceItemID") && $timeSheet->get_value("status") == "invoiced") {
      $query.= " AND invoiceItem.invoiceItemID = ".$timeSheet->get_value("invoiceItemID");
    }

    $query.= " GROUP BY invoiceItem.invoiceItemID";

    $db->query($query);


    if ($timeSheet->get_value("invoiceItemID") && $timeSheet->get_value("status") == 'invoiced') {
      if ($db->next_record()) {
        $TPL["invoiceItem_options"] = "<a href=\"".$TPL["url_alloc_invoiceItem"]."&invoiceItemID=".$db->f("iiiiID")."\">";
        $TPL["invoiceItem_options"].= $db->f("iiiNum").",  $".$db->f("iiAmount")." ".$db->f("iiiiMemo")."</a>";
      }
    } else if ($timeSheet->get_value("status") == 'admin') {

      // Every second array element is a string separator.  YEA!
      $display_fields = array("", "iiiNum", ",  $", "iiAmount", " ", "iiName", " - ", "iiiiMemo");
      $TPL["invoiceItem_options"] = "<select name=\"timeSheet_invoiceItemID\"><option value=\"\">Potential Invoices</option>";
      $TPL["invoiceItem_options"].= get_options_from_db($db, $display_fields, "iiiiID", $timeSheet->get_value("invoiceItemID"), 100);
      $TPL["invoiceItem_options"].= "</select>";

    }

    include_template("templates/timeSheetInvoiceForm.tpl");
  }
}


function task_exists($taskID) {

  $db = new db_alloc;

  // its not zero is it
  $taskID == 0 ? $rtn = false : $rtn = true;

  // is task in DB
  $query = sprintf("SELECT * FROM task WHERE taskID = %d", $taskID);
  $db->query($query);
  $db->next_record()? $rtn = true : $rtn = false;
  return $rtn;
}


function show_timeSheet_list($template) {
  global $TPL, $timeSheet, $db, $tskDesc;
  global $timeSheetItem, $timeSheetItem_timeSheetItemID, $timeSheetID;

  $db_task = new db_alloc;

  if (is_object($timeSheet) && $timeSheet->get_value("status") == "edit") {
    $TPL["timeSheetItem_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_edit\" value=\"Edit\">";
    $TPL["timeSheetItem_buttons"].= "<input type=\"submit\" name=\"timeSheetItem_delete\" value=\"Delete\">";
  }

  $timeUnit = new timeUnit;
  $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelB");
  
  $item_query = sprintf("SELECT * from timeSheetItem WHERE timeSheetID=%d ", $timeSheetID);
  $item_query.= sprintf("GROUP BY timeSheetItemID ORDER BY dateTimeSheetItem, timeSheetItemID");
  $db->query($item_query);

  while ($db->next_record()) {
    $timeSheetItem = new timeSheetItem;
    $timeSheetItem->read_db_record($db);
    $timeSheetItem->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheetItem_");

    // If editing a timeSheetItem then don't display it in the list
    if ($timeSheetItem_timeSheetItemID == $timeSheetItem->get_id()) {
      continue;
    }  
   
    $TPL["timeSheet_totalHours"] += $timeSheetItem->get_value("timeSheetItemDuration");


    $text = $TPL["timeSheetItem_description_printer_version"] = stripslashes($timeSheetItem->get_value('description'));
    $TPL["timeSheetItem_comment_printer_version"] = "";
    !$timeSheetItem->get_value("commentPrivate") and $TPL["timeSheetItem_comment_printer_version"] = $timeSheetItem->get_value("comment");
    
    $TPL["timeSheetItem_description"] = "<a href=\"".$TPL["url_alloc_task"]."&taskID=".$timeSheetItem->get_value('taskID')."\">".$text."</a>";
    $timeSheetItem->get_value("comment") and $TPL["timeSheetItem_comment"] = "<br/>".$timeSheetItem->get_value("comment");
    $TPL["timeSheetItem_unit_times_rate"] = sprintf("%0.2f",$timeSheetItem->get_value('timeSheetItemDuration') * $timeSheetItem->get_value('rate'));

    include_template($template);

  }

  $TPL["summary_totals"] = $timeSheet->pay_info["summary_unit_totals"];

}




function show_new_timeSheet($template) {
  global $TPL, $timeSheet, $timeSheetID, $db, $current_user, $timeSheetItem_timeSheetItemID;

  // Don't show entry form for new timeSheet.
  if (!$timeSheetID) {
    return;
  } 


  if (is_object($timeSheet) && $timeSheet->get_value("status") == 'edit' 
  && ($timeSheet->get_value("personID") == $current_user->get_id() || $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS))) {

    // If we are editing an existing timeSheetItem
    if ($timeSheetItem_timeSheetItemID) {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->set_id($timeSheetItem_timeSheetItemID);
      $timeSheetItem->select();
      $timeSheetItem->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheetItem_");
      $taskID = $timeSheetItem->get_value("taskID");
      $TPL["timeSheetItem_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_save\" value=\"Save Time Sheet Item\">";
      $TPL["timeSheetItem_buttons"].= "<input type=\"submit\" name=\"timeSheetItem_delete\" value=\"Delete\">";

      $timeSheetItemDurationUnitID = $timeSheetItem->get_value("timeSheetItemDurationUnitID");
      $TPL["timeSheetItem_commentPrivate"] and $TPL["commentPrivateChecked"] = " checked";

    // Else default values for creating a new timeSheetItem
    } else {
      $timeSheetItem = new timeSheetItem;
      $timeSheetItem->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheetItem_");
      $TPL["timeSheetItem_buttons"] = "<input type=\"submit\" name=\"timeSheetItem_save\" value=\"Add Time Sheet Item\">";
      $TPL["timeSheetItem_personID"] = $current_user->get_id();
      $timeSheet->load_pay_info();
      $TPL["timeSheetItem_rate"] = $timeSheet->pay_info["project_rate"];
      $timeSheetItemDurationUnitID = $timeSheet->pay_info["project_rateUnitID"];
    }

    $TPL["taskListDropdown_taskID"] = $taskID;
    $TPL["taskListDropdown"] = $timeSheet->get_task_list_dropdown("my_open",$timeSheet->get_id(),$taskID);
    $TPL["timeSheetItem_timeSheetID"] = $timeSheet->get_id();

    $timeUnit = new timeUnit;
    $unit_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
    $TPL["timeSheetItem_unit_options"] = get_options_from_array($unit_array, $timeSheetItemDurationUnitID);

    #$TPL["timeSheetItem_dateTimeSheetItem"] or $TPL["timeSheetItem_dateTimeSheetItem"] = date("Y-m-d");

    include_template($template);
  }
}




// ============ END FUNCTIONS 

global $timeSheetID, $timeSheet, $timeSheetItem, $timeSheetItemID, $db, $current_user, $personID, $TPL;
global $transaction_save, $transaction_delete, $p_button, $a_button, $r_button;

$db = new db_alloc;

if ($timeSheetID) {
  $timeSheet = new timeSheet;
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
  $timeSheet->set_tpl_values();
}

$timeSheet = new timeSheet;


if (isset($save)
    || isset($save_and_new)
    || isset($save_and_returnToList)
    || isset($save_and_returnToProject)
    || isset($save_and_MoveForward)
    || isset($save_and_invoice)
    || isset($save_and_MoveBack)) {

  if ($save_and_invoice) {
    $save_and_MoveForward = true;
  }
  // Saving a record
  $timeSheet->read_globals();
  $timeSheet->read_globals("timeSheet_");

  if ($invoiceItemID) {
    $invoiceItem = new invoiceItem;
    $invoiceItem->set_id($invoiceItemID);
    $invoiceItem->select() || die("invoiceItemID was a bogey: ".$invoiceItemID);

    $db->query("select invoiceNum from invoice where invoiceID = ".$invoiceItem->get_value("invoiceID"));
    $db->next_record();
    $timeSheet->set_value("invoiceNum", $db->f("invoiceNum"));
  }


  $projectID = $timeSheet->get_value("projectID");

  if ($projectID != 0) {
    $project = new project;
    $project->set_id($projectID);
    $project->select();

    // Get vars for the emails below
    $people_cache = get_cached_table("person");
    $projectManagers = $project->get_timeSheetRecipients();
    $projectName = $project->get_value("projectName");
    $timeSheet_personID_email = $people_cache[$timeSheet->get_value("personID")]["emailAddress"];
    $timeSheet_personID_name  = $people_cache[$timeSheet->get_value("personID")]["name"];
    $config = new config;
    $url = $config->get_config_item("allocURL")."time/timeSheet.php?timeSheetID=".$timeSheet->get_id();
    $admin_name = $people_cache[$config->get_config_item('timeSheetAdminEmail')]["name"];
    $admin_email = $people_cache[$config->get_config_item('timeSheetAdminEmail')]["emailAddress"];



  } else {
    $save_error=true;
    $TPL["message_help"][] = "Step 1/3: Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button.";
  }


  if (isset($save_and_MoveForward)) {

    switch ($timeSheet->get_value("status")) {
    case 'edit':

      if (is_array($projectManagers)) {
        $timeSheet->set_value("status", "manager");
        $timeSheet->set_value("dateSubmittedToManager", date("y-m-d"));
   
        // Send Email 
        foreach ($projectManagers as $pm) {
          $address = $people_cache[$pm]["emailAddress"];
          $subject = "Timesheet submitted for your approval";
          $body = "\n  To Manager: ".$people_cache[$pm]["name"];
          $body.= "\n  Time Sheet: ".$url;
          $body.= "\nSubmitted By: ".$timeSheet_personID_name;
          $body.= "\n For Project: ".$projectName;
          $body.= "\n";
          $body.= "\nA timesheet has been submitted for your approval. If it is satisfactory, submit the";
          $body.= "\ntimesheet to the Administrator. If not, make it editable again for re-submission.";
          $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
          $rtn[] = $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
        }
        is_array($rtn) and $msg.= implode("<br/>",$rtn);

     } else {
        $timeSheet->set_value("status", "admin");
        $timeSheet->set_value("dateSubmittedToAdmin", date("y-m-d"));

        // Send Email
        $address = $admin_email;
        $subject = "Timesheet submitted for your approval";
        $body = "\n    To Admin: ".$admin_name;
        $body.= "\n  Time Sheet: ".$url;
        $body.= "\nSubmitted By: ".$timeSheet_personID_name;
        $body.= "\n For Project: ".$projectName;
        $body.= "\n";
        $body.= "\nA timesheet has been submitted for your approval. If it is not satisfactory, make it";
        $body.= "\neditable again for re-submission.";
        $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
        $msg.= $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
      }
    break;

    case 'manager':
      if (is_array($projectManagers) && (in_array($current_user->get_id(),$projectManagers)) || ($timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {
        $timeSheet->set_value("approvedByManagerPersonID", $current_user->get_id());
        $timeSheet->set_value("dateSubmittedToAdmin", date("y-m-d"));
        $timeSheet->set_value("status", "admin");
        $approvedByManagerPersonID_email = $people_cache[$timeSheet->get_value("approvedByManagerPersonID")]["emailAddress"];
        $approvedByManagerPersonID_name  = $people_cache[$timeSheet->get_value("approvedByManagerPersonID")]["name"];
      
        // Send Email
        $address = $admin_email;
        $subject = "Timesheet submitted for your approval";
        $body = "\n    To Admin: ".$admin_name;
        $body.= "\n  Time Sheet: ".$url;
        $body.= "\nSubmitted By: ".$timeSheet_personID_name;
        $body.= "\n For Project: ".$projectName;
        $body.= "\n Approved By: ".$approvedByManagerPersonID_name;
        $body.= "\n";
        $body.= "\nA timesheet has been submitted for your approval. If it is not satisfactory, make it";
        $body.= "\neditable again for re-submission.";
        $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
        $msg.= $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
      }
    break;

    case 'admin':
      if ($projectManagers && !$timeSheet->get_value("approvedByManagerPersonID")) {
        $timeSheet->set_value("approvedByManagerPersonID", $current_user->get_id());
      }
      $timeSheet->set_value("approvedByAdminPersonID", $current_user->get_id());

      if (!$save_and_invoice) {
        $db->query("select dateFrom from timeSheet where timeSheetID = ".$timeSheet->get_id());
        $db->next_record();
        $msg.= $timeSheet->createTransactions();
      }

      $timeSheet->set_value("status", "invoiced");

    break;
    }


  // They've clicked the <- Back Submit button
  } else if (isset($save_and_MoveBack)) {

    if ($timeSheet->get_value("status") == "invoiced") {
      $timeSheet->destroyTransactions();
      $timeSheet->set_value("approvedByAdminPersonID", "");
      $timeSheet->set_value("status", "admin");

    } else if ($timeSheet->get_value("status") == "admin") {

      if ($timeSheet->get_value("approvedByManagerPersonID")) {
        $timeSheet->set_value("status", "manager");
        $approvedByManagerPersonID_email = $people_cache[$timeSheet->get_value("approvedByManagerPersonID")]["emailAddress"];
        $approvedByManagerPersonID_name  = $people_cache[$timeSheet->get_value("approvedByManagerPersonID")]["name"];
      
        // Send Email
        $address = $approvedByManagerPersonID_email;
        $subject = "Timesheet Rejected";
        $body = "\n  To Manager: ".$approvedByManagerPersonID_name;
        $body.= "\n  Time Sheet: ".$url;
        $body.= "\nSubmitted By: ".$timeSheet_personID_name;
        $body.= "\n For Project: ".$projectName;
        $body.= "\n Rejected By: ".$people_cache[$current_user->get_id()]["name"];
        $body.= "\n";
        $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
        $msg.= $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
       
      } else {
        $timeSheet->set_value("status", "edit");

        // Send Email
        $address = $timeSheet_personID_email;
        $subject = "Timesheet Rejected";
        $body = "\n          To: ".$timeSheet_personID_name;
        $body.= "\n  Time Sheet: ".$url;
        $body.= "\n For Project: ".$projectName;
        $body.= "\n Rejected By: ".$people_cache[$current_user->get_id()]["name"];
        $body.= "\n";
        $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
        $msg.= $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
      }

      $timeSheet->set_value("dateSubmittedToAdmin", "");        // unset 
      $timeSheet->set_value("approvedByAdminPersonID", "");     // unset approved by admin owner.

    } else if ($timeSheet->get_value("status") == "manager") {
      $timeSheet->set_value("status", "edit");
      $timeSheet->set_value("approvedByManagerPersonID", "");   // unset approvedByManagerPersonID
      $timeSheet->set_value("dateSubmittedToManager", "");      // unset

      // Send Email
      $address = $timeSheet_personID_email;
      $subject = "Timesheet Rejected";
      $body = "\n          To: ".$timeSheet_personID_name;
      $body.= "\n  Time Sheet: ".$url;
      $body.= "\n For Project: ".$projectName;
      $body.= "\n Rejected By: ".$people_cache[$current_user->get_id()]["name"];
      $body.= "\n";
      $timeSheet->get_value("billingNote") and $body.= "\n\nBilling Note: ".$timeSheet->get_value("billingNote");
      $msg.= $timeSheet->shootEmail($address, $body, $subject, $dont_send_email);
    }
  }





  // WE ARE STILL WITHIN THAT BIG IF "SAVE" STATEMENT AT THE TOP
  if ($save_error) {
    // don't save or sql will complain
    $url = $TPL["url_alloc_timeSheet"];
  } else if ($timeSheet->save()) {
    if (isset($save_and_new)) {
      $url = $TPL["url_alloc_timeSheet"];
    } else if (isset($save_and_returnToList)) {
      $url = $TPL["url_alloc_timeSheetList"];
    } else if (isset($save_and_returnToProject)) {
      $url = $TPL["url_alloc_project"]."&projectID=".$timeSheet->get_value("projectID");
    } else {
      $msg = htmlentities(urlencode($msg));
      $url = $TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheet->get_id()."&msg=".$msg."&dont_send_email=".$dont_send_email;
    }
    page_close();
    header("Location: $url");
    exit();
  }
} else if (isset($delete)) {
  // Deleting a record
  $timeSheet->read_globals();
  $timeSheet->select();
  $timeSheet->delete();
  header("location: ".$TPL["url_alloc_timeSheetList"]);
} else if (isset($timeSheetID)) {

  if ($timeSheetItem_save || $timeSheetItem_edit || $timeSheetItem_delete) {
    $timeSheetItem = new timeSheetItem;
    $timeSheetItem->read_globals();
    $timeSheetItem->read_globals("timeSheetItem_");
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();

    if ($timeSheetItem_save) {
      // SAVE INDIVIDUAL TIME SHEET ITEM

      if ($timeSheetItem_taskID != 0 && $timeSheetItem_taskID) {
        $db->query("select taskName from task where taskID = ".$timeSheetItem_taskID);
        $db->next_record();
        $taskName = $db->f("taskName");
      }

      $timeSheetItem->set_value("description", $taskName);
      $timeSheetItem_commentPrivate and $timeSheetItem->set_value("commentPrivate", 1);
      $timeSheetItem->save();
      header("Location: ".$TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheetItem->get_value("timeSheetID"));

    } else if ($timeSheetItem_edit) {
      // Hmph. Nothing needs to go here?

    } else if ($timeSheetItem_delete) {
      $timeSheetItem->delete();
      header("Location: ".$TPL["url_alloc_timeSheet"]."&timeSheetID=".$timeSheetID);
    }
  }
  // Displaying a record
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select();
} else {
  // create a new record
  $timeSheet->read_globals();
  $timeSheet->read_globals("timeSheet_");
  $timeSheet->set_value("status", "edit");
  $TPL["message_help"] = "Step 1/3: Begin a Time Sheet by selecting a Project and clicking the Create Time Sheet button.";
}

// THAT'S THE END OF THE BIG SAVE.  

if (!isset($timeSheetID)) {
  $timeSheet->set_value("personID", $current_user->get_id());
}

$person = $timeSheet->get_foreign_object("person");
$TPL["timeSheet_personName"] = $person->get_username(1);

$timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");

$TPL["timeSheet_status_label"] = ucwords($TPL["timeSheet_status"]);

// Take care of the P A R buttons

if (($p_button || $a_button || $r_button) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {

  if ($p_button) {
    $status = "pending";
  } else if ($a_button) {
    $status = "approved";
  } else if ($r_button) {
    $status = "rejected";
  }

  $query = sprintf("update transaction set status = '%s' where timeSheetID = %d", $status, $timeSheet->get_id());
  $db->query($query);
  $db->next_record();
}
// Take care of the transaction line items on an invoiced timesheet created by admin

if (($transaction_save || $transaction_delete) && $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
  $transaction = new transaction;
  $transaction->read_globals();
  $transaction->read_globals("transaction_");
  if ($transaction_save) {
    if (is_numeric($percent_dropdown)) {
      $transaction->set_value("amount", $percent_dropdown);
    }

    $transaction->save();
  } else if ($transaction_delete) {
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
  $query = sprintf("SELECT * FROM project WHERE projectStatus = 'current' ORDER by projectName");
}

$db->query($query);
$project_array = get_array_from_db($db, "projectID", "projectName");
$projectID = $timeSheet->get_value("projectID");
$TPL["timeSheet_projectName"] = $project_array[$projectID];
$TPL["projectID"] = $projectID;



// Get the project record to determine which button for the edit status.
// Get client name

if ($projectID != 0) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();

  
  $projectManagers = $project->get_timeSheetRecipients();
  if (!$projectManagers) {
    $TPL["timeSheet_dateSubmittedToManager"] = "N/A";
    $TPL["timeSheet_approvedByManagerPersonID_username"] = "N/A";
    
  }

  $client = $project->get_foreign_object("client");
  $TPL["clientName"] = $client->get_value("clientName");
  $TPL["cost_centre_link"] = "<a href=\"".$TPL["url_alloc_transactionList"]."&tfID=".$project->get_value("cost_centre_tfID")."\">";
  $TPL["cost_centre_link"].= get_tf_name($project->get_value("cost_centre_tfID"))."</a>";
  $TPL["client_link"] = "<a href=\"".$TPL["url_alloc_client"]."&clientID=".$project->get_value("clientID")."\">".$client->get_value("clientName")."</a>";
}
// THIS IS A GOOD PLACE TO PUT STUFF 

$msg and $TPL["message_good"][] = $msg;

global $dont_send_email, $percent_array;

if ($dont_send_email) {
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
                       "D"=>"Old Rates", sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.772)=>"77.2%", sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.722)=>"72.2%", sprintf("%0.2f", $timeSheet->pay_info["total_dollars"] * 0.228)=>"22.8%");



// display the buttons to move timesheet forward and backward.

if (!$timeSheet->get_id()) {
#<input type=\"submit\" name=\"delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record?')\">
  $TPL["timeSheet_ChangeStatusButton"] = "
    <input type=\"submit\" name=\"save\" value=\"Create Time Sheet\"> ";
}


switch ($timeSheet->get_value("status")) {
case '':
  $TPL["timeSheet_ChangeStatusButton"] = "
      <input type=\"submit\" name=\"save\" value=\"Save\"> 
      <input type=\"submit\" name=\"delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record?')\">
        ";
  break;

case 'edit':
  if (($timeSheet->get_value("personID") == $current_user->get_id() || $timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) && ($timeSheetID)) {
    if ($projectManagers) {
          #<input type=\"submit\" name=\"grab_project_pay_values\" value=\"Re-Grab Pay Details\">
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save\" value=\"Save\"> 
          <input type=\"submit\" name=\"delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record?')\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"To Manager -->\"> ";
    } else {
          #<input type=\"submit\" name=\"grab_project_pay_values\" value=\"Re-Grab Pay Details\">
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save\" value=\"Save\"> 
          <input type=\"submit\" name=\"delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record?')\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"To Admin -->\"> ";
    }
  }
  break;

case 'manager':
  if (in_array($current_user->get_id(),$projectManagers)
      || ($timeSheet->have_perm(PERM_TIME_APPROVE_TIMESHEETS))) {

    $TPL["timeSheet_ChangeStatusButton"] = "
        <input type=\"submit\" name=\"save_and_MoveBack\" value=\"<-- Back\">
        <input type=\"submit\" name=\"save\" value=\"Save\">
        <input type=\"submit\" name=\"save_and_MoveForward\" value=\"To Admin -->\">
        ";
    $TPL["radio_email"] = "<input type=\"checkbox\" name=\"dont_send_email\" value=\"1\"".$TPL["dont_send_email_checked"]."> Don't send email<br>";
  }
  break;

case 'admin':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    if ($projectManagers) {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save_and_MoveBack\" value=\"<-- Back\">
          <input type=\"submit\" name=\"save\" value=\"Save\">
          <input type=\"submit\" name=\"save_and_invoice\" value=\"Mark Invoiced -->\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Mark Invoiced and Create Transactions -->\">
          ";
    } else {
      $TPL["timeSheet_ChangeStatusButton"] = "
          <input type=\"submit\" name=\"save_and_MoveBack\" value=\"<-- Back\">
          <input type=\"submit\" name=\"save\" value=\"Save\">
          <input type=\"submit\" name=\"save_and_invoice\" value=\"Mark Invoiced -->\">
          <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Mark Invoiced and Create Transactions -->\">
          ";
    }

    $TPL["radio_email"] = "<input type=\"checkbox\" name=\"dont_send_email\" value=\"1\"";
    $TPL["radio_email"].= $TPL["dont_send_email_checked"]."> Don't send email<br>";

    if ($timeSheet->transactions_are_complex()) {
      $checked2 = " checked";
    } else {
      $checked1 = " checked";
    }

    $TPL["simple_or_complex_transaction"] = "<nobr><input type=\"radio\" name=\"simple_or_complex_transaction\" value=\"simple\"".$checked1."> New Style</nobr><br>
         <nobr><input type=\"radio\" name=\"simple_or_complex_transaction\" value=\"complex\"".$checked2."> Old Style</nobr>
        ";
  }
  break;

case 'invoiced':
  if ($timeSheet->have_perm(PERM_TIME_INVOICE_TIMESHEETS)) {
    $TPL["timeSheet_ChangeStatusButton"] = "
        <input type=\"submit\" name=\"save_and_MoveBack\" value=\"<-- Back (Will delete all transactions)\">
        <input type=\"submit\" name=\"save\" value=\"Save\">";
  }
  break;
}



// Get recipient_tfID

if ($timeSheet->get_value("status") == "edit") {

  $tf_db = new db_alloc;
  $tf_db->query("select preferred_tfID from person where personID = ".$timeSheet->get_value("personID"));
  $tf_db->next_record();

  if ($preferred_tfID = $tf_db->f("preferred_tfID")) {

    $tf_db->query("select * from tfPerson where personID = ".$timeSheet->get_value("personID")." and tfID = ".$preferred_tfID);

    if ($tf_db->next_record()) {        // The person has a preferred TF, and is a tfPerson for it too
      $TPL["recipient_tfID_name"] = get_tf_name($tf_db->f("tfID"));
      $TPL["recipient_tfID"] = $tf_db->f("tfID");
    }
  } else {
    $TPL["recipient_tfID_name"] = "No Preferred Payment TF nominated.  Go to \"Personal\" and choose one.";
    $TPL["recipient_tfID"] = "";
  }

} else {
  $TPL["recipient_tfID_name"] = get_tf_name($timeSheet->get_value("recipient_tfID"));
  $TPL["recipient_tfID"] = $timeSheet->get_value("recipient_tfID");
}


$timeSheet->load_pay_info();
if ($timeSheet->pay_info["total_customerBilledDollars"]) {
  $TPL["total_customerBilledDollars"] = "$".sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars"]);
  $TPL["ex_gst"] = " ($".sprintf("%0.2f",$timeSheet->pay_info["total_customerBilledDollars_minus_gst"])." excl GST )";
}
if ($timeSheet->pay_info["total_dollars"]) {
  $TPL["total_dollars"] = "$".sprintf("%0.2f",$timeSheet->pay_info["total_dollars"]);
}

$TPL["total_units"] = $timeSheet->pay_info["summary_unit_totals"];

$TPL["timeSheet_payment_insurance_checked"] = $timeSheet->get_value("payment_insurance") ? " checked" : "";

if ($timeSheet->get_value("payment_insurance") == 1) {
$TPL["insurance"] = "Yes";
} else {
  $TPL["insurance"] = "No";
}

// If we are entering the page from a project link: New time sheet
if ($newTimeSheet_projectID && !$projectID) {
  $projectID = $newTimeSheet_projectID;
}
// Set up arrays for the forms.
if (!$TPL["timeSheet_projectName"]) {
  $TPL["show_project_options"] = "<select size=\"1\" name =\"timeSheet_projectID\"><option></option>";
  $TPL["show_project_options"].= get_options_from_array($project_array, $projectID)."</Select>";
} else {
  $TPL["show_project_options"] = "<a href=\"".$TPL["url_alloc_project"]."&projectID=".$TPL["timeSheet_projectID"]."\">".$TPL["timeSheet_projectName"]."</a>";
}


if ($grab_project_pay_values) {
  $query = sprintf("update timeSheetItem set rate = '".$timeSheet->pay_info["project_rate"]."',unit = '".$timeSheet->pay_info["project_rateUnitID"]."' where timeSheetID = %d", $timeSheet->get_id());
  $db->query($query);
  $db->next_record();
}



if ($timeSheetID) {
  $db->query(sprintf("SELECT max(dateTimeSheetItem) AS maxDate, min(dateTimeSheetItem) AS minDate, count(timeSheetItemID) as count
        FROM timeSheetItem WHERE timeSheetID=%d ", $timeSheetID));
  $db->next_record();
  $timeSheet->set_id($timeSheetID);
  $timeSheet->select() || die("unable to determine timeSheetID for purposes of latest date.");
  $timeSheet->set_value("dateFrom", $db->f("minDate"));
  $timeSheet->set_value("dateTo", $db->f("maxDate"));
  $timeSheet->save();
  if ($db->f("minDate") || $db->f("maxDate")) {
    $TPL["period"] = $db->f("minDate")." to ".$db->f("maxDate");
  }

  if ($timeSheet->get_value("status") == "edit" && $db->f("count") == 0) {
    $TPL["message_help"][] = "Step 2/3: Enter line items by inputting the Duration, Amount and Task and clicking the Add Time Sheet Item Button.";

  } else if ($timeSheet->get_value("status") == "edit" && $db->f("count") > 0) {
    $TPL["message_help"][] = "Step 3/3: When finished adding Time Sheet Line Items, click the To Manager/Admin button to submit this Time Sheet (it will no longer be editable).";
  }

}

if ($printVersion) {
  include_template("templates/timeSheetPrintM.tpl");
} else {
  include_template("templates/timeSheetFormM.tpl");
} 


page_close();
?>
