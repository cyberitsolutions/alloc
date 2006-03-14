<?php
include("alloc.inc");

$current_user->check_employee();

global $search;

function startSearch($template) {

  global $TPL, $db, $search, $invoiceNum, $invoice, $invoiceItem_status, $dateOne, $dateTwo, $invoiceName, $invoiceItemID;



  if ($search) {


    $invoiceItemID and $str.= "and invoiceItem.invoiceItemID = '$invoiceItemID'";
    $invoiceNum and $str.= "and invoice.invoiceNum like '$invoiceNum%'";
    $dateOne and $str.= "and invoice.invoiceDate>=\"$dateOne\"";
    $dateTwo and $str.= "and invoice.invoiceDate<=\"$dateTwo\"";
    $invoiceName and $str.= "and invoice.invoiceName like '%$invoiceName%'";
    $invoiceItem_status and $str.= "and invoiceItem.status = '$invoiceItem_status'";

    $query = "SELECT invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName
      FROM invoiceItem, invoice
      WHERE invoiceItem.invoiceID = invoice.invoiceID $str";

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

$TPL["statusOptions"] = get_options_from_array(array("pending"=>"Pending", "allocated"=>"Allocated", "paid"=>"Paid",), $invoiceItem_status, false);
$TPL["status"] = $status;
$TPL["dateOne"] = $dateOne;
$TPL["dateTwo"] = $dateTwo;
$TPL["invoiceID"] = $invoiceID;
$TPL["invoiceName"] = $invoiceName;
$TPL["invoiceNum"] = $invoiceNum;
$TPL["invoiceItemID"] = $invoiceItemID;


include_template("templates/searchInvoiceM.tpl");

page_close();




?>
