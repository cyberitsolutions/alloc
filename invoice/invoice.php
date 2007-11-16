<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("../alloc.php");

function show_new_invoiceItem($template) {
  global $TPL, $invoice, $invoiceID, $current_user;

  // Don't show entry form if no ID
  if (!$invoiceID) {
    return;
  }

  $TPL["radio1"] = "checked";
  $TPL["div1"] = "";
  $TPL["radio2"] = "";
  $TPL["div2"] = " class=\"hidden\"";
  $TPL["radio3"] = "";
  $TPL["div3"] = " class=\"hidden\"";


  if (is_object($invoice) && $invoice->get_value("invoiceStatus") == 'edit' && $current_user->have_role('admin')) {

    // If we are editing an existing invoiceItem
    if (is_array($_POST["invoiceItem_edit"])) {
      $invoiceItemID = key($_POST["invoiceItem_edit"]);
      $invoiceItem = new invoiceItem;
      $invoiceItem->set_id($invoiceItemID);
      $invoiceItem->select();
      $invoiceItem->set_tpl_values(DST_HTML_ATTRIBUTE, "invoiceItem_");
      $TPL["invoiceItem_buttons"] = "<input type=\"submit\" name=\"invoiceItem_save[".$invoiceItemID."]\" value=\"Save Invoice Item\">";
      $TPL["invoiceItem_buttons"].= "<input type=\"submit\" name=\"invoiceItem_delete[".$invoiceItemID."]\" value=\"Delete\">";
     
      
      if ($invoiceItem->get_value("timeSheetID")) {
        unset($TPL["div2"]);
        $TPL["div1"] = " class=\"hidden\"";
        $TPL["radio2"] = " checked";
        unset($TPL["radio1"]);

      } else if ($invoiceItem->get_value("expenseFormID")) {
        unset($TPL["div3"]);
        $TPL["div1"] = " class=\"hidden\"";
        $TPL["radio3"] = " checked";
        unset($TPL["radio1"]);
      }

    // Else default values for creating a new invoiceItem
    } else {
      $invoiceItem = new invoiceItem;
      $invoiceItem->set_tpl_values(DST_HTML_ATTRIBUTE, "invoiceItem_");
      $TPL["invoiceItem_buttons"] = "<input type=\"submit\" name=\"invoiceItem_save\" value=\"Add Invoice Item\">";
    }

    $currency = '$';

    // Build dropdown lists for timeSheet and expenseForm options.
    if ($invoice->get_value("clientID")) {

      // Time Sheet dropdown
      $db = new db_alloc();
      $q = sprintf("SELECT projectID FROM project WHERE clientID = %d",$invoice->get_value("clientID"));
      $db->query($q);
      $projectIDs = array();
      while ($row = $db->row()) {
        $projectIDs[] = $row["projectID"];
      }
      if ($projectIDs) {
        $q = sprintf("SELECT timeSheet.*, project.projectName 
                        FROM timeSheet
                   LEFT JOIN project ON project.projectID = timeSheet.projectID 
                       WHERE timeSheet.projectID IN (%s) 
                         AND timeSheet.status != 'finished'
                    GROUP BY timeSheet.timeSheetID
                    ORDER BY dateFrom
                     ",implode(", ",$projectIDs));
        $db->query($q);
    
        $timeSheetStatii = timeSheet::get_timeSheet_statii();

        while ($row = $db->row()) {
          $t = new timeSheet;
          $t->read_db_record($db);
          $t->load_pay_info();
          $dollars = $t->pay_info["total_customerBilledDollars"] or $dollars = $t->pay_info["total_dollars"];
          $timeSheetOptions[$row["timeSheetID"]] = $row["dateFrom"]." ".$currency.sprintf("%0.2f",$dollars)." Time Sheet #".$t->get_id()." for ".person::get_fullname($row["personID"]).", Project: ".$row["projectName"]." [".$timeSheetStatii[$t->get_value("status")]."]";
        }

        $TPL["timeSheetOptions"] = get_select_options($timeSheetOptions,$invoiceItem->get_value("timeSheetID"),150);
      }

      // Expense Form dropdown
      $db = new db_alloc();
      $q = sprintf("SELECT expenseForm.*,transaction.* 
                      FROM transaction 
                 LEFT JOIN expenseForm ON transaction.expenseFormID = expenseForm.expenseFormID 
                     WHERE expenseFormFinalised = 1 
                       AND seekClientReimbursement = 1
                       AND clientID = %d
                  ORDER BY expenseForm.expenseFormID, transaction.transactionDate",$invoice->get_value("clientID"));
      $db->query($q);
      $r = array();
      while ($row = $db->row()) {
        $r[$row["expenseFormID"]] += $row["amount"];
        !$done[$row["expenseFormID"]] and $expenseFormOptions[$row["expenseFormID"]] = "Expense Form #".$row["expenseFormID"]."  %s  ".person::get_fullname($row["enteredBy"]);
        $done[$row["expenseFormID"]] = true;
      }

      foreach ($r as $k => $dollars) {
        $expenseFormOptions[$k] = sprintf($expenseFormOptions[$k],$currency.sprintf("%0.2f",abs($dollars)));
      }

      if ($invoiceItem->get_value("expenseFormID")) {
        $id = $invoiceItem->get_value("expenseFormID");
      }
      $TPL["expenseFormOptions"] = get_select_options($expenseFormOptions,$id,90);
    }

    $TPL["invoiceItem_iiQuantity"] or $TPL["invoiceItem_iiQuantity"] = 1;
    $TPL["invoiceItem_invoiceID"] = $invoice->get_id();

    include_template($template);
  }
}

function show_invoiceItem_list() {
  global $invoiceID, $TPL, $invoice, $current_user;

  $currency = '$';
  $template = "templates/invoiceItemListR.tpl";

  $db = new db_alloc();
  $db2 = new db_alloc();
  $q = sprintf("SELECT * FROM tf WHERE status != 'disabled' ORDER BY tfName");
  $db->query($q);
  #$tf_array = get_array_from_db($db, "tfID", "tfName");

  $q = sprintf("SELECT *
                  FROM invoiceItem 
                 WHERE invoiceItem.invoiceID = %d ORDER by iiDate,invoiceItem.invoiceItemID",$invoiceID);
  $db->query($q);
  while ($db->next_record()) {
    $invoiceItem = new invoiceItem;
    if (!$invoiceItem->read_db_record($db,false)) {
      continue;
    }
    $invoiceItem->set_tpl_values(DST_HTML_ATTRIBUTE, "invoiceItem_");

    unset($transaction_sum);
    unset($transaction_info);
    unset($transaction_statii);
    unset($one_approved);
    unset($one_rejected);
    unset($one_pending);
    unset($br);
    unset($sel);
    unset($amount);
    unset($TPL["invoiceItem_buttons_top"],$TPL["invoiceItem_buttons"],$TPL["transaction_info"],$TPL["status_label"]);

    // If editing a invoiceItem then don't display it in the list
    if (is_array($_POST["invoiceItem_edit"]) && key($_POST["invoiceItem_edit"]) == $invoiceItem->get_id()) {
      continue;
    }
    
    $q = sprintf("SELECT *
                       , transaction.amount AS transaction_amount
                       , transaction.tfID AS transaction_tfID
                       , transaction.status AS transaction_status  
                    FROM transaction 
                   WHERE transaction.invoiceItemID = %d",$invoiceItem->get_id());
    $db2->query($q);
    while ($db2->next_record()) {

      $transaction = new transaction();
      if (!$transaction->read_db_record($db2,false)) {
        $other_peoples_transactions.= "<br>Tansaction access denied for transaction #".$db2->f("transactionID");
        continue;
      }


      if ($db2->f("transaction_status") == "approved") {
        $one_approved = true;
      }
      if ($db2->f("transaction_status") == "rejected") {
        $one_rejected = true;
      }
      if ($db2->f("transaction_status") == "pending") {
        $one_pending = true;
      }

      $amounts[$invoiceItem->get_id()] = $db2->f("transaction_amount");

      $transaction_sum+= $db2->f("transaction_amount");
      $transaction_info.= $br.ucwords($db2->f("transaction_status"))." Transaction ";
      $transaction_info.= "<a href=\"".$TPL["url_alloc_transaction"]."transactionID=".$db2->f("transactionID")."\">#".$db2->f("transactionID")."</a>";
      $transaction_info.= " for <b>".$currency.sprintf("%0.2f",$db2->f("transaction_amount"))."</b>";
      $transaction_info.= " in TF <a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$db2->f("transaction_tfID")."\">";
      $transaction_info.= get_tf_name($db2->f("transaction_tfID"))."</a>";
      $br = "<br>";
    }


    $TPL["transaction_info"] = $transaction_info;
    $TPL["transaction_info"].= $other_peoples_transactions;

    
    // Sets the background colour of the invoice item boxes based on transaction.status
    if (!$one_rejected && !$one_pending && $one_approved) {
      $TPL["box_class"] = " approved";
      $transaction_status = "approved";
    } else if ($one_rejected) {
      $TPL["box_class"] = " rejected";
      $transaction_status = "rejected";
    } else if ($one_pending) {
      $transaction_status = "pending";
      $TPL["box_class"] = " warn";
    } else {
      $TPL["box_class"] = " pending";
      $transaction_status = "";
    }

    if ($transaction_sum > 0 && $transaction_sum < $invoiceItem->get_value("iiAmount")) {
      $TPL["box_class"] = " warn";
    } 

    $sel[$transaction_status] = " checked";

    if ($sel["rejected"]) {
      $TPL["status_label"] = "<b>[Not Going To Be Paid]</b>";

    } else if ($sel["pending"]) {
      $TPL["status_label"] = "<b>[In Dispute]</b>";

    } else if ($sel["approved"]) {
      $TPL["status_label"] = "<b>[Paid]</b>";
    }

    if ($transaction_sum > 0 && $transaction_sum < $invoiceItem->get_value("iiAmount")) {
      $TPL["status_label"] = "<b>[Paid in part]</b>";
    } else if ($transaction_sum > $invoiceItem->get_value("iiAmount")) {
      $TPL["status_label"] = "<b>[Overpaid]</b>";
    }

    $TPL["status_label"] or $TPL["status_label"] = "<b>[No Transactions Created]</b>";



    if ($invoice->get_value("invoiceStatus") == "reconcile") {
      
      if ($amounts[$invoiceItem->get_id()] === null) {
        $amount = $invoiceItem->get_value("iiAmount");
      } else {
        $amount = $amounts[$invoiceItem->get_id()];
      }
      
      $selected_tfID = $db->f("transaction_tfID");
      if (!$selected_tfID && $invoiceItem->get_value("timeSheetID")) {
        $timeSheet = $invoiceItem->get_foreign_object("timeSheet");
        $project = $timeSheet->get_foreign_object("project");
        $selected_tfID = $project->get_value("cost_centre_tfID");
      
      } else if (!$selected_tfID && $invoiceItem->get_value("transactionID")) {
        $transaction = $invoiceItem->get_foreign_object("transaction");
        $project = $transaction->get_foreign_object("project");
        $selected_tfID = $project->get_value("cost_centre_tfID");
        $selected_tfID or $selected_tfID = $transaction->get_value("tfID");
      }
      $selected_tfID or $selected_tfID = config::get_config_item("cybersourceTfID");


      #$tf_options = get_select_options($tf_array, $selected_tfID);
      #$tf_options = "<select name=\"invoiceItemAmountPaidTfID[".$invoiceItem->get_id()."]\">".$tf_options."</select>";
      #$TPL["invoiceItem_buttons"] = $currency."<input size=\"8\" type=\"text\" id=\"ap_".$invoiceItem->get_id()."\" name=\"invoiceItemAmountPaid[".$invoiceItem->get_id()."]\" value=\"".$amount."\">";
      #$TPL["invoiceItem_buttons"].= $tf_options;


      unset($radio_buttons);
      if ($current_user->have_role('admin')) {
      $radio_buttons = "<label for=\"invoiceItemStatus_rejected_".$invoiceItem->get_id()."\">Not Going To Be Paid</label> ";
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_rejected_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"rejected\"".$sel["rejected"].">";

      $radio_buttons.= "&nbsp;&nbsp;&nbsp;<label for=\"invoiceItemStatus_pending_".$invoiceItem->get_id()."\">In Dispute</label> ";
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_pending_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"pending\"".$sel["pending"].">";

      $radio_buttons.= "&nbsp;&nbsp;&nbsp;<label for=\"invoiceItemStatus_approved_".$invoiceItem->get_id()."\">Paid</label> "; 
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_approved_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"approved\"".$sel["approved"].">";

      $TPL["invoiceItem_buttons_top"] = $radio_buttons;
      $TPL["invoiceItem_buttons_top"].= "<input type=\"text\" size=\"7\" name=\"invoiceItemAmountPaid[".$invoiceItem->get_id()."]\" value=\"".$amount."\">";
      $TPL["invoiceItem_buttons_top"].= "<input type=\"hidden\" name=\"invoiceItemAmountPaidTfID[".$invoiceItem->get_id()."]\" value=\"".$selected_tfID."\">";
      }


      unset($TPL["invoiceItem_buttons"]);
        
    } else if ($invoice->get_value("invoiceStatus") == "finished") {


      

    } else if (is_object($invoice) && $invoice->get_value("invoiceStatus") == "edit") {
      $TPL["invoiceItem_buttons"] = "<input type=\"submit\" name=\"invoiceItem_edit[".$invoiceItem->get_id()."]\" value=\"Edit\">";
      $TPL["invoiceItem_buttons"].= "<input type=\"submit\" name=\"invoiceItem_delete[".$invoiceItem->get_id()."]\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record? The transactions associated with this item will be deleted as well.')\">";
    }

    if ($invoiceItem->get_value("timeSheetID")) {
      $TPL["invoiceItem_iiMemo"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$invoiceItem->get_value("timeSheetID")."\">".$invoiceItem->get_value("iiMemo")."</a>";
    } else if ($invoiceItem->get_value("expenseFormID")) {
      $TPL["invoiceItem_iiMemo"] = "<a href=\"".$TPL["url_alloc_expenseForm"]."expenseFormID=".$invoiceItem->get_value("expenseFormID")."\">".$invoiceItem->get_value("iiMemo")."</a>";
    } 

    $TPL["invoiceItem_iiUnitPrice"] = $currency.sprintf("%0.2f",$TPL["invoiceItem_iiUnitPrice"]);
    $TPL["invoiceItem_iiAmount"] = $currency.sprintf("%0.2f",$TPL["invoiceItem_iiAmount"]);

    include_template($template);
  }

}

function show_attachments($invoiceID) {
  util_show_attachments("invoice",$invoiceID);
}

$invoiceID = $_POST["invoiceID"] or $invoiceID = $_GET["invoiceID"];

$db = new db_alloc;
$invoice = new invoice;


if ($invoiceID) {
  $invoice->set_id($invoiceID);
  $invoice->select();
  $invoice->set_tpl_values();
  $invoiceItemIDs = invoice::get_invoiceItems($invoiceID);
}


// If creating a new invoice
if ($_POST["save"] || $_POST["save_and_MoveForward"] || $_POST["save_and_MoveBack"]) {
  $invoice->read_globals();

  // Validation
  if (!$invoice->get_value("clientID")) {
    $TPL["message"][] = "Please select a Client.";
  }

  if (!$invoice->get_value("invoiceNum") || !is_numeric($invoice->get_value("invoiceNum"))) {
    #$TPL["message"][] = "Please enter a unique Invoice Number.";
    $invoice->set_value("invoiceNum",invoice::get_next_invoiceNum());
  } else {
    $invoiceID and $invoiceID_sql = sprintf(" AND invoiceID != %d",$invoiceID);
    $q = sprintf("SELECT * FROM invoice WHERE invoiceNum = '%s' %s",db_esc($invoice->get_value("invoiceNum")),$invoiceID_sql);
    $db->query($q);
    if ($db->row()) {
      $TPL["message"][] = "Please enter a unique Invoice Number (that number is already taken).";
    }
  }

  if (!$invoice->get_value("invoiceName") && $invoice->get_value("clientID")) {
    $client = new client;
    $client->set_id($invoice->get_value("clientID"));
    $client->select();
    $invoice->set_value("invoiceName", $client->get_value("clientName"));
  } 

  if (!$TPL["message"]) {

    if ($_POST["save_and_MoveForward"]) {
      $invoice->change_status("forwards");
      
    } else if ($_POST["save_and_MoveBack"]) {
      $invoice->change_status("backwards");
    }
  }

  if (!$TPL["message"]) {

    if (!$invoice->get_value("invoiceStatus")) {
      $invoice->set_value("invoiceStatus","edit");
    }

    $invoice->save();
    $invoiceID = $invoice->get_id();

    // Save invoice Item approved/rejected info
    if (is_array($_POST["invoiceItemStatus"])) {
      foreach ($_POST["invoiceItemStatus"] as $iiID => $status) {
        $ii = new invoiceItem;
        $ii->set_id($iiID);
        $ii->select();
        $amount = $ii->get_value("iiAmount");
        $q = sprintf("SELECT * FROM transaction WHERE invoiceItemID = %d",$iiID);
        $db = new db_alloc();
        $db->query($q);
        $db->next_record();
        $transaction = new transaction;
        if ($db->f("transactionID")) {
          $transaction->set_id($db->f("transactionID"));
          $transaction->select();
          #$amount = $transaction->get_value("amount");
        }
        $transaction->set_value("amount",sprintf("%0.2f",$_POST["invoiceItemAmountPaid"][$iiID]));  
        $transaction->set_value("tfID",$_POST["invoiceItemAmountPaidTfID"][$iiID]);
        $transaction->set_value("status",$status);
        $transaction->set_value("invoiceID",$ii->get_value("invoiceID"));
        $transaction->set_value("invoiceItemID",$iiID);
        $transaction->set_value("transactionDate",$ii->get_value("iiDate"));
        $transaction->set_value("transactionType","invoice");
        $transaction->set_value("product",sprintf("%s",$ii->get_value("iiMemo")));
        $transaction->save();
      }
    }

    #$TPL["message_good"] = "Invoice saved.";
    header("Location: ".$TPL["url_alloc_invoice"]."invoiceID=".$invoiceID."&msg=Invoice saved.");
  }

} else if ($_POST["delete"] && $invoice->get_value("invoiceStatus") == "edit") {
  
  if ($invoiceItemIDs) {
    $db = new db_alloc();
    $q = sprintf("DELETE FROM transaction WHERE invoiceItemID in (%s)",implode(",",$invoiceItemIDs));
    $db->query($q);
    $q = sprintf("DELETE FROM invoiceItem WHERE invoiceItemID in (%s)",implode(",",$invoiceItemIDs));
    $db->query($q);
  }

  // DONT FORGET TO DELETE/UNLINK THE PDFS DOCUMENTS!!!

  $invoice->delete();
  header("Location: ".$TPL["url_alloc_invoiceList"]);


// Saving editing individual invoiceItems
} else if (($_POST["invoiceItem_save"] || $_POST["invoiceItem_edit"] || $_POST["invoiceItem_delete"]) && $invoice->get_value("invoiceStatus") == "edit") {

  is_array($_POST["invoiceItem_edit"]) and $invoiceItemID = key($_POST["invoiceItem_edit"]);
  is_array($_POST["invoiceItem_delete"]) and $invoiceItemID = key($_POST["invoiceItem_delete"]);

  $invoiceItem = new invoiceItem;
  $invoiceItem->set_id($invoiceItemID);
  #echo $invoiceItem->get_id();
  $invoice->set_id($invoiceID);
  $invoice->select();

  #echo "<pre>".print_r($_POST,1)."</pre>";


  if ($_POST["invoiceItem_save"]) {
    $invoiceItem->read_globals();
    $invoiceItem->read_globals("invoiceItem_");

    if ($_POST["timeSheetID"] && $_POST["split_timeSheet"]) {
      $invoiceItem->add_timeSheetItems($invoiceItem->get_value("invoiceID"),$_POST["timeSheetID"]);

    } else if ($_POST["timeSheetID"]) {
      $invoiceItem->add_timeSheet($invoiceItem->get_value("invoiceID"),$_POST["timeSheetID"]);

    } else if ($_POST["expenseFormID"] && $_POST["split_expenseForm"]) {
      $invoiceItem->add_expenseFormItems($invoiceItem->get_value("invoiceID"),$_POST["expenseFormID"]);

    } else if ($_POST["expenseFormID"]) {
      $invoiceItem->add_expenseForm($invoiceItem->get_value("invoiceID"),$_POST["expenseFormID"]);
    
    } else {
      $invoiceItem->save();
    }

    header("Location: ".$TPL["url_alloc_invoice"]."invoiceID=".$invoiceItem->get_value("invoiceID"));

  } else if ($_POST["invoiceItem_edit"]) {
    // Hmph. Nothing needs to go here?

  } else if ($_POST["invoiceItem_delete"]) {

    $invoiceItem->select();
    $invoiceItem->delete();
    header("Location: ".$TPL["url_alloc_invoice"]."invoiceID=".$invoiceID);
  }
  // Displaying a record
  $invoice->set_id($invoiceID);
  $invoice->select();

// if someone uploads an attachment                                                                                                                                      
} else if ($_POST["save_attachment"]) {
  move_attachment("invoice",$invoiceID);
  header("Location: ".$TPL["url_alloc_invoice"]."invoiceID=".$invoiceID);

} else if ($_POST["generate_pdf"]) {
  $invoice->generate_invoice_file();
  header("Location: ".$TPL["url_alloc_invoice"]."invoiceID=".$invoiceID);
}






if ($invoiceID) {
  
  $currency = '$';

  $q = sprintf("SELECT sum(iiAmount) as sum_iiAmount
                  FROM invoiceItem WHERE invoiceID = %d",$invoiceID);
  $db->query($q);
  $db->next_record();
  $TPL["invoiceTotal"] = $currency.sprintf("%0.2f",$db->f("sum_iiAmount"));

  if ($invoiceItemIDs) {
    $q = sprintf("SELECT sum(amount) as sum_transaction_amount
                    FROM transaction 
                   WHERE status = 'approved' 
                     AND invoiceItemID in (%s)",implode(",",$invoiceItemIDs));
    $db->query($q);
    $db->next_record();
    $TPL["invoiceTotalPaid"] = $currency.sprintf("%0.2f",$db->f("sum_transaction_amount"));
  }


}


$invoice->set_tpl_values();

$statii = invoice::get_invoice_statii();

foreach ($statii as $s => $label) {
  unset($pre,$suf);// prefix and suffix
  $status = $invoice->get_value("invoiceStatus");
  if (!$invoice->get_id()) {
    $status = "create";
  }

  if ($s == $status) {
    $pre = "<b>";
    $suf = "</b>";
  }
  $TPL["invoice_status_label"].= $sep.$pre.$label.$suf;
  $sep = "&nbsp;&nbsp;|&nbsp;&nbsp;";
}


if ($invoice->get_id() && is_dir(ATTACHMENTS_DIR."invoice".DIRECTORY_SEPARATOR.$invoice->get_id())) {
  $rows = get_attachments("invoice",$invoice->get_id());
  foreach ($rows as $arr) {
    if ($invoice->has_attachment_permission($current_user)) {
      $TPL["invoice_download"] .= $commar.$arr["file"]."&nbsp;&nbsp;".$arr["mtime"];
    } else {
      $TPL["invoice_download"] .= $commar.$arr["text"];
    }
    $commar = "<br>";
  }
}


$TPL["field_invoiceNum"] = '<input type="text" name="invoiceNum" value="'.$TPL["invoiceNum"].'">';
$TPL["field_invoiceName"] = '<input type="text" name="invoiceName" value="'.$TPL["invoiceName"].'">';

$c = new client;
$c->set_id($invoice->get_value("clientID"));
$c->select();
$client_label = "<a href=\"".$TPL["url_alloc_client"]."clientID=".$c->get_id()."\">".$c->get_client_name()."</a>";



// Main invoice buttons
if ($current_user->have_role('admin')) {

  if (!$invoiceID) {
    $_GET["clientID"] and $TPL["clientID"] = $_GET["clientID"];
    $TPL["invoice_buttons"] = "<input type=\"submit\" name=\"save\" value=\"Create Invoice\">";
    $options["clientStatus"] = "current";
    $options["return"] = "dropdown_options";
    $ops = client::get_client_list($options);
    $TPL["field_clientID"] = "<select name=\"clientID\"><option value=\"\">".get_select_options($ops,$TPL["clientID"])."</select>";

  } else if ($invoice->get_value("invoiceStatus") == "edit") {
    $TPL["invoice_buttons"] = "
    <input type=\"submit\" name=\"delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this record?')\">
    <input type=\"submit\" name=\"save\" value=\"Save\"> 
    <input type=\"submit\" name=\"save_and_MoveForward\" value=\"".$statii["reconcile"]." --&gt;\"> 
    ";
    $options["clientStatus"] = "current";
    $options["return"] = "dropdown_options";
    $ops = client::get_client_list($options);
    $TPL["field_clientID"] = "<select name=\"clientID\"><option value=\"\">".get_select_options($ops,$invoice->get_value("clientID"))."</select>";

  } else if ($invoice->get_value("invoiceStatus") == "reconcile") {
    $TPL["invoice_buttons"] = "
    <input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">
    <input type=\"submit\" name=\"save\" value=\"Save\">
    <input type=\"submit\" name=\"save_and_MoveForward\" value=\"Invoice to ".$statii["finished"]." --&gt;\">
    ";
    $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
    $TPL["field_invoiceName"] = $TPL["invoiceName"];
    $TPL["field_clientID"] = $client_label;

  } else if ($invoice->get_value("invoiceStatus") == "finished") {
    $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
    $TPL["field_invoiceName"] = $TPL["invoiceName"];
    $TPL["field_clientID"] = $client_label;
    $TPL["invoice_buttons"] = "<input type=\"submit\" name=\"save_and_MoveBack\" value=\"&lt;-- Back\">";
  }
} else {
  $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
  $TPL["field_invoiceName"] = $TPL["invoiceName"];
  $TPL["field_clientID"] = $client_label;
}

if (!$invoice->get_value("clientID")) {
  $options["clientStatus"] = "current";
  $options["return"] = "dropdown_options";
  $ops = client::get_client_list($options);
  $TPL["field_clientID"] = "<select name=\"clientID\"><option value=\"\">".get_select_options($ops,$TPL["clientID"])."</select>";
} 



#$db->query("SELECT * FROM tf ORDER BY tfName");
#$tf_array = get_array_from_db($db, "tfID", "tfName");

if ($invoiceID) {
  $TPL["main_alloc_title"] = "Invoice " . $TPL["invoiceNum"] . " - ".APPLICATION_NAME;
} else {
  $TPL["main_alloc_title"] = "New Invoice - ".APPLICATION_NAME;
}

include_template("templates/invoiceM.tpl");

page_close();



?>
