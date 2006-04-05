<?php
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
        $headers = "From: alloc-admin@cyber.com.au";
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
