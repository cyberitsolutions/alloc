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

require_once("../alloc.php");

$lockfile = ATTACHMENTS_DIR."mail.lock.person_".$current_user->get_id();

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

if (!$info["host"]) {
  die("Email mailbox host not defined, assuming email fetch function is inactive.");
}

$mail = new alloc_email_receive($info,$lockfile);
$mail->open_mailbox(config::get_config_item("allocEmailFolder"));

if ($_GET["msg_uid"]) {
  //header('Content-Disposition: attachment; filename="task_comment_email.txt"');
  header('Content-Type: text/plain; charset=utf-8');
  list($h,$b) = $mail->get_raw_email_by_msg_uid($_GET["msg_uid"]);
}
$mail->close();
echo $h.$b;
?>
