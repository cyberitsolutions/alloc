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

require_once("../alloc.php");

if (!$current_user->have_role("god")) {
  alloc_error("Only super-user has permission to use this page.",true);
}

function verify_hash($id,$hash) {
  global $info;
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"),OP_HALFOPEN | OP_READONLY);
  $email_receive->set_msg($id);
  $email_receive->get_msg_header();
  $rtn = ($hash == md5($email_receive->mail_headers["date"]
                      .$email_receive->get_printable_from_address()
                      .$email_receive->mail_headers["subject"]));
  $email_receive->close();
  return $rtn;
}


$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

if (!$info["host"]) {
  alloc_error("Email mailbox host not defined, assuming email function is inactive.",true);
}

if ($_REQUEST["id"] && $_REQUEST["hash"] && !verify_hash($_REQUEST["id"],$_REQUEST["hash"])) {
  alloc_error("The IMAP ID for that email is no longer valid. Refresh the list and try again.");
}


// If they want to archive the email
if ($_REQUEST["archive"] && $_REQUEST["id"] && $_REQUEST["hash"]) {
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
  $mailbox = "INBOX/archive".date("Y");
  $email_receive->create_mailbox($mailbox) and $TPL["message_good"][] = "Created mailbox: ".$mailbox;
  $email_receive->move_mail($_REQUEST["id"],$mailbox) and $TPL["message_good"][] = "Moved email ".$_REQUEST["id"]." to ".$mailbox;
  $email_receive->close();
  alloc_redirect($TPL["url_alloc_inbox"]);

// Else if they want to download it
} else if ($_REQUEST["download"] && $_REQUEST["id"] && $_REQUEST["hash"]) {
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"),OP_HALFOPEN | OP_READONLY);
  $email_receive->set_msg($_REQUEST["id"]);
  $new_nums = $email_receive->get_new_email_msg_uids();
  in_array($_REQUEST["id"],(array)$new_nums) and $new = true;
  list($h,$b) = $email_receive->get_raw_header_and_body();
  $new and $email_receive->set_unread(); // might have to "unread" the email, if it was new, i.e. set it back to new
  $email_receive->close();
  header('Content-Type: text/plain');
  header('Content-Disposition: attachment; filename="email'.$_REQUEST["id"].'.txt"');
  echo $h.$b;
  exit();

// Else if they want to process it by attaching it to a task etc
} else if ($_REQUEST["process"] && $_REQUEST["id"] && $_REQUEST["hash"]) {
  $orig_current_user = &$current_user;
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));

  // wrap db queries in a transaction
  $db = new db_alloc();
  $db->start_transaction();

  $email_receive->set_msg($_REQUEST["id"]);
  $email_receive->get_msg_header();

  if (same_email_address($email_receive->mail_headers["from"], ALLOC_DEFAULT_FROM_ADDRESS)) {
    $email_receive->archive();
    alloc_error("Email was from ".ALLOC_DEFAULT_FROM_ADDRESS.", email archived.");
  }

  list($from_address,$from_name) = parse_email_address($email_receive->mail_headers["from"]);
  if (!$email_receive->mail_headers["from"] || !$from_address) {
    $db->query("ROLLBACK");
    alloc_error("No from address. Skipping email: ".$email_receive->mail_text);
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
  $command->run_commands($commands,$email_receive);

  if ($TPL["message"]) {
    $db->query("ROLLBACK");
  } else {
    // Commit the db, and move the email into its storage location eg: INBOX.task1234
    $db->commit();
    $email_receive->archive();
  }

  $current_user = &$orig_current_user;
  singleton("current_user",$current_user);
  $email_receive->close();
  alloc_redirect($TPL["url_alloc_inbox"]);

// Else if they want to use this email to create a new task
} else if ($_REQUEST["newtask"] && $_REQUEST["id"] && $_REQUEST["hash"]) {

  // Need the task ID up front
  $task = new task();
  $task->set_value("taskName"," ");
  $task->set_value("priority","3");
  $task->set_value("taskTypeID","Task");
  $task->save();
  $taskID = $task->get_id();

  // Save the email's attachments to the task's attachments tab, save_email
  // also loads up mail_text and then archive this email to the task's mbox.
  if (!$TPL["message"]) {
    $email_receive = new email_receive($info);
    $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
    $email_receive->set_msg($_REQUEST["id"]);
    $email_receive->get_msg_header();
    $email_receive->save_email(ATTACHMENTS_DIR."task".DIRECTORY_SEPARATOR.$task->get_id());
  }

  // Subject line is name, email body is body
  if (!$TPL["message"] && $taskID) {
    $task = new task();
    $task->set_id($taskID);
    $task->select();
    $task->skip_modified_fields = true;
    $task->set_value("taskName",$email_receive->mail_headers["subject"]);
    $task->set_value("taskDescription",$email_receive->mail_text);
    $task->save();
    if ($task->get_id()) {
      $TPL["message_good"][] = "Created task ".$task->get_id()." and moved the email to the task's mail folder.";
      $mailbox = "INBOX/task".$task->get_id();
      $email_receive->create_mailbox($mailbox) and $TPL["message_good"][] = "Created mailbox: ".$mailbox;
      $email_receive->move_mail($_REQUEST["id"],$mailbox) and $TPL["message_good"][] = "Moved email ".$_REQUEST["id"]." to ".$mailbox;
    }
  }
  if (is_object($email_receive)) {
    $email_receive->close(); 
  }
  alloc_redirect($TPL["url_alloc_inbox"]);
// Else if they want to mark the email as unread
} else if ($_REQUEST["unreadmail"] && $_REQUEST["id"] && $_REQUEST["hash"]) {
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
  $email_receive->set_msg($_REQUEST["id"]);
  $email_receive->set_unread();
  $email_receive->close();
  alloc_redirect($TPL["url_alloc_inbox"]);

// Else if they want to mark the email as read
} else if ($_REQUEST["readmail"] && $_REQUEST["id"] && $_REQUEST["hash"]) {
  $email_receive = new email_receive($info);
  $email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
  $email_receive->set_msg($_REQUEST["id"]);
  list($h,$b) = $email_receive->get_raw_header_and_body();
  $email_receive->close();
  alloc_redirect($TPL["url_alloc_inbox"]);
}






// Get list of emails
$email_receive = new email_receive($info);
$email_receive->open_mailbox(config::get_config_item("allocEmailFolder"),OP_HALFOPEN | OP_READONLY);
$email_receive->check_mail();
$new_nums = $email_receive->get_new_email_msg_uids();
$msg_nums = $email_receive->get_all_email_msg_uids();

if ($msg_nums) {
  foreach ($msg_nums as $num) {
    $row = array();
    $email_receive->set_msg($num);
    $email_receive->get_msg_header();
    $row["from"] = $email_receive->get_printable_from_address();
    in_array($num,(array)$new_nums) and $row["new"] = true;

    $row["id"] = $num;
    $row["date"] = $email_receive->mail_headers["date"];
    $row["subject"] = $email_receive->mail_headers["subject"];
    $TPL["rows"][] = $row;
  }
}
$email_receive->close();

include_template("templates/inboxM.tpl");

?>
