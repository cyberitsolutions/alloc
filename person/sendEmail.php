<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

define("NO_AUTH",true);
define("SENDMAIL", true);         // shoot out emails 

require_once("alloc.inc");


if (date(D) == "Sat" || date(D) == "Sun") {
  die("IT'S THE WEEKEND - GET OUTTA HERE");
}
// stats
$stats = new stats;


// Do announcements ONCE up here.
$announcement = person::get_announcements_for_email();
$db = new db_alloc;
$db->query("SELECT * FROM person");
// where username=\"clancy\""); // or username=\"ashridah\"");


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
    $msg.= "<html><head><title>AllocPSA Daily Digest</title></head><body>";

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

    $msg.= $stats->get_stats_for_email($person->get_value("emailFormat"));

    $headers.= "From: ".ALLOC_DEFAULT_FROM_ADDRESS;
    $subject = "AllocPSA Daily Digest";
    $to = $person->get_value("emailAddress");


    // FINISH OFF HTML - MUSTN'T SEND BROKEN HTML 
    if ($person->get_value("emailFormat") == "html") {
      $msg.= "</body></html>";
    }


    if ($tasks != "" && SENDMAIL == true) {
      // They have Tasks and we are not debugging!
      mail($to, $subject, stripslashes($msg), $headers);
      echo "Email sent to ".$person->get_value("username")."\r\n";
    } else {
      echo $msg;
      // echo "No Tasks or SENDMAIL was false\n";
      // echo "Email NOT sent to " . $person->get_value("username") . "\n";
      // echo "START MSG->" . $msg . "<-END MSG\n"; 
    }
  } else {
    echo "NO EMAIL ADDRESS FOR: ".$person->get_value("username")."\n";
  }
}



page_close();



?>
