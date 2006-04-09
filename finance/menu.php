<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

$options = array(array("url"=>"tfList",
                       "text"=>"TF List",
                       "entity"=>"tf",
                       "action"=>PERM_READ),
                 array("url"=>"separator"),
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
                       "action"=>PERM_READ),
                 array("url"=>"invoicesUpload",
                       "params"=>"",
                       "text"=>"Upload Invoices File",
                       "entity"=>"invoiceItem",
                       "action"=>PERM_CREATE),
                 array("url"=>"separator"),
                 array("url"=>"expOneOff",
                       "text"=>"New Expense Form",
                       "entity"=>"expenseForm",
                       "action"=>PERM_CREATE),
                 array("url"=>"checkRepeat",
                       "params"=>"",
                       "text"=>"Push Repeating Expenses Through",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT),
                 array("url"=>"expenseUpload",
                       "params"=>"",
                       "text"=>"Upload Expenses File",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_UPLOAD_EXPENSES_FILE),
                 array("url"=>"expenseFormList",
                       "params"=>"&view=true",
                       "text"=>"View Pending Expense Forms",
                       "entity"=>"expenseForm",
                       "action"=>PERM_READ),
                 array("url"=>"separator"),
                 array("url"=>"transaction",
                       "params"=>"",
                       "text"=>"New Transaction",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION),
                 array("url"=>"searchTransaction",
                       "params"=>"",
                       "text"=>"Search Transactions",
                       "entity"=>"transaction",
                       "action"=>PERM_READ),
                 array("url"=>"transactionPendingList",
                       "params"=>"",
                       "text"=>"View Pending Transactions",
                       "entity"=>"transaction",
                       "action"=>PERM_READ),
                 array("url"=>"separator"),
                 array("url"=>"reconciliationReport",
                       "params"=>"",
                       "text"=>"Reconciliation Report",
                       "entity"=>"transaction",
                       "action"=>PERM_FINANCE_RECONCILIATION_REPORT),
                 array("url"=>"wagesUpload", "params"=>"", "text"=>"Upload Wages File", "entity"=>"transaction", "action"=>PERM_FINANCE_WRITE_WAGE_TRANSACTION), array("url"=>"separator"), array("url"=>"tf", "params"=>"", "text"=>"New TF", "entity"=>"tf", "action"=>PERM_CREATE)
  );

function show_options($template) {
  global $options, $TPL;

  reset($options);
  while (list(, $option) = each($options)) {
    if ($option["url"] == "separator") {
      print "<br><br>\n";
    } else if (have_entity_perm($option["entity"], $option["action"], $current_user, true)) {
      $TPL["url"] = $TPL["url_alloc_".$option["url"]];
      $TPL["params"] = $option["params"];
      $TPL["text"] = $option["text"];
      include_template($template);
    }
  }
}

include_template("templates/menuM.tpl");



?>
