<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

class finance_module extends module {
  var $db_entities = array("tf", "transaction", "expenseForm", "tfPerson", "transactionRepeat");

  function register_home_items() {
    include(ALLOC_MOD_DIR."finance/lib/tfList_home_item.inc.php");
    register_home_item(new tfList_home_item);
  } 
}

include(ALLOC_MOD_DIR."finance/lib/tf.inc.php");
include(ALLOC_MOD_DIR."finance/lib/transaction.inc.php");
include(ALLOC_MOD_DIR."finance/lib/expenseForm.inc.php");
include(ALLOC_MOD_DIR."finance/lib/tfPerson.inc.php");
include(ALLOC_MOD_DIR."finance/lib/transactionRepeat.inc.php");




?>
