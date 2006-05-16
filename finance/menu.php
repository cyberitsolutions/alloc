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

// Is this even used?
 #array("url"=>"expenseUpload",
       #"params"=>"",
       #"text"=>"Upload Expenses File",
       #"entity"=>"transaction",
       #"action"=>PERM_FINANCE_UPLOAD_EXPENSES_FILE),

$options = array(array("url"=>"tf", 
                       "params"=>"", 
                       "text"=>"New TF", 
                       "entity"=>"tf", 
                       "action"=>PERM_CREATE),

                 array("url"=>"tfList",
                       "text"=>"TF List",
                       "entity"=>"tf",
                       "action"=>PERM_READ,
                       "br"=>true),

                 array("url"=>"transaction",
                       "params"=>"",
                       "text"=>"New Transaction",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION),

                 array("url"=>"searchTransaction",
                       "params"=>"",
                       "text"=>"Search Transactions",
                       "entity"=>"transaction",
                       "action"=>PERM_READ,
                       "br"=>true),

                 array("url"=>"invoicesUpload",
                       "params"=>"",
                       "text"=>"Upload Invoices File",
                       "entity"=>"invoiceItem",
                       "action"=>PERM_CREATE),
                       
                 array("url"=>"invoiceItemList",
                       "params"=>"&mode=allocate",
                       "text"=>"Allocate Invoices",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_WRITE_INVOICE_TRANSACTION),

                 array("url"=>"invoiceItemList",
                       "params"=>"&mode=approve",
                       "text"=>"Approve Invoices",
                       "entity"=>"invoiceItem",
                       "action"=>PERM_FINANCE_UPDATE_APPROVED),

                 array("url"=>"searchInvoice",
                       "params"=>"",
                       "text"=>"Search Invoices",
                       "entity"=>"invoice",
                       "action"=>PERM_READ,
                       "br"=>true),

                 array("url"=>"expOneOff",
                       "text"=>"New Expense Form",
                       "entity"=>"expenseForm",
                       "action"=>PERM_CREATE),

                 array("url"=>"expenseFormList",
                       "params"=>"&view=true",
                       "text"=>"View Pending Expense Forms",
                       "entity"=>"expenseForm",
                       "action"=>PERM_READ,
                       "br"=>true),
                       
                 array("url"=>"reconciliationReport",
                       "params"=>"",
                       "text"=>"Reconciliation Report",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_RECONCILIATION_REPORT),
                 array("url"=>"wagesUpload", 
                       "params"=>"", 
                       "text"=>"Upload Wages File", 
                       "entity"=>"transaction", 
                       "action"=>PERM_FINANCE_WRITE_WAGE_TRANSACTION,
                       "br"=>true), 

               array("url"=>"transactionRepeat",
                       "params"=>"",
                       "text"=>"New Repeating Expense",
                       "entity"=>"transaction",
                       "action"=>PERM_READ),
 

                 array("url"=>"transactionRepeatList",
                       "params"=>"",
                       "text"=>"Repeating Expense List",
                       "entity"=>"transaction",
                       "action"=>PERM_READ),
 
                 array("url"=>"checkRepeat",
                       "params"=>"",
                       "text"=>"Push Repeating Expenses Through",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT)
                       
                 
                );

function show_options($template) {
  global $options, $TPL;
  foreach ($options as $option) {
    if (have_entity_perm($option["entity"], $option["action"], $current_user, true)) {
      $TPL["url"] = $TPL["url_alloc_".$option["url"]];
      $TPL["params"] = $option["params"];
      $TPL["text"] = $option["text"];
      $TPL["br"] = "";
      if ($option["br"]) {
        $TPL["br"] = "<br><br>\n";
      }
      include_template($template);
    }
  }
}

include_template("templates/menuM.tpl");



?>
