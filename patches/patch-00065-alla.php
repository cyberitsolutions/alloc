<?php
// UPDATE the invoice.clientID field, by attempting to determine which
// client each invoice belongs to via the invoice.invoiceName field.

#$hardcoded['Finsys Pty Ltd'] = 
#$hardcoded['DOJ'] = 
#$hardcoded['OSV'] = 1823;
#$hardcoded['Marker Solutions and Services'] = 3363;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;
#$hardcoded[''] = ;


$db = new db_alloc();
$db2 = new db_alloc();

$q = sprintf("SELECT distinct invoiceName FROM invoice");
$db->query($q);

while ($db->next_record()) {

  list($probable_clientID,$client_percent) = get_clientID_from_name($db->f("invoiceName"));

  if ($client_percent > 80) {
    $q = sprintf("UPDATE invoice SET clientID = %d WHERE invoiceName = '%s'",$probable_clientID,db_esc($db->f("invoiceName")));
    $db2->query($q);
  }
}
?>
