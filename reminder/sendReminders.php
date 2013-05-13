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
require_once("../alloc.php");

$db = new db_alloc();

// do advanced notice emails
$query = prepare("SELECT *
                    FROM reminder
                   WHERE reminderActive = 1
                     AND reminderAdvNoticeSent = 0
                     AND NOW() > 
                         CASE
                           WHEN reminderAdvNoticeInterval = 'Minute' THEN DATE_SUB(reminderTime, INTERVAL reminderAdvNoticeValue MINUTE)
                           WHEN reminderAdvNoticeInterval = 'Hour'   THEN DATE_SUB(reminderTime, INTERVAL reminderAdvNoticeValue HOUR)
                           WHEN reminderAdvNoticeInterval = 'Day'    THEN DATE_SUB(reminderTime, INTERVAL reminderAdvNoticeValue DAY)
                           WHEN reminderAdvNoticeInterval = 'Week'   THEN DATE_SUB(reminderTime, INTERVAL reminderAdvNoticeValue WEEK)
                           WHEN reminderAdvNoticeInterval = 'No'     THEN NULL
                         END
                 ");

$db->query($query);
while ($db->next_record()) {
  $reminder = new reminder();
  $reminder->read_db_record($db);
  //echo "<br>Adv: ".$reminder->get_id();
  $current_user = new person();
  $current_user->load_current_user($db->f('reminderCreatedUser'));
  singleton("current_user",$current_user);
  if (!$reminder->is_alive()) {
    $reminder->deactivate();
  } else {
    $reminder->mail_advnotice();
  }
}


// do reminders
$query = prepare("SELECT *
                    FROM reminder
                   WHERE reminderActive = 1
                     AND (reminderTime IS NULL OR NOW() > reminderTime)
                 ");

$db->query($query);
while ($db->next_record()) {
  $reminder = new reminder();
  $reminder->read_db_record($db);
  //echo "<br>Rem: ".$reminder->get_id();
  $current_user = new person();
  $current_user->load_current_user($db->f('reminderCreatedUser'));
  singleton("current_user",$current_user);
  if (!$reminder->is_alive()) {
    $reminder->deactivate();
  } else {
    $reminder->mail_reminder();
  }
}


?>
