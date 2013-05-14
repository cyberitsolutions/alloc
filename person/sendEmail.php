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

define("NO_AUTH",true);
define("IS_GOD",true);
require_once("../alloc.php");


if (date("D") == "Sat" || date("D") == "Sun") {
  alloc_error("IT'S THE WEEKEND - GET OUTTA HERE",true);
}



// Do announcements ONCE up here.
$announcement = person::get_announcements_for_email();
$db = new db_alloc();
$db->query("SELECT personID,emailAddress,firstName,surname FROM person WHERE personActive = '1'");
// AND username='alla'"); // or username=\"ashridah\"");


while ($db->next_record()) {

  $person = new person();
  $person->read_db_record($db);
  $person->set_id($db->f("personID"));
  $person->load_prefs();
  if (!$person->prefs["dailyTaskEmail"]) {
    continue;
  }

  $msg = "";
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

    $subject = commentTemplate::populate_string(config::get_config_item("emailSubject_dailyDigest", ""));
    $to = $person->get_value("emailAddress");
    if ($person->get_value("firstName") && $person->get_value("surname") && $to) {
      $to = $person->get_value("firstName")." ".$person->get_value("surname")." <".$to.">";  
    }

    if ($tasks && $to) {
      $email = new email_send($to, $subject, $msg, "daily_digest");
      if ($email->send()) {
        echo "\n<br>Sent email to: ".$to;
      }
    } 

  }
}


?>
