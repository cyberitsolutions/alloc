#!/usr/bin/env php
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

/*
 * This script may need to be run via:
 * sudo -u apache php pipe.php
 *
 * This is because we need to read alloc_config.php
 * which may only be readable by the webserver.
*/

PHP_SAPI == 'cli' or die_bad("This script must be run from the command line.");
define("NO_AUTH",1);
require_once(dirname(__FILE__)."/../alloc.php");
unset($current_user);

function die_bad($msg) {
  echo $msg;
  exit(1);
}

function die_good($msg) {
  echo $msg;
  exit(0);
}

function die_rollback($msg) {
  $db = new db_alloc();
  $db->query("ROLLBACK");
  echo $msg;
  global $email_receive;
  if ($email_receive) {
    $email_receive->archive("INBOX");
  }
}

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

$info["host"] or die_bad("Email mailbox host not defined, assuming email function is inactive.");


// Read an email from stdin
while (FALSE !== ($line = fgets(STDIN))) {
  $email[] = $line;
}

// Nuke any mbox header that sendmail/postfix may have prepended.
if ($email[0] == "") {
  array_shift($email);
}
if (preg_match("/^From /i",$email[0])) {
  array_shift($email);
}

$email = implode("", (array)$email);
$email or die_bad("Empty email message, halting.");

// Don't alloc_die() on errors. Static call to permeate all instances of db_entity objects.
db_entity::skip_errors();

// wrap db queries in a transaction
$db = new db_alloc();
$db->start_transaction();

$email_receive = new email_receive($info);
$email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
$email_receive->set_msg_text($email);
$email_receive->get_msg_header();

// Skip over emails that are from alloc. These emails are kept only for
// posterity and should not be parsed and downloaded and re-emailed etc.
if (same_email_address($email_receive->mail_headers["from"], ALLOC_DEFAULT_FROM_ADDRESS)) {
  $email_receive->mark_seen();
  $email_receive->archive();
  die_good("Email was sent from alloc. Archived email.");
}

list($from_address,$from_name) = parse_email_address($email_receive->mail_headers["from"]);
if (!$email_receive->mail_headers["from"] || !$from_address) {
  die_bad("No from address. Skipping email: ".$rtn["message"]);
}

$person = new person;
$personID = $person->find_by_email($from_address);
$personID or $personID = $person->find_by_name($from_name);

// If we've determined a personID from the $from_address and $current_user->get_id() isn't set
if ($personID) {
  $current_user = &singleton("person");
  $current_user = new person;
  $current_user->load_current_user($personID);
} 


// Save the email's attachments into a directory, (which also loads up $email_receive->mail_text)
$dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR."tmp-".md5($email_receive->mail_headers["message-id"]);
$email_receive->save_email_from_text($email,$dir.DIRECTORY_SEPARATOR);

// Run any commands that have been embedded in the email
$command = new command();
$fields = $command->get_fields();
$commands = $email_receive->get_commands($fields);
$rtn = $command->run_commands($commands,$email_receive);
if ($rtn["status"] == "err") {
  die_rollback("Email command failed: ".$rtn["message"]);

// Commit the db, and move the email into its storage location eg: INBOX.task1234
} else {
  $db->commit();
  $email_receive->archive();
}

$email_receive->expunge();
$email_receive->close();

?>
