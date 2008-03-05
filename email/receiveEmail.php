<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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


define("NO_AUTH",1);
require_once("../alloc.php");

#$nl = "<br>";
$nl = "\n";
$debug = true;

$lockfile = ATTACHMENTS_DIR."mail.lock";

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

if (!$info["host"]) {
  die("Email mailbox host not defined, assuming email receive function is inactive.");
}

$mail = new alloc_email_receive($info,$lockfile);
$mail->open_mailbox(config::get_config_item("allocEmailFolder"));
$mail->check_mail();
$num_new_emails = $mail->get_num_new_emails();

if ($num_new_emails >0) {

  $msg_nums = $mail->get_new_email_msg_nums(); 
  #$msg_nums = $mail->get_all_email_msg_nums(); // for debugging (and if degbugging don't forget to add a ||1 to the if statement above :)

  $debug and print $nl.date("Y-m-d H:i:s")." Found ".count($msg_nums)." new/unseen emails.";

  // fetch and parse email
  foreach ($msg_nums as $num) {
    unset($bad_key,$done);
    $mail->set_msg($num);
    $mail->set_uid($mail->get_uid($num)); // This is so that comments can start tracking the emails uid. 
    $headers = $mail->get_msg_header();
    $keys = $mail->get_hashes();
    $debug and print $nl.$nl."Keys: ".$nl.print_r($keys,1);
    
    foreach ($keys as $key) {
      $token = new token;
      $debug and print $nl."Attempting key: ".$key;
      if ($token->set_hash($key)) {
        $debug and print $nl."Executing with key ".$key;
        $debug and print $nl."  From: ".$mail->mail_headers->fromaddress;
        $debug and print $nl."  Subject: ".$mail->mail_headers->subject;
        $debug and print $nl."  To: ".$mail->mail_headers->toaddress;
        $token->execute($mail);
        $done = true;
      } else {
        $debug and print $nl."Unable to set key to: ".$key;
      }
    }

    if (!$done) {
      $debug and print $nl."Mail failed and forwarded to admin!";
      // forward to admin
      if (config::get_config_item("allocEmailAdmin")) {
        $mail->forward(config::get_config_item("allocEmailAdmin"), "Email sent to ".config::get_config_item("AllocFromEmailAddress"));
      }
    }
  }
}


$mail->close();








?>
