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

if (!$info["username"] || !$info["password"] || !$info["host"]) {
  die("Email inbox username not defined, assuming email receive function is inactive.");
}

$mail = new alloc_email_receive($info,$lockfile);
$mail->open_mailbox(config::get_config_item("allocEmailFolder"));
$mail->check_mail();
$num_emails = $mail->mailbox_info->Nmsgs;
$debug && $num_emails and print $nl.date("Y-m-d H:i:s")." Found $num_emails emails.";

$x = 0;
// fetch and parse email
while ($x < $num_emails) {
  unset($bad_key,$done);
  $x++;
  $mail->set_msg($x);
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
      $token->execute($mail);
      $mail->delete();
      $done = true;
    } else {
      $debug and print $nl."Unable to set key to: ".$key;
    }
  }

  if (!$done) {
    $debug and print $nl."Mail failed and forwarded to admin!";
    // forward to admin
    if (config::get_config_item("allocEmailAdmin")) {
      $mail->forward(config::get_config_item("allocEmailAdmin"), "[allocPSA] Unable to process email sent to ".config::get_config_item("AllocFromEmailAddress"));
    }
    $mail->delete();
  }


}

$mail->expunge();
$mail->close();








?>
