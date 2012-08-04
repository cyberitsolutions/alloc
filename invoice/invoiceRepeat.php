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

$current_user = &singleton("current_user");
$db = new db_alloc();
$invoiceRepeat = new invoiceRepeat($_REQUEST["invoiceRepeatID"]);

if ($_POST["save"]) {
  $invoiceRepeat->set_value("invoiceID",$_POST["invoiceID"]);
  $invoiceRepeat->set_value("message",$_POST["message"]);
  $invoiceRepeat->set_value("active",1);
  $invoiceRepeat->set_value("personID",$current_user->get_id());
  $invoiceRepeat->save($_POST["frequency"]);
  interestedParty::make_interested_parties("invoiceRepeat",$invoiceRepeat->get_id(),$_POST["commentEmailRecipients"]);
}

alloc_redirect($TPL["url_alloc_invoice"]."invoiceID=".$_POST["invoiceID"]);

?>
