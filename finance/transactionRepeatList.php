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

global $tfID, $TPL, $transactionRepeat, $db;

$db = new db_alloc;
$TPL["tfID"] = $tfID;
$db->query("select * from tf where tfID=$tfID");
$db->next_record();
$TPL["user"] = $db->f("tfName");

include_template("templates/transactionRepeatListM.tpl");



function show_expenseFormList($template_name) {

  global $db, $TPL, $tfID, $john, $transactionRepeat;

  $db = new db_alloc;
  $transactionRepeat = new transactionRepeat;

  if ($tfID) {
    $db->query("select * from transactionRepeat where tfID=$tfID");
  }

  if ($john) {
    $db->query("select * from transactionRepeat where tfID=$john");
  }

  while ($db->next_record()) {
    $i++;
    $TPL["row_class"] = "odd";
    $i % 2 == 0 and $TPL["row_class"] = "even";

    $transactionRepeat->read_db_record($db);
    $transactionRepeat->set_tpl_values();
    include_template($template_name);
  }
  $TPL["tfID"] = $tfID;
}




page_close();




?>
