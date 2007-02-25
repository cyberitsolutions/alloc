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

define("NO_AUTH",true);
require_once("../alloc.php");


if (date("D") == "Sat" || date("D") == "Sun") {
  die("IT'S THE WEEKEND - GET OUTTA HERE");
}



// Do announcements ONCE up here.
$announcement = person::get_announcements_for_email();
$db = new db_alloc;
$db->query("SELECT personID,emailAddress,firstName,surname FROM person WHERE dailyTaskEmail = 'yes' AND personActive = '1'");
// AND username='alla'"); // or username=\"ashridah\"");


while ($db->next_record()) {

  $person = new person;
  $person->read_db_record($db);
  $person->set_id($db->f("personID"));
  $msg = "";
  $headers = "";
  $tasks = "";
  $to = "";

  if ($announcement["heading"]) {
    $msg.= $announcement["heading"];
    $msg.= "\n".$announcement["body"]."\n";
    $msg.= "\n- - - - - - - - - -\n";
  }

  if ($person->get_value("emailAddress")) {
    $tasks = $person->get_tasks_for_email();
    $msg.= $tasks;

    $headers.= "From: ".ALLOC_DEFAULT_FROM_ADDRESS;
    $subject = "Daily Digest";
    $to = $person->get_value("emailAddress");
    if ($person->get_value("firstName") && $person->get_value("surname") && $to) {
      $to = $person->get_value("firstName")." ".$person->get_value("surname")." <".$to.">";  
    }

    if ($tasks && $to) {
      $email = new alloc_email;
      if ($email->send($to, $subject, $msg, "daily_digest", $headers)) {
        echo "\n<br>Sent email to: ".$to;
      }
    } 

  }
}



page_close();



?>
