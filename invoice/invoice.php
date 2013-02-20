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

function show_new_invoiceItem($template) {
  global $TPL;
  global $invoice;
  global $invoiceID;
  $current_user = &singleton("current_user");

  // Don't show entry form if no ID
  if (!$invoiceID) {
    return;
  }

  $TPL["div1"] = "";
  $TPL["div2"] = " class=\"hidden\"";
  $TPL["div3"] = " class=\"hidden\"";
  $TPL["div4"] = " class=\"hidden\"";


  if (is_object($invoice) && $invoice->get_value("invoiceStatus") == 'edit' && $current_user->have_role('admin')) {

    // If we are editing an existing invoiceItem
    if (is_array($_POST["invoiceItem_edit"])) {
      $invoiceItemID = key($_POST["invoiceItem_edit"]);
      $invoiceItem = new invoiceItem();
      $invoiceItem->currency = $invoice->get_value("currencyTypeID");
      $invoiceItem->set_id($invoiceItemID);
      $invoiceItem->select();
      $invoiceItem->set_tpl_values("invoiceItem_");
      $TPL["invoiceItem_buttons"] = '
        <button type="submit" name="invoiceItem_delete['.$invoiceItemID.']" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="invoiceItem_save['.$invoiceItemID.']" value="1" class="save_button">Save Item<i class="icon-edit"></i></button>
      ';
      
      if ($invoiceItem->get_value("timeSheetID")) {
        unset($TPL["div2"]);
        $TPL["div1"] = " class=\"hidden\"";
        $TPL["sbs_link"] = "timeSheet_ii";

      } else if ($invoiceItem->get_value("expenseFormID")) {
        unset($TPL["div3"]);
        $TPL["div1"] = " class=\"hidden\"";
        $TPL["sbs_link"] = "expenseForm_ii";

      } else if ($invoiceItem->get_value("productSaleID")) {
        unset($TPL["div4"]);
        $TPL["div1"] = " class=\"hidden\"";
        $TPL["sbs_link"] = "productSale_ii";
      }

    // Else default values for creating a new invoiceItem
    } else {
      $invoiceItem = new invoiceItem();
      $invoiceItem->set_values("invoiceItem_");
      $TPL["invoiceItem_buttons"] = '
         <button type="submit" name="invoiceItem_save" value="1" class="save_button">Add Item<i class="icon-plus-sign"></i></button>
      ';
    }

    // Build dropdown lists for timeSheet and expenseForm options.
    if ($invoice->get_value("clientID")) {

      // Time Sheet dropdown
      $db = new db_alloc();
      $q = prepare("SELECT projectID FROM project WHERE clientID = %d",$invoice->get_value("clientID"));
      $db->query($q);
      $projectIDs = array();
      while ($row = $db->row()) {
        $projectIDs[] = $row["projectID"];
      }
      if ($projectIDs) {
        $q = prepare("SELECT timeSheet.*, project.projectName 
                        FROM timeSheet
                   LEFT JOIN project ON project.projectID = timeSheet.projectID 
                       WHERE timeSheet.projectID IN (%s) 
                         AND timeSheet.status != 'finished'
                    GROUP BY timeSheet.timeSheetID
                    ORDER BY timeSheetID
                     ",$projectIDs);
        $db->query($q);
    
        $timeSheetStatii = timeSheet::get_timeSheet_statii();

        while ($row = $db->row()) {
          $t = new timeSheet();
          $t->read_db_record($db);
          $t->load_pay_info();
          $dollars = $t->pay_info["total_customerBilledDollars"] or $dollars = $t->pay_info["total_dollars"];
          $timeSheetOptions[$row["timeSheetID"]] = "Time Sheet #".$t->get_id()." ".$row["dateFrom"]." ".$dollars." for ".person::get_fullname($row["personID"]).", Project: ".$row["projectName"]." [".$timeSheetStatii[$t->get_value("status")]."]";
        }

        $TPL["timeSheetOptions"] = page::select_options($timeSheetOptions,$invoiceItem->get_value("timeSheetID"),150);
      }

      // Expense Form dropdown
      $db = new db_alloc();
      $q = prepare("SELECT expenseFormID, expenseFormCreatedUser
                      FROM expenseForm 
                     WHERE expenseFormFinalised = 1 
                       AND seekClientReimbursement = 1
                       AND clientID = %d
                  ORDER BY expenseForm.expenseFormCreatedTime",$invoice->get_value("clientID"));
      $db->query($q);
      while ($row = $db->row()) {
        $expenseFormOptions[$row["expenseFormID"]] = "Expense Form #".$row["expenseFormID"]." ".page::money(config::get_config_item("currency"),expenseForm::get_abs_sum_transactions($row["expenseFormID"]),"%s%m %c")." ".person::get_fullname($row["expenseFormCreatedUser"]);
      }

      if ($invoiceItem->get_value("expenseFormID")) {
        $id = $invoiceItem->get_value("expenseFormID");
      }
      $TPL["expenseFormOptions"] = page::select_options($expenseFormOptions,$id,90);


      $q = prepare("SELECT *
                      FROM productSale
                     WHERE clientID = %d
                       AND status = 'admin'
                   ",$invoice->get_value("clientID"));
      $invoice->get_value("projectID") and $q.= prepare(" AND projectID = %d",$invoice->get_value("projectID"));
      $db->query($q);
      while ($row = $db->row()) {
        $productSale = new productSale();
        $productSale->set_id($row["productSaleID"]);
        $productSale->select();
        $ps_row = $productSale->get_amounts();
        $productSaleOptions[$row["productSaleID"]] = "Sale #".$row["productSaleID"]." ".$ps_row["total_sellPrice"]." ".person::get_fullname($row["personID"]);
      }
      if ($invoiceItem->get_value("productSaleID")) {
        $id = $invoiceItem->get_value("productSaleID");
      }
      $TPL["productSaleOptions"] = page::select_options($productSaleOptions,$id,90);
    }

    $TPL["invoiceItem_iiQuantity"] or $TPL["invoiceItem_iiQuantity"] = 1;
    $TPL["invoiceItem_invoiceID"] = $invoice->get_id();

    include_template($template);
  }
}

function show_invoiceItem_list() {
  global $invoiceID;
  global $TPL;
  global $invoice;
  $current_user = &singleton("current_user");

  $template = "templates/invoiceItemListR.tpl";

  $db = new db_alloc();
  $db2 = new db_alloc();
  $q = prepare("SELECT *
                  FROM invoiceItem 
                 WHERE invoiceItem.invoiceID = %d 
              ORDER BY iiDate,invoiceItem.invoiceItemID"
              ,$invoiceID);
  $db->query($q);
  while ($db->next_record()) {
    $invoiceItem = new invoiceItem();
    $invoiceItem->currency = $invoice->get_value("currencyTypeID");
  
    if (!$invoiceItem->read_db_record($db)) {
      continue;
    }
    $invoiceItem->set_tpl_values("invoiceItem_");

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
    
    $q = prepare("SELECT *
                       , transaction.amount * pow(10,-currencyType.numberToBasic) AS transaction_amount
                       , transaction.tfID AS transaction_tfID
                       , transaction.fromTfID AS transaction_fromTfID
                       , transaction.status AS transaction_status  
                       , transaction.currencyTypeID
                    FROM transaction 
               LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                   WHERE transaction.invoiceItemID = %d",$invoiceItem->get_id());
    $db2->query($q);
    while ($db2->next_record()) {

      $transaction = new transaction();
      if (!$transaction->read_db_record($db2)) {
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

      $amounts[$invoiceItem->get_id()]+= $db2->f("transaction_amount");

      $db2->f("transaction_status") != "rejected" and $transaction_sum+= $db2->f("transaction_amount");
      $transaction_info.= $br.ucwords($db2->f("transaction_status"))." Transaction ";
      $transaction_info.= "<a href=\"".$TPL["url_alloc_transaction"]."transactionID=".$db2->f("transactionID")."\">#".$db2->f("transactionID")."</a>";
      $transaction_info.= " from ";
      $transaction_info.= "<a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$db2->f("transaction_fromTfID")."\">".tf::get_name($db2->f("transaction_fromTfID"))."</a>";
      $transaction_info.= " to <a href=\"".$TPL["url_alloc_transactionList"]."tfID=".$db2->f("transaction_tfID")."\">".tf::get_name($db2->f("transaction_tfID"))."</a>";
      $transaction_info.= " for <b>".page::money($db2->f("currencyTypeID"),$db2->f("transaction_amount"),"%s%m")."</b>";
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

    $sel[$transaction_status] = " checked";

    if ($sel["rejected"]) {
      $TPL["status_label"] = "<b>[Not Going To Be Paid]</b>";

    } else if ($sel["pending"]) {
      $TPL["status_label"] = "<b>[Pending]</b>";

    } else if ($sel["approved"]) {
      $TPL["status_label"] = "<b>[Paid]</b>";
    }

    if ($transaction_sum > 0 && $transaction_sum < $invoiceItem->get_value("iiAmount",DST_HTML_DISPLAY)) {
      $TPL["status_label"] = "<b>[Paid in part]</b>";
      $TPL["box_class"] = " warn";

    } else if ($transaction_sum > $invoiceItem->get_value("iiAmount")) {
      $TPL["status_label"] = "<b>[Overpaid]</b>";
    }

    $TPL["status_label"] or $TPL["status_label"] = "<b>[No Transactions Created]</b>";



    if ($invoice->get_value("invoiceStatus") == "reconcile") {
      
      if ($amounts[$invoiceItem->get_id()] === null) {
        $amount = $invoiceItem->get_value("iiAmount",DST_HTML_DISPLAY);
        if (config::get_config_item("taxPercent") && $invoiceItem->get_value("iiTax") == 0) {
          $amount = page::money($invoice->get_value("currencyTypeID"),$amount * (config::get_config_item("taxPercent") / 100 + 1),"%m");
        }
      } else {
        $amount = page::money($invoice->get_value("currencyTypeID"),$amounts[$invoiceItem->get_id()],"%m");
      }
      
      $selected_tfID = $db2->f("transaction_tfID");
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
      $selected_tfID or $selected_tfID = config::get_config_item("mainTfID");


      #$tf_options = page::select_options($tf_array, $selected_tfID);
      #$tf_options = "<select name=\"invoiceItemAmountPaidTfID[".$invoiceItem->get_id()."]\">".$tf_options."</select>";
      #$TPL["invoiceItem_buttons"] = "<input size=\"8\" type=\"text\" id=\"ap_".$invoiceItem->get_id()."\" name=\"invoiceItemAmountPaid[".$invoiceItem->get_id()."]\" value=\"".$amount."\">";
      #$TPL["invoiceItem_buttons"].= $tf_options;


      unset($radio_buttons);
      if ($current_user->have_role('admin')) {
      $radio_buttons = "<label class='radio corner' for=\"invoiceItemStatus_rejected_".$invoiceItem->get_id()."\">Not Going To Be Paid";
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_rejected_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"rejected\"".$sel["rejected"].">";
      $radio_buttons.= "</label>";

      $radio_buttons.= "&nbsp;&nbsp;";
      $radio_buttons.= "<label class='radio corner' for=\"invoiceItemStatus_pending_".$invoiceItem->get_id()."\">Pending";
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_pending_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"pending\"".$sel["pending"].">";
      $radio_buttons.= "</label>";

      $radio_buttons.= "&nbsp;&nbsp;";
      $radio_buttons.= "<label class='radio corner' for=\"invoiceItemStatus_approved_".$invoiceItem->get_id()."\">Paid"; 
      $radio_buttons.= "<input type=\"radio\" id=\"invoiceItemStatus_approved_".$invoiceItem->get_id()."\" name=\"invoiceItemStatus[".$invoiceItem->get_id()."]\"";
      $radio_buttons.= " value=\"approved\"".$sel["approved"].">";
      $radio_buttons.= "</label>";


      $TPL["invoiceItem_buttons_top"] = $radio_buttons;
      $TPL["invoiceItem_buttons_top"].= "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" size=\"7\" name=\"invoiceItemAmountPaid[".$invoiceItem->get_id()."]\" value=\"".$amount."\">";
      $TPL["invoiceItem_buttons_top"].= "<input type=\"hidden\" name=\"invoiceItemAmountPaidTfID[".$invoiceItem->get_id()."]\" value=\"".$selected_tfID."\">";
      }


      unset($TPL["invoiceItem_buttons"]);
        
    } else if ($invoice->get_value("invoiceStatus") == "finished") {


      

    } else if (is_object($invoice) && $invoice->get_value("invoiceStatus") == "edit") {
      $TPL["invoiceItem_buttons"] = '
        <button type="submit" name="invoiceItem_delete['.$invoiceItem->get_id().']" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="invoiceItem_edit['.$invoiceItem->get_id().']" value="1">Edit<i class="icon-edit"></i></button>
      ';
    }

    if ($invoiceItem->get_value("timeSheetID")) {
      $t = new timeSheet();
      $t->set_id($invoiceItem->get_value("timeSheetID"));
      $t->select();
      $t->load_pay_info();
      $amount = $t->pay_info["total_customerBilledDollars"] or $amount = $t->pay_info["total_dollars"];

      $TPL["invoiceItem_iiMemo"] = "<a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$invoiceItem->get_value("timeSheetID")."\">".$invoiceItem->get_value("iiMemo")." (Currently: $".$amount.", Status: ".$t->get_timeSheet_status().")</a>";


    } else if ($invoiceItem->get_value("expenseFormID")) {
      $ep = $invoiceItem->get_foreign_object("expenseForm");
      $total = $ep->get_abs_sum_transactions();
      $TPL["invoiceItem_iiMemo"] = "<a href=\"".$TPL["url_alloc_expenseForm"]."expenseFormID=".$invoiceItem->get_value("expenseFormID")."\">".$invoiceItem->get_value("iiMemo")." (Currently: ".page::money(config::get_config_item("currency"),$total,"%s%m %c").", Status: ".$ep->get_status().")</a>";
    } 

    $TPL["currency"] = $invoice->get_value("currencyTypeID");
    include_template($template);
  }

}

function show_attachments($invoiceID) {
  global $TPL;
  $options["hide_buttons"] = true;
  util_show_attachments("invoice",$invoiceID, $options);
}

function show_comments() {
  global $invoiceID;
  global $TPL;
  global $invoice;
  if ($invoiceID) {
    $TPL["commentsR"] = comment::util_get_comments("invoice",$invoiceID);
    $TPL["class_new_comment"] = "hidden";
  
    if ($invoice->get_value("projectID")) {
      $project = $invoice->get_foreign_object("project");
      $interestedPartyOptions = $project->get_all_parties($invoice->get_value("projectID"));
      $client = $project->get_foreign_object("client");
      $clientID = $client->get_id();

    } else if ($invoice->get_value("clientID")) {
      $client = $invoice->get_foreign_object("client");
      $interestedPartyOptions = $client->get_all_parties($invoice->get_value("clientID"));
      $clientID = $client->get_id();
    }

    $interestedPartyOptions = interestedParty::get_interested_parties("invoice",$invoice->get_id()
                                                                     ,$interestedPartyOptions);
    $TPL["allParties"] = $interestedPartyOptions or $TPL["allParties"] = array();
    $TPL["entity"] = "invoice";
    $TPL["entityID"] = $invoice->get_id();
    $TPL["clientID"] = $clientID;
    $commentTemplate = new commentTemplate();
    $ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"invoice"));
    $TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);

    $ops = array(""=>"Format as...","generate_pdf" => "PDF Invoice","generate_pdf_verbose"=>"PDF Invoice - verbose");
    $TPL["attach_extra_files"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $TPL["attach_extra_files"].= "Attach Invoice ";
    $TPL["attach_extra_files"].= '<select name="attach_invoice">'.page::select_options($ops).'</select><br>';
    include_template("../comment/templates/commentM.tpl");
  }
}

$invoiceID = $_POST["invoiceID"] or $invoiceID = $_GET["invoiceID"];

$db = new db_alloc();
$invoice = new invoice();


if ($invoiceID) {
  $invoice->set_id($invoiceID);
  $invoice->select();
  $invoice->set_values();
  $invoiceItemIDs = invoice::get_invoiceItems($invoiceID);
}


// If creating a new invoice
if ($_POST["save"] || $_POST["save_and_MoveForward"] || $_POST["save_and_MoveBack"]) {
  $invoice->read_globals();

  // Validation
  if ($invoice->get_value("projectID")) {
    $project = $invoice->get_foreign_object("project");
    $currency = $project->get_value("currencyTypeID");
    $invoice->set_value("clientID",$project->get_value("clientID"));
  }

  if (!$invoice->get_value("clientID")) {
    alloc_error("Please select a Client.");
  }

  $currency or $currency = config::get_config_item("currency");
  $invoice->set_value("currencyTypeID",$currency);


  if (!$invoice->get_value("invoiceNum") || !is_numeric($invoice->get_value("invoiceNum"))) {
    #alloc_error("Please enter a unique Invoice Number.");
    $invoice->set_value("invoiceNum",invoice::get_next_invoiceNum());
  } else {
    $invoiceID and $invoiceID_sql = prepare(" AND invoiceID != %d",$invoiceID);
    $q = prepare("SELECT * FROM invoice WHERE invoiceNum = '%s' ".$invoiceID_sql,$invoice->get_value("invoiceNum"));
    $db->query($q);
    if ($db->row()) {
      alloc_error("Please enter a unique Invoice Number (that number is already taken).");
    }
  }

  if (!$invoice->get_value("invoiceName") && $invoice->get_value("clientID")) {
    $client = new client();
    $client->set_id($invoice->get_value("clientID"));
    $client->select();
    $invoice->set_value("invoiceName", $client->get_value("clientName"));
  } 
  if ($_POST["save_and_MoveForward"]) {
    $direction = "forwards";
  } else if ($_POST["save_and_MoveBack"]) {
    $direction = "backwards";
  }

  if (!$TPL["message"]) {

    if (!$invoice->get_value("invoiceStatus")) {
      $invoice->set_value("invoiceStatus","edit");
    }
    // Save invoice Item approved/rejected info
    $invoiceItemIDs = $invoice->get_invoiceItems();
    foreach ($invoiceItemIDs as $iiID) {
      $status = $_POST["invoiceItemStatus"][$iiID];
      if ($status || $_POST["changeTransactionStatus"]) {
        $_POST["changeTransactionStatus"] and $status = $_POST["changeTransactionStatus"];
        if ($status) {
          $ii = new invoiceItem();
          $ii->set_id($iiID);
          $ii->select();
          $ii->create_transaction($_POST["invoiceItemAmountPaid"][$iiID],$invoice->get_value("tfID"),$status);
        }
      }
    }

    if (!$TPL["message"]) {
      $invoice->change_status($direction);
    }

    $invoice->save();
    $invoiceID = $invoice->get_id();

    $TPL["message_good"][] = "Invoice saved.";
    alloc_redirect($TPL["url_alloc_invoice"]."invoiceID=".$invoiceID.$extra);
  }

} else if ($_POST["delete"] && $invoice->get_value("invoiceStatus") == "edit") {
  
  if ($invoiceItemIDs) {
    $db = new db_alloc();
    $q = prepare("DELETE FROM transaction WHERE invoiceItemID in (%s)",$invoiceItemIDs);
    $db->query($q);
    $q = prepare("DELETE FROM invoiceItem WHERE invoiceItemID in (%s)",$invoiceItemIDs);
    $db->query($q);
  }

  // should probablg delete/unlink the pdf docs

  $invoice->delete();
  $TPL["message_good"][] = "Invoice deleted.";
  alloc_redirect($TPL["url_alloc_invoiceList"]);


// Saving editing individual invoiceItems
} else if (($_POST["invoiceItem_save"] || $_POST["invoiceItem_edit"] || $_POST["invoiceItem_delete"]) && $invoice->get_value("invoiceStatus") == "edit") {

  is_array($_POST["invoiceItem_edit"]) and $invoiceItemID = key($_POST["invoiceItem_edit"]);
  is_array($_POST["invoiceItem_delete"]) and $invoiceItemID = key($_POST["invoiceItem_delete"]);

  $invoiceItem = new invoiceItem();
  $invoiceItem->currency = $invoice->get_value("currencyTypeID");
  $invoiceItem->set_id($invoiceItemID);
  #echo $invoiceItem->get_id();
  $invoice->set_id($invoiceID);
  $invoice->select();

  #echo "<pre>".print_r($_POST,1)."</pre>";

  $_POST["iiTax"] or $_POST["iiTax"] = '';

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

    } else if ($_POST["productSaleID"] && $_POST["split_productSale"]) {
      $invoiceItem->add_productSaleItems($invoiceItem->get_value("invoiceID"),$_POST["productSaleID"]);

    } else if ($_POST["productSaleID"]) {
      $invoiceItem->add_productSale($invoiceItem->get_value("invoiceID"),$_POST["productSaleID"]);

    } else {
      $invoiceItem->save();
    }

    $TPL["message_good"][] = "Invoice Item saved.";
    alloc_redirect($TPL["url_alloc_invoice"]."invoiceID=".$invoiceItem->get_value("invoiceID"));

  } else if ($_POST["invoiceItem_edit"]) {
    // Hmph. Nothing needs to go here?

  } else if ($_POST["invoiceItem_delete"]) {

    $invoiceItem->select();
    $invoiceItem->delete();
    $TPL["message_good"][] = "Invoice Item deleted.";
    alloc_redirect($TPL["url_alloc_invoice"]."invoiceID=".$invoiceID);
  }
  // Displaying a record
  $invoice->set_id($invoiceID);
  $invoice->select();

// if someone uploads an attachment                                                                                                                                      
} else if ($_POST["save_attachment"]) {
  move_attachment("invoice",$invoiceID);
  $TPL["message_good"][] = "Attachment saved.";
  alloc_redirect($TPL["url_alloc_invoice"]."invoiceID=".$invoiceID);
}






if ($invoiceID && $invoiceItemIDs) {
  $currency = $invoice->get_value("currencyTypeID");
  $q = prepare("SELECT SUM(IF((iiTax IS NULL OR iiTax = 0) AND value,
                          (value/100+1) * iiAmount * pow(10,-currencyType.numberToBasic),
                          iiAmount * pow(10,-currencyType.numberToBasic)
                      )) as sum_iiAmount
                  FROM invoiceItem 
             LEFT JOIN invoice on invoiceItem.invoiceID = invoice.invoiceID
             LEFT JOIN currencyType on invoice.currencyTypeID = currencyType.currencyTypeID
             LEFT JOIN config ON config.name = 'taxPercent'
                 WHERE invoiceItem.invoiceID = %d",$invoiceID);
  $db->query($q);
  $db->next_record() and $TPL["invoiceTotal"] = page::money($currency,$db->f("sum_iiAmount"),"%S%m %c");

  $q = prepare("SELECT sum(amount * pow(10,-currencyType.numberToBasic)) as sum_transaction_amount
                  FROM transaction 
             LEFT JOIN currencyType on transaction.currencyTypeID = currencyType.currencyTypeID
                 WHERE status = 'approved' 
                   AND invoiceItemID in (%s)",$invoiceItemIDs);
  $db->query($q);
  $db->next_record() and $TPL["invoiceTotalPaid"] = page::money($currency,$db->f("sum_transaction_amount"),"%S%m %c");
}


$invoice->set_values();

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


# if ($invoice->get_id() && is_dir(ATTACHMENTS_DIR."invoice".DIRECTORY_SEPARATOR.$invoice->get_id())) {
#   $rows = get_attachments("invoice",$invoice->get_id());
#   foreach ($rows as $arr) {
#     if ($invoice->has_attachment_permission($current_user)) {
#       $TPL["invoice_download"] .= $commar.$arr["file"]."&nbsp;&nbsp;".$arr["mtime"];
#     } else {
#       $TPL["invoice_download"] .= $commar.$arr["text"];
#     }
#     $commar = "<br>";
#   }
# }


$TPL["field_invoiceNum"] = '<input type="text" name="invoiceNum" value="'.$TPL["invoiceNum"].'">';
$TPL["field_invoiceName"] = '<input type="text" name="invoiceName" value="'.$TPL["invoiceName"].'">';
$TPL["field_maxAmount"] = '<input type="text" name="maxAmount" size="10" value="'.$invoice->get_value("maxAmount",DST_HTML_DISPLAY).'"> ';
$TPL["field_maxAmount"].= page::help('invoice_maxAmount');
$TPL["field_invoiceDateFrom"] = page::calendar("invoiceDateFrom",$TPL["invoiceDateFrom"]);
$TPL["field_invoiceDateTo"] = page::calendar("invoiceDateTo",$TPL["invoiceDateTo"]);

$clientID = $invoice->get_value("clientID") or $clientID = $_GET["clientID"];
$projectID = $invoice->get_value("projectID") or $projectID = $_GET["projectID"];

list($client_select, $client_link, $project_select, $project_link) 
  = client::get_client_and_project_dropdowns_and_links($clientID, $projectID);

$tf = new tf();
if ($invoice->get_value("tfID")) {
  $tf->set_id($invoice->get_value("tfID"));
  $tf->select();
  $tf_link = $tf->get_link();
  $tf_sel = $invoice->get_value("tfID");
}
$tf_sel or $tf_sel = config::get_config_item("mainTfID");
$tf_select = "<select id='tfID' name='tfID'>".page::select_options($tf->get_assoc_array("tfID","tfName"),$tf_sel)."</select>";


// Main invoice buttons
if ($current_user->have_role('admin')) {

  if (!$invoiceID) {
    $_GET["clientID"] and $TPL["clientID"] = $_GET["clientID"];
    $TPL["invoice_buttons"] = '
         <button type="submit" name="save" value="1" class="save_button">Create Invoice<i class="icon-ok-sign"></i></button>
    ';
    $TPL["field_clientID"] = $client_select;
    $TPL["field_projectID"] = $project_select;
    $TPL["field_tfID"] = $tf_select;

  } else if ($invoice->get_value("invoiceStatus") == "edit") {
    $TPL["invoice_buttons"] = '
        <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">'.$statii["reconcile"].'<i class="icon-arrow-right"></i></button>
    ';
    $options["clientStatus"] = "Current";
    $options["return"] = "dropdown_options";
    $ops = client::get_list($options);
    $TPL["field_clientID"] = $client_select;
    $TPL["field_projectID"] = $project_select;
    $TPL["field_tfID"] = $tf_select;

  } else if ($invoice->get_value("invoiceStatus") == "reconcile") {

    $TPL["invoice_buttons"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        <button type="submit" name="save_and_MoveForward" value="1" class="save_button">Invoice to '.$statii["finished"].'<i class="icon-arrow-right"></i></button>
        <select name="changeTransactionStatus"><option value="">Transaction Status<option value="approved">Approve<option value="rejected">Reject</select>';

    $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
    $TPL["field_invoiceName"] = page::htmlentities($TPL["invoiceName"]);
    $TPL["field_clientID"] = $client_link;
    $TPL["field_projectID"] = $project_link;
    $TPL["field_tfID"] = $tf_link;
    $TPL["field_maxAmount"] = page::money($currency,$TPL["maxAmount"],"%s%mo %c");
    $TPL["field_invoiceDateFrom"] = $TPL["invoiceDateFrom"];
    $TPL["field_invoiceDateTo"] = $TPL["invoiceDateTo"];

  } else if ($invoice->get_value("invoiceStatus") == "finished") {
    $TPL["invoice_buttons"] = '
        <button type="submit" name="save_and_MoveBack" value="1" class="save_button"><i class="icon-arrow-left" style="margin:0px; margin-right:5px"></i>Back</button>
    ';
    $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
    $TPL["field_invoiceName"] = page::htmlentities($TPL["invoiceName"]);
    $TPL["field_clientID"] = $client_link;
    $TPL["field_projectID"] = $project_link;
    $TPL["field_tfID"] = $tf_link;
    $TPL["field_maxAmount"] = page::money($currency,$TPL["maxAmount"],"%s%mo %c");
    $TPL["field_invoiceDateFrom"] = $TPL["invoiceDateFrom"];
    $TPL["field_invoiceDateTo"] = $TPL["invoiceDateTo"];
  }
} else {
  $TPL["field_invoiceNum"] = $TPL["invoiceNum"];
  $TPL["field_invoiceName"] = $TPL["invoiceName"];
  $TPL["field_clientID"] = $client_link;
  $TPL["field_projectID"] = $project_link;
  $TPL["field_tfID"] = $tf_link;
  $TPL["field_maxAmount"] = page::money($currency,$TPL["maxAmount"],"%s%mo %c");
  $TPL["field_invoiceDateFrom"] = $TPL["invoiceDateFrom"];
  $TPL["field_invoiceDateTo"] = $TPL["invoiceDateTo"];
}

if (!$invoice->get_value("clientID")) {
  $options["clientStatus"] = "Current";
  $options["return"] = "dropdown_options";
  $ops = client::get_list($options);
  $TPL["field_clientID"] = $client_select;
  $TPL["field_projectID"] = $project_select;
  $TPL["field_tfID"] = $tf_select;
} 


if (is_object($invoice) && $invoice->get_id() 
&& ($invoice->get_value("invoiceStatus") == "reconcile" || $invoice->get_value("invoiceStatus") == "finished")
&& $invoice->has_attachment_permission($current_user)) {
  define("SHOW_INVOICE_ATTACHMENTS",1);
}

if ($invoiceID) {
  $TPL["main_alloc_title"] = "Invoice " . $TPL["invoiceNum"] . " - ".APPLICATION_NAME;
} else {
  $TPL["main_alloc_title"] = "New Invoice - ".APPLICATION_NAME;
}


$invoiceRepeat = new invoiceRepeat();
if (is_object($invoice) && $invoice->get_id()) {
  $q = prepare("SELECT * FROM invoiceRepeat WHERE invoiceID = %d LIMIT 1",$invoice->get_id());
  $qid1 = $db->query($q);
  if ($db->row($qid1)) {
    $invoiceRepeat->read_db_record($db);
    $invoiceRepeat->set_values("invoiceRepeat_");
    foreach (explode(" ",$TPL["invoiceRepeat_frequency"]) as $id) {
      if ($id) {
        $qid2 = $db->query("SELECT * FROM invoice WHERE invoiceRepeatID = %d AND invoiceRepeatDate = '%s'"
                           ,$invoiceRepeat->get_id(),$id);
        if ($idrow = $db->row($qid2)) {
          $links[] = "<a href='".$TPL["url_alloc_invoice"]."invoiceID=".$idrow["invoiceID"]."'>".$id."</a>";
        } else {
          $links[] = $id;
        }
      }
    }
    $TPL["message_help_no_esc"][] = "This invoice is also a template for the scheduled creation of new invoices on the following dates:
                              <br>".implode("&nbsp;&nbsp;",(array)$links)."
                              <br>Click the Repeating Invoice link for more information.";
  }


if ($invoice->get_value("invoiceRepeatID")) {
  $ir = new invoiceRepeat();
  $ir->set_id($invoice->get_value("invoiceRepeatID"));
  $ir->select();
  $i = new invoice();
  $i->set_id($ir->get_value("invoiceID"));
  $i->select();
  $TPL["message_help_no_esc"][] = "This invoice was automatically generated by the
                                   <a href='".$TPL["url_alloc_invoice"]."invoiceID=".$ir->get_value("invoiceID")."'>
                                   repeating invoice ".$i->get_value("invoiceNum")."</a>";
}


}
$TPL["invoice"] = $invoice;
$TPL["invoiceRepeat"] = $invoiceRepeat;



include_template("templates/invoiceM.tpl");


?>
