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

class finance_module extends module {
  var $db_entities = array("tf", "transaction", "expenseForm", "invoice", "invoiceItem", "tfPerson", "transactionRepeat");

  function register_toolbar_items() {
    global $current_user;

    if (isset($current_user) && $current_user->is_employee()) {
      register_toolbar_item("financeMenu", "Finance");
    }
  }

}

include(ALLOC_MOD_DIR."/finance/lib/tf.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/transaction.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/expenseForm.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/invoice.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/invoiceItem.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/tfPerson.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/transactionRepeat.inc.php");




?>
