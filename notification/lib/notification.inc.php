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

class notification {
  function handle_event($event) {
    global $debug;

    $object = $event->get_object();

    $query = sprintf("SELECT * 
                              FROM eventFilter 
                              WHERE className='%s' AND eventName='%s' AND action='email'", $object->data_table, $event->get_name());
    if ($debug) {
      echo $query."<br>";
    }

    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $eventFilter = new eventFilter;
      $eventFilter->read_db_record($db);
      $object_filter = $eventFilter->get_value("objectFilter");
      $person = $eventFilter->get_foreign_object("person");
      if ($object->have_perm(PERM_MONITOR_EVENTS, $person)
          && ((!is_object($object_filter)) || $object_filter->check_object($object))) {
        $person = $eventFilter->get_foreign_object("person");
        $email_address = $person->get_value("emailAddress");
        $headers = "From: ".ALLOC_DEFAULT_FROM_ADDRESS;
        if ($email_address) {
          $subject = "Alloc Notification";

          if ($object->data_table == "timeSheet") {
            $msg = $object_filter->get_message($object);
          } else {
            $msg = $object->data_table." ".$object->get_display_value()." has been ".$event->get_name();
          }
          if ($debug) {
            echo "To: $email_address<br>
                                  Subject: Alloc Notification<br><br>".$object->data_table." ".$object->get_display_value()." has been ".$event->get_name()."<hr>";
          }
          mail($email_address, $subject, $msg, $headers);
        }
      }
    }
  }
}



?>
