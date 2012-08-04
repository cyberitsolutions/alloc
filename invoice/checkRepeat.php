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

define("NO_AUTH",true);
require_once("../alloc.php");
singleton("errors_fatal",true);
singleton("errors_format","text");
singleton("errors_logged",true);
singleton("errors_thrown",true);
singleton("errors_haltdb",false);


#$today = $_REQUEST["today"] or $today = date("Y-m-d");

$q = prepare("SELECT invoiceRepeatDate.invoiceRepeatID
                   , invoiceRepeatDate.invoiceDate
                   , invoiceRepeat.invoiceID AS templateInvoiceID
                   , invoiceRepeat.personID AS currentUser
                   , invoiceRepeat.message
                   , invoice.invoiceID
                FROM invoiceRepeatDate
           LEFT JOIN invoiceRepeat ON invoiceRepeatDate.invoiceRepeatID = invoiceRepeat.invoiceRepeatID
           LEFT JOIN invoice ON invoice.invoiceRepeatID = invoiceRepeatDate.invoiceRepeatID
                 AND invoice.invoiceRepeatDate = invoiceRepeatDate.invoiceDate
               WHERE invoice.invoiceID IS NULL
                 AND invoiceRepeatDate.invoiceDate <= '%s'",$today);

$orig_current_user = &singleton("current_user");
$db = new db_alloc();
$id = $db->query($q);
while ($row = $db->row($id)) {

  if ($row["currentUser"]) {
    $current_user = new person();
    $current_user->load_current_user($row["currentUser"]);
    singleton("current_user",$current_user);
  } 

  #echo "<br>Checking row: ".print_r($row,1);

  $invoice = new invoice();
  $invoice->set_id($row["templateInvoiceID"]);
  $invoice->select();

  $i = new invoice();
  $i->set_value("invoiceRepeatID",$row["invoiceRepeatID"]);
  $i->set_value("invoiceRepeatDate",$row["invoiceDate"]);
  $i->set_value("invoiceNum",invoice::get_next_invoiceNum());
  $i->set_value("clientID",$invoice->get_value("clientID"));
  $i->set_value("projectID",$invoice->get_value("projectID"));
  $i->set_value("invoiceName",$invoice->get_value("invoiceName"));
  $i->set_value("invoiceStatus","edit");
  $i->set_value("invoiceDateTo",$row["invoiceDate"]);
  $i->set_value("currencyTypeID",$invoice->get_value("currencyTypeID"));
  $i->set_value("maxAmount",$invoice->get_value("maxAmount"));
  $i->save();

  #echo "<br>Created invoice: ".$i->get_id();

  $q = prepare("SELECT * FROM invoiceItem WHERE invoiceID = %d",$invoice->get_id());
  $id2 = $db->query($q);
  while ($item = $db->row($id2)) {
    $ii = new invoiceItem();
    $ii->currency = $i->get_value("currencyTypeID");
    $ii->set_value("invoiceID",$i->get_id());
    $ii->set_value("iiMemo",$item["iiMemo"]);
    $ii->set_value("iiUnitPrice",page::money($ii->currency,$item["iiUnitPrice"],"%mo"));
    $ii->set_value("iiAmount",page::money($ii->currency,$item["iiAmount"],"%mo"));
    $ii->set_value("iiQuantity",$item["iiQuantity"]);
    $ii->save();
    #echo "<br>Created invoice item: ".$ii->get_id();
  }


  if ($row["message"]) {
    $ips = interestedParty::get_interested_parties("invoiceRepeat",$row["invoiceRepeatID"]);

    $recipients = array();
    foreach ($ips as $email => $info) {
      $recipients[$email] = $info;
      $recipients[$email]["addIP"] = true;
    }

    $commentID = comment::add_comment("invoice", $i->get_id(), $row["message"], "invoice", $i->get_id());
    if ($recipients) {
      $emailRecipients = comment::add_interested_parties($commentID, null, $recipients);
      comment::attach_invoice($commentID,$i->get_id(),$verbose=true);

      // Re-email the comment out, including any attachments
      if (!comment::send_comment($commentID,$emailRecipients)) {
        alloc_error("Failed to email invoice: ".$i->get_id());
      }
    }
  }

  // Put current_user back to normal
  $current_user = &$orig_current_user;
  singleton("current_user",$current_user);
}

?>
