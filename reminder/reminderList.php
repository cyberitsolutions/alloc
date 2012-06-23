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

function show_reminders($template) {
  global $TPL;
  $current_user = &singleton("person");

  // show all reminders for this project
  $db = new db_alloc;

  $query = "SELECT * FROM reminder JOIN reminderRecipient ON reminder.reminderID = reminderRecipient.reminderID WHERE 1";

  if ($current_user->have_role("admin") || $current_user->have_role("manage")) {
    if ($_REQUEST["filter_recipient"]) {
      $query.= prepare(" AND personID = %d", $_REQUEST["filter_recipient"]);
    }
  } else {
    $query.= prepare(" AND personID = %d", $current_user->get_id());
  }

  if (imp($_REQUEST["filter_reminderActive"])) {
    $query.= prepare(" AND reminderActive = %d",$_REQUEST["filter_reminderActive"]);
  }

  $query.= " GROUP BY reminder.reminderID ORDER BY reminderTime,reminderType";

  $db->query($query);
  while ($db->next_record()) {
    $reminder = new reminder;
    $reminder->read_db_record($db);
    $reminder->set_values("reminder_");

    // only show reminder in list if project/task/client aren't archived/complete
    if ($reminder->is_alive()) {

      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }
      $TPL["returnToParent"] = "list";
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      include_template($template);
    }
  }
}

function show_reminder_filter($template) {
  $current_user = &singleton("person");
  global $TPL;
  if ($current_user->have_role("admin") || $current_user->have_role("manage")) {

    $TPL["reminderActiveOptions"] = page::select_options(array("1"=>"Active","0"=>"Inactive"),$_REQUEST["filter_reminderActive"]);

    $db = new db_alloc;
    $db->query("SELECT username,personID FROM person ORDER BY username");
    while ($db->next_record()) {
      $recipientOptions[$db->f("personID")] = $db->f("username");
    }
    $TPL["recipientOptions"] = page::select_options($recipientOptions, $_REQUEST["filter_recipient"]);
    include_template($template);
  }
}


include_template("templates/reminderListM.tpl");


?>
