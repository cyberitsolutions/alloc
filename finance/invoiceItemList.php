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

require_once("alloc.inc");

$mode or $mode = "allocate";

function show_invoices($template) {
  global $TPL, $mode, $HTTP_GET_VARS, $id, $invoiceID;
  $db = new db_alloc;
  $sort = $HTTP_GET_VARS["sort"];       // there is a stray cookie running about with this name. I'll get you gadget.

  if (!$sort) {
    $sort = invoiceItemID;
  }

  if ($mode == "approve") {
    $query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName 
      FROM invoiceItem, invoice 
      WHERE status='allocated' AND invoiceItem.invoiceID = invoice.invoiceID
      ORDER BY $sort";
  } else {
    // default allocate 
    $query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName 
      FROM invoiceItem, invoice 
      WHERE status='pending' AND invoiceItem.invoiceID = invoice.invoiceID
      ORDER BY $sort";
  }

  $db->query($query);
  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

    $invoice = new invoice;
    $invoice->read_db_record($db);
    $invoice->set_tpl_values();

    $invoice_item = new invoiceItem;
    $invoice_item->read_db_record($db);
    $invoice_item->set_tpl_values();

    $TPL["iiAmount"] = number_format($TPL["iiAmount"], 2);

    include_template($template);
  }
}



$TPL["mode"] = $mode;
$TPL["mode_desc"] = ucwords($mode)."d"; // teehee



include_template("templates/invoiceItemListM.tpl");

page_close();



?>
