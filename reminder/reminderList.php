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

function show_reminders($template) {
  global $TPL, $current_user;

  // show all reminders for this project
  $db = new db_alloc;
  if ($current_user->have_role("admin") || $current_user->have_role("manage")) {
    $query = sprintf("SELECT * FROM reminder WHERE personID like '%s' ORDER BY reminderTime,reminderType", $_REQUEST["filter_recipient"]);
  } else {
    $query = sprintf("SELECT * FROM reminder WHERE personID = '%s' ORDER BY reminderType,reminderTime", $current_user->get_id());
  }
  $db->query($query);
  while ($db->next_record()) {
    $reminder = new reminder;
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
      $TPL["reminder_reminderRecipient"] = $reminder->get_recipient_description();
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
    if (!$_REQUEST["filter_recipient"]) {
      $_REQUEST["filter_recipient"] = $current_user->get_id();
    }

    $db = new db_alloc;
    $db->query("select username,personID from person order by username");
    while ($db->next_record()) {
      $recipientOptions[$db->f("personID")] = $db->f("username");
    }
    $TPL["recipientOptions"] = page::select_options($recipientOptions, $_REQUEST["filter_recipient"]);
    include_template($template);
  }
}


include_template("templates/reminderListM.tpl");


?>
