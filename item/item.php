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

$current_user->check_employee();

global $db, $today;

$itemID = $_POST["itemID"] or $itemID = $_GET["itemID"];
$loanID = $_POST["loanID"] or $loanID = $_GET["loanID"];

$item = new item;
$loan = new loan;
$db = new db_alloc;
$db->query("select * from item where itemID=$itemID");
$db->next_record();
$item->read_db_record($db);
$item->set_tpl_values();

// new crap
if ($current_user->have_role("admin") || $current_user->have_role("manage")) {
  $users = array();
  $_db = new db_alloc;
  $_db->query("SELECT * FROM person ORDER BY username");
  while ($_db->next_record()) {
    $person = new person;
    $person->read_db_record($_db);
    $users[$person->get_id()] = $person->get_value('username');
  }
  $TPL["userSelect"] = "<select name=\"userID\">".get_options_from_array($users, $current_user->get_id(), true)
    ."</select><br>\n";
} else {
  $TPL["userSelect"] = "";
}

$temp = mktime(0, 0, 0, date("m") + $_POST["timePeriod"], date("d"), date("Y"));
$whenToReturn = date("Y", $temp)."-".date("m", $temp)."-".date("d", $temp);



$today = date("Y")."-".date("m")."-".date("d");

if ($loanID) {
  $loan->set_id($loanID);
  $loan->select();
}


if ($_POST["borrowItem"]) {
  $db->query("select * from loan where itemID=$itemID and dateReturned='0000-00-00'");

  if ($db->next_record()) {     // if the item is already borrowed
    header("location:".$TPL["url_alloc_item"]."itemID=$itemID&badBorrow=true&error=already_borrowed");
    exit();
  } else {                      // else lets make a new loan!
    $loan = new loan;
    $loan->read_globals();
    $loan->set_value("dateToBeReturned", $whenToReturn);

    // if admin/manager then check to see if an alternate user was selected
    if ($_POST["userID"] && ($current_user->have_role("admin") || $current_user->have_role("manage"))) {
      if ($_POST["userID"] != $current_user->get_id()) {
        $person = new person;
        $person->set_id($_POST["userID"]);
        $person->select();
      }
      $loan->set_value("personID", $_POST["userID"]);
    } else {
      $loan->set_value("personID", $current_user->get_id());
    }

    $loan->set_value("dateBorrowed", $today);
    $loan->set_value("dateReturned", "0000-00-00");
    $loan->save();

    header("location:".$TPL["url_alloc_loanAndReturn"]);
  }
}



if ($_POST["returnItem"]) {

  $dbTemp = new db_alloc;
  $dbTemp->query("select * from loan where itemID=$itemID and dateReturned='0000-00-00'");

  $db->query("select * from loan where loan.itemID=$itemID and dateBorrowed>dateReturned");
  $db->next_record();
  $loan->read_db_record($db);
  $loan->set_id($db->f("loanID"));
  $loan->select();
  $loan->set_value("dateReturned", $today);

  // check to see if admin/manager returning someone elses item, and sent email
  if ($loan->get_value("personID") != $current_user->get_id()) {
    if ($current_user->have_role("admin") || $current_user->have_role("manage")) {
      $person = new person;
      $person->set_id($loan->get_value("personID"));
      $person->select();
      $loan->save();
    }
  } else {
    $loan->save();
  }

  header("location:".$TPL["url_alloc_loanAndReturn"]);
}



if ($_GET["return"]) {
  include_template("templates/itemReturnM.tpl");
} else {
  include_template("templates/itemBorrowM.tpl");
}


page_close();






?>
