<?php
include("alloc.inc");

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
