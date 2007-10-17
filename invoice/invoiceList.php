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
define("PAGE_IS_PRINTABLE",1);

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
  global $TPL,$defaults;
  $_FORM = invoice::load_form_data($defaults);
  $arr = invoice::load_invoice_filter($_FORM);
  is_array($arr) and $TPL = array_merge($TPL,$arr);
  include_template("templates/invoiceListFilterS.tpl");
}

function show_invoice_list() {
  global $defaults, $current_user;

  $_FORM = invoice::load_form_data($defaults);

  #$_FORM["debug"] = true;

  // Restrict non-admin users records
  if (!$current_user->have_role("admin")) {
    $_FORM["personID"] = $current_user->get_id();
  }

  echo invoice::get_invoice_list($_FORM);
}

if ($current_user->have_role("admin")) {
  $TPL["invoice_links"] = "<a href=\"".$TPL["url_alloc_invoicesUpload"]."\">Upload Invoices</a>";
  $TPL["invoice_links"].= "&nbsp;&nbsp;<a href=\"".$TPL["url_alloc_invoice"]."\">New Invoice</a>";
}

include_template("templates/invoiceListM.tpl");
page_close();




?>
