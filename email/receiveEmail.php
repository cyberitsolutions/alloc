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

define("NO_AUTH",1);
require_once("../alloc.php");
singleton("errors_fatal",false);
singleton("errors_format","text");
singleton("errors_logged",false);
singleton("errors_thrown",true);

#$nl = "<br>";
$nl = "\n";
$debug = true;

//$lockfile = ATTACHMENTS_DIR."mail.lock";

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

if (!$info["host"]) {
  alloc_error("Email mailbox host not defined, assuming email receive function is inactive.",true);
}

$email_receive = new email_receive($info,$lockfile);
$email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
$email_receive->check_mail();
$num_new_emails = $email_receive->get_num_new_emails();

if ($num_new_emails >0) {

  $msg_nums = $email_receive->get_new_email_msg_uids(); 
  #$msg_nums = $email_receive->get_all_email_msg_uids(); // for debugging (don't forget to add a ||1 to the if statement above :)
  #$msg_nums = array("20");                              // for debugging to specify a particular UID

  $debug and print $nl.date("Y-m-d H:i:s")." Found ".count($msg_nums)." new/unseen emails.";

  // fetch and parse email
  foreach ($msg_nums as $num) {
    unset($current_user);

    // wrap db queries in a transaction
    $db = new db_alloc();
    $db->start_transaction();

    $email_receive->set_msg($num);
    $email_receive->get_msg_header();

    // Skip over emails that are from alloc. These emails are kept only for
    // posterity and should not be parsed and downloaded and re-emailed etc.
    if (same_email_address($email_receive->mail_headers["from"], ALLOC_DEFAULT_FROM_ADDRESS)) {
      $email_receive->mark_seen();
      $email_receive->archive();
      continue;
    }

    list($from_address,$from_name) = parse_email_address($email_receive->mail_headers["from"]);
    if (!$email_receive->mail_headers["from"] || !$from_address) {
      $db->query("ROLLBACK");
      $email_receive->forward(config::get_config_item("allocEmailAdmin"), "No from address. Skipping email.");
      alloc_error("No from address. Skipping email: ".$email_receive->mail_text);
      continue;
    }

    $person = new person;
    $personID = $person->find_by_email($from_address);
    $personID or $personID = $person->find_by_name($from_name);

    // If we've determined a personID from the $from_address
    if ($personID) {
      $current_user = new person;
      $current_user->load_current_user($personID);
      singleton("current_user",$current_user);
    } 

    // Save the email's attachments into a directory, (which also loads up $email_receive->mail_text)
    $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR."tmp-".md5($email_receive->mail_headers["message-id"]);
    $email_receive->save_email($dir.DIRECTORY_SEPARATOR);

    // Run any commands that have been embedded in the email
    $command = new command();
    $fields = $command->get_fields();
    $commands = $email_receive->get_commands($fields);

    try {
      $command->run_commands($commands,$email_receive);
    } catch (Exception $e) {
      $db->query("ROLLBACK");
      $email_receive->forward(config::get_config_item("allocEmailAdmin")
                             ,"Email command failed"
                             ,"\n".$e->getMessage()."\n\n".$e->getTraceAsString());
      $email_receive->archive();
      continue;
    }

    // Commit the db, and move the email into its storage location eg: INBOX.task1234
    $db->commit();
    $email_receive->archive();
  }
}

$email_receive->expunge();
$email_receive->close();

?>
