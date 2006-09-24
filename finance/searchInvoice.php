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

$current_user->check_employee();

global $search;

function startSearch($template) {

  global $TPL, $current_user;

  $db = new db_alloc;

  if ($_POST["search"]) {


    $_POST["invoiceItemID"]      and $str.= sprintf(" and invoiceItem.invoiceItemID = %d",$_POST["invoiceItemID"]);
    $_POST["invoiceNum"]         and $str.= sprintf(" and invoice.invoiceNum like %d",$_POST["invoiceNum"]);
    $_POST["dateOne"]            and $str.= sprintf(" and invoice.invoiceDate>=\"%s\"",$_POST["dateOne"]);
    $_POST["dateTwo"]            and $str.= sprintf(" and invoice.invoiceDate<=\"%s\"",$_POST["dateTwo"]);
    $_POST["invoiceName"]        and $str.= sprintf(" and invoice.invoiceName like '%%%s%%'",$_POST["invoiceName"]);
    $_POST["invoiceItem_status"] and $str.= sprintf(" and invoiceItem.status = '%s'",$_POST["invoiceItem_status"]);

    if ($current_user->have_role("admin")) {

      $query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName
                  FROM invoiceItem, invoice
                 WHERE invoiceItem.invoiceID = invoice.invoiceID $str";

    // Rsstrict the results to invoices that have invoiceItems on them for TF's that this user can view
    } else {
      $tfIDs = $current_user->get_tfIDs();
      if (is_array($tfIDs)) {
        $tfIDs = " AND transaction.tfID in (".implode(",",$tfIDs).")";
      }
      $query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName
                  FROM invoiceItem, invoice
             LEFT JOIN transaction on transaction.invoiceItemID = invoiceItem.invoiceItemID 
                 WHERE invoiceItem.invoiceID = invoice.invoiceID $str $tfIDs";

    }

    $db->query($query);

    while ($db->next_record()) {
      $i++;
      $TPL["row_class"] = "odd";
      $i % 2 == 0 and $TPL["row_class"] = "even";

      $invoice = new invoice;
      $invoice->read_db_record($db);
      $invoice->set_tpl_values();

      $invoiceItem = new invoiceItem;
      $invoiceItem->read_db_record($db);
      $invoiceItem->set_tpl_values(DST_HTML_ATTRIBUTE, "invoiceItem_");

      $TPL["mode"] = "";
      $status = $invoiceItem->get_value("status");
      $status == "pending" and $TPL["mode"] = "allocate";
      $status == "allocated" and $TPL["mode"] = "approve";

      include_template($template);
    }
  }
}




$db = new db_alloc;

$TPL["statusOptions"] = get_options_from_array(array("pending"=>"Pending", "allocated"=>"Allocated", "paid"=>"Paid",), $_POST["invoiceItem_status"], false);
$TPL["status"] = $_POST["status"];
$TPL["dateOne"] = $_POST["dateOne"];
$TPL["dateTwo"] = $_POST["dateTwo"];
$TPL["invoiceID"] = $_POST["invoiceID"];
$TPL["invoiceName"] = $_POST["invoiceName"];
$TPL["invoiceNum"] = $_POST["invoiceNum"];
$TPL["invoiceItemID"] = $_POST["invoiceItemID"];


include_template("templates/searchInvoiceM.tpl");

page_close();




?>
