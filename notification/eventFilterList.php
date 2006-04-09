<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

function showEventFilterList($template) {
  global $TPL, $current_user;

  $db = new db_alloc;
  $query = "SELECT * FROM eventFilter WHERE action='email' AND personID=".$current_user->get_id();
  $db->query($query);
  while ($db->next_record()) {
    $eventFilter = new eventFilter;
    $eventFilter->read_db_record($db);
    $eventFilter->set_tpl_values(DST_HTML_DISPLAY);
    $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

    include_template($template);
  }
}

function show_reminders($template) {
  global $TPL, $current_user, $filter_recipient;

  // show all reminders for this project
  $reminder = new reminder;
  $db = new db_alloc;
  $permissions = explode(",", $current_user->get_value("perms"));
  if (in_array("admin", $permissions) || in_array("manage", $permissions)) {
    $query = sprintf("SELECT * FROM reminder WHERE personID like '%s' ORDER BY reminderTime,reminderType", $filter_recipient);
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
      $TPL["returnToParent"] = "f";
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      include_template($template);
    }
  }
}

function show_reminder_filter($template) {
  global $current_user, $TPL, $filter_recipient;
  $permissions = explode(",", $current_user->get_value("perms"));
  if (in_array("admin", $permissions) || in_array("manage", $permissions)) {

    // Set default filter parameter
    if (!isset($filter_recipient)) {
      $filter_recipient = $current_user->get_id();
    }

    $db = new db_alloc;
    $db->query("select username,personID from person order by username");
    while ($db->next_record()) {
      $recipientOptions[$db->f("personID")] = $db->f("username");
    }
    $TPL["recipientOptions"] = get_options_from_array($recipientOptions, $filter_recipient);
    include_template($template);
  }
}






include_template("templates/eventFilterListM.tpl");

page_close();



?>
