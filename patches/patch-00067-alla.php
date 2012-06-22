<?php

// UPDATE the newly created invoiceItem.iiDate field with the date from the invoice table (used to be invoiceDate, now it's invoiceDateFrom)
$db = new db_alloc();
$db2 = new db_alloc();

$db->query("SELECT * FROM invoice");
while ($db->next_record()) {
  $db2->query(prepare("UPDATE invoiceItem SET iiDate = '%s' WHERE invoiceID = %d",$db->f("invoiceDateFrom"),$db->f("invoiceID")));
}

?>
