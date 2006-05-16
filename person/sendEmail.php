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
require_once("alloc.inc");


if (date("D") == "Sat" || date("D") == "Sun") {
  die("IT'S THE WEEKEND - GET OUTTA HERE");
}



// Do announcements ONCE up here.
$announcement = person::get_announcements_for_email();
$db = new db_alloc;
$db->query("SELECT * FROM person");
// WHERE username='alla'"); // or username=\"ashridah\"");


while ($db->next_record()) {
  $person = new person;
  $person->read_db_record($db);
  $msg = "";
  $headers = "";

  if ($person->get_value("dailyTaskEmail") != "yes") {
    // skip person
    continue;
  }

  if ($person->get_value("emailFormat") == "html") {
    $headers = "MIME-Version: 1.0\r\n";
    $headers.= "Content-type: text/html; charset=iso-8859-1\r\n";
    $msg.= "<html><head><title>allocPSA Daily Digest</title></head><body>";

    if ($announcement["heading"]) {
      $msg.= "<br><h4>".$announcement["heading"]."</h4>";
      $msg.= $announcement["body"]."<br>";
    }
  } else {
    if ($announcement["heading"]) {
      $msg.= $announcement["heading"];
      $msg.= "\n".$announcement["body"]."\n";
      $msg.= "\n- - - - - - - - - -\n";
    }
  }

  if ($person->get_value("emailAddress")) {
    $tasks = $person->get_tasks_for_email();
    $msg.= $tasks;

    $headers.= "From: allocPSA <".ALLOC_DEFAULT_FROM_ADDRESS.">";
    $subject = "Daily Digest";
    $to = $person->get_value("emailAddress");
    if ($person->get_value("firstName") && $person->get_value("surname") && $to) {
      $to = $person->get_value("firstName")." ".$person->get_value("surname")." <".$to.">";  
    }

    // Finish off HTML 
    if ($person->get_value("emailFormat") == "html") {
      $msg.= "</body></html>";
    }

    if ($tasks != "" && $to) {
      $email = new alloc_email;

      if ($email->send($to, $subject, stripslashes($msg), $headers)) {
        echo "Email sent to: ".$person->get_value("username")."\r\n";
      } else {
        echo "Not sent email: ".$subject." to: ".$person->get_value("username");
      }
    } else {
      echo $msg;
    }
  } else {
    echo "No Email Address For: ".$person->get_value("username")."\n";
  }
}



page_close();



?>
