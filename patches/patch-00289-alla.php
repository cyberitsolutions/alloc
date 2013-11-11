<?php

// add in some reminder recipients for every task reminder that is missing recipients

$q = "SELECT reminder.reminderID
            ,reminder.reminderSubject
            ,COUNT(reminderRecipient.reminderRecipientID)
        FROM reminder
   LEFT JOIN reminderRecipient ON reminder.reminderID = reminderRecipient.reminderID
       WHERE reminder.reminderActive = 1
         AND reminder.reminderSubject like '%reopen%'
    GROUP BY reminder.reminderID
      HAVING COUNT(reminderRecipient.reminderRecipientID) = 0";

$db = new db_alloc();
$db->query($q);

while ($row = $db->row()) {
  $reminderRecipient = new reminderRecipient();
  $reminderRecipient->set_value("reminderID",$row["reminderID"]);
  $reminderRecipient->set_value("metaPersonID",-2);
  $reminderRecipient->save();
  $reminderRecipient = new reminderRecipient();
  $reminderRecipient->set_value("reminderID",$row["reminderID"]);
  $reminderRecipient->set_value("metaPersonID",-3);
  $reminderRecipient->save();
}

?>
