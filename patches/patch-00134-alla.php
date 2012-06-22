<?php

// Move the client.clientPrimaryContactID over to clientContact.primaryContact
$q = "alter table clientContact add primaryContact boolean default false";
$db = new db_alloc();
$db2 = new db_alloc();
$db->query($q);

$q = "select clientID, clientPrimaryContactID from client";
$db->query($q);

while ($row = $db->row()) {
  if ($row["clientPrimaryContactID"]) {
    $q = prepare("update clientContact set primaryContact = true where clientContactID = %d",$row["clientPrimaryContactID"]);
    $db2->query($q);
  }
}

$q = "alter table client drop clientPrimaryContactID";
$db->query($q);

?>
