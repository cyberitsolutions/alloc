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

$info = inbox::get_mail_info();

if (!$info["host"]) {
  alloc_error("Email mailbox host not defined, assuming email receive function is inactive.",true);
}

$email_receive = new email_receive($info);
$email_receive->open_mailbox(config::get_config_item("allocEmailFolder"));
$email_receive->check_mail();
$num_new_emails = $email_receive->get_num_new_emails();

if ($num_new_emails >0) {
  $msg_nums = $email_receive->get_new_email_msg_uids(); 
  print $nl.date("Y-m-d H:i:s")." Found ".count($msg_nums)." new/unseen emails.";
  foreach ($msg_nums as $num) {
    $email_receive->set_msg($num);
    $email_receive->get_msg_header();
    inbox::process_one_email($email_receive);
  }
}
$email_receive->expunge();
$email_receive->close();

?>
