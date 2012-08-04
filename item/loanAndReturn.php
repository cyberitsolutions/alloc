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
include_template("templates/loanAndReturnM.tpl");


function show_items($template_name) {
  global $TPL;
  global $db;
  global $db2;
  $current_user = &singleton("current_user");

  $today = date("Y")."-".date("m")."-".date("d");

  $dbUsername = new db_alloc();
  $db = new db_alloc();
  $db2 = new db_alloc();

  $db->query("select * from item order by itemName");


  while ($db->next_record()) {
    $i++;


    $item = new item();
    $item->read_db_record($db);

    $db2->query("select * from loan where itemID=".$item->get_id()." and dateReturned='0000-00-00'");
    $db2->next_record();
    $loan = new loan();
    $loan->read_db_record($db2);

    $item->set_values();    // you need to have this repeated here for the a href bit below.

    if ($loan->get_value("dateReturned") == "0000-00-00") {

      if ($loan->have_perm(PERM_READ_WRITE)) {

        // if item is overdue
        if ($loan->get_value("dateToBeReturned") < $today) {
          $ret = "Return Now!";
        } else {
          $ret = "Return";
        }


        $TPL["itemAction"] = "<td><a href=\"".$TPL["url_alloc_item"]
          ."itemID=".$TPL["itemID"]
          ."&return=true\">$ret</a></td>";

      } else {                  // if you don't have permission to borrow or return item.
        $TPL["itemAction"] = "<td>&nbsp;</td>";
      }


      $TPL["status"] = "Due ".$loan->get_value("dateToBeReturned");
      $dbUsername->query("select username from person where personID=".$loan->get_value("personID"));
      $dbUsername->next_record();
      $TPL["person"] = "from ".$dbUsername->f("username");

    } else {                    // if the item is available

      $TPL["status"] = "Available";
      $TPL["person"] = "";
      $TPL["itemAction"] = "<td><a href=\"".$TPL["url_alloc_item"]."itemID=".$TPL["itemID"]."&borrow=true\">Borrow</a></td>";
      $TPL["dueBack"] = "";
    }


    $loan->set_values();
    $item->set_values();
    include_template($template_name);
  }


}



?>
