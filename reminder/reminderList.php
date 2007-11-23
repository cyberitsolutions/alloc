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

require_once("../alloc.php");

function show_reminders($template) {
  global $TPL, $current_user;

  // show all reminders for this project
  $reminder = new reminder;
  $db = new db_alloc;
  if ($current_user->have_role("admin") || $current_user->have_role("manage")) {
    $query = sprintf("SELECT * FROM reminder WHERE personID like '%s' ORDER BY reminderTime,reminderType", $_POST["filter_recipient"]);
  } else {
    $query = sprintf("SELECT * FROM reminder WHERE personID = '%s' ORDER BY reminderType,reminderTime", $current_user->get_id());
  }
  $db->query($query);
  while ($db->next_record()) {
    $reminder->read_db_record($db);
    $reminder->set_tpl_values(DST_HTML_ATTRIBUTE, "reminder_");

    // only show reminder in list if project/task/client arent archived/complete
    if ($reminder->is_alive()) {

      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }
      $person = new person;
      $person->set_id($reminder->get_value('personID'));
      $person->select();
      $TPL["reminder_reminderRecipient"] = $person->get_value('username');
      $TPL["returnToParent"] = "list";
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      include_template($template);
    }
  }
}

function show_reminder_filter($template) {
  global $current_user, $TPL;
  if ($current_user->have_role("admin") || $current_user->have_role("manage")) {

    // Set default filter parameter
    if (!$_POST["filter_recipient"]) {
      $_POST["filter_recipient"] = $current_user->get_id();
    }

    $db = new db_alloc;
    $db->query("select username,personID from person order by username");
    while ($db->next_record()) {
      $recipientOptions[$db->f("personID")] = $db->f("username");
    }
    $TPL["recipientOptions"] = get_select_options($recipientOptions, $_POST["filter_recipient"]);
    include_template($template);
  }
}






include_template("templates/reminderListM.tpl");

page_close();



?>
