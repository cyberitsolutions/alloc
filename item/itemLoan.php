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

$TPL["main_alloc_title"] = "Item Loans - ".APPLICATION_NAME;
include_template("templates/itemLoanM.tpl");




function show_overdue($template_name) {

  global $db;
  global $TPL;
  $current_user = &singleton("current_user");

  $db = new db_alloc();
  $temp = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
  $today = date("Y", $temp)."-".date("m", $temp)."-".date("d", $temp);

  $q = prepare("SELECT itemName,itemType,item.itemID,dateBorrowed,dateToBeReturned,loan.personID 
                  FROM loan,item 
                 WHERE dateToBeReturned < '%s' 
					         AND dateReturned = '0000-00-00' 
					         AND item.itemID = loan.itemID
               ",$today);

  if (!have_entity_perm("loan", PERM_READ, $current_user, false)) {
    $q .= prepare("AND loan.personID = %d",$current_user->get_id());
  }

  $db->query($q);

  while ($db->next_record()) {
    $i++;

    $item = new item();
    $loan = new loan();
    $item->read_db_record($db);
    $loan->read_db_record($db);
    $item->set_values();
    $loan->set_values();
    $person = new person();
    $person->set_id($loan->get_value("personID"));
    $person->select();
    $TPL["person"] = $person->get_name();
    $TPL["overdue"] = "<a href=\"".$TPL["url_alloc_item"]."itemID=".$item->get_id()."&return=true\">Overdue!</a>";

    include_template($template_name);
  }
}



?>
