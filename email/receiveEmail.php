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


define("NO_AUTH",1);
require_once("../alloc.php");

$nl = "<br>";
#$nl = "\n";

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

print $nl."Found $num_emails emails.";

$x = 0;
// fetch and parse email
while ($x < $num_emails) {
  unset($bad_key,$done);
  $x++;
  $mail->set_msg($x);
  $headers = $mail->get_msg_header();
  $keys = $mail->get_hashes();
  #echo "<pre>".print_r($keys,1)."</pre>";
  
  foreach ($keys as $key) {
    $token = new token;
    if ($token->set_hash($key)) {
      print $nl.$nl."Executing...";
      print $nl."  From: ".$mail->mail_headers->fromaddress;
      print $nl."  Subject: ".$mail->mail_headers->subject;
      $token->execute($mail);
      $mail->delete();
      $done = true;
    } 
  }

  if (!$done) {
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
