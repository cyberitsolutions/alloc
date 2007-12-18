<?php
// Remove the commenting and run the patch to attempt to update the
// invoice.clientID field with a best guess based on the invoice.invoiceName
// field.

#$db = new db_alloc();
#$db2 = new db_alloc();
#
#$q = sprintf("SELECT distinct invoiceName FROM invoice");
#$db->query($q);
#
#while ($db->next_record()) {
#
#  list($probable_clientID,$client_percent) = get_clientID_from_name($db->f("invoiceName"));
#
#  if ($client_percent > 80) {
#    $q = sprintf("UPDATE invoice SET clientID = %d WHERE invoiceName = '%s'",$probable_clientID,db_esc($db->f("invoiceName")));
#    $db2->query($q);
#  }
#}
?>
