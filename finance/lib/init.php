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

require_once(dirname(__FILE__)."/tf.inc.php");
require_once(dirname(__FILE__)."/transaction.inc.php");
require_once(dirname(__FILE__)."/expenseForm.inc.php");
require_once(dirname(__FILE__)."/tfPerson.inc.php");
require_once(dirname(__FILE__)."/transactionRepeat.inc.php");
require_once(dirname(__FILE__)."/tfList_home_item.inc.php");
require_once(dirname(__FILE__)."/exchangeRate.inc.php");

class finance_module extends module {
  var $db_entities = array("tf", "transaction", "expenseForm", "tfPerson", "transactionRepeat");

  function register_home_items() {
    register_home_item(new tfList_home_item);
  } 
}





?>
