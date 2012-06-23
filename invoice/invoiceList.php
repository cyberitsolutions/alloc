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

$current_user->check_employee();

$defaults = array("showHeader"=>true
                 ,"showInvoiceNumber"=>true
                 ,"showInvoiceClient"=>true
                 ,"showInvoiceName"=>true
                 ,"showInvoiceAmount"=>true
                 ,"showInvoiceAmountPaid"=>true
                 ,"showInvoiceDate"=>true
                 ,"showInvoiceStatus"=>true
                 ,"url_form_action"=>$TPL["url_alloc_invoiceList"]
                 ,"form_name"=>"invoiceList_filter"
                 );


function show_filter() {
  global $TPL;
  global $defaults;
  $_FORM = invoice::load_form_data($defaults);
  $arr = invoice::load_invoice_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);

  $payment_statii = invoice::get_invoice_statii_payment();
  foreach($payment_statii as $payment_status => $label) {
    $summary.= "\n".$nbsp.invoice::get_invoice_statii_payment_image($payment_status)." ".$label;
    $nbsp = "&nbsp;&nbsp;";
  }
  $TPL["status_legend"] = $summary;

  include_template("templates/invoiceListFilterS.tpl");
}


$_FORM = invoice::load_form_data($defaults);

// Restrict non-admin users records
if (!$current_user->have_role("admin")) {
  $_FORM["personID"] = $current_user->get_id();
}
$TPL["invoiceListRows"] = invoice::get_list($_FORM);
$TPL["_FORM"] = $_FORM;

if (!$current_user->prefs["invoiceList_filter"]) {
  $TPL["message_help"][] = "

allocPSA allows you to create Invoices for your Clients and record the
payment status of those Invoices. This page allows you to view a list of
Invoices.

<br><br>

Simply adjust the filter settings and click the <b>Filter</b> button to
display a list of previously created Invoices. 
If you would prefer to create a new Invoice, click the <b>New Invoice</b> link
in the top-right hand corner of the box below.";
}



$TPL["main_alloc_title"] = "Invoice List - ".APPLICATION_NAME;
include_template("templates/invoiceListM.tpl");

?>
