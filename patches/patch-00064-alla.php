<?php
  // UPDATE invoice.invoiceStatus from the invoiceItem.status field

  $db2 = new db_alloc();

  $q = prepare("SELECT invoiceID FROM invoiceItem WHERE status = 'paid'");
  $db = new db_alloc();
  $db->query($q);

  while ($db->next_record()) {
    $q = prepare("UPDATE invoice SET invoiceStatus = 'finished' WHERE invoiceID = %d",$db->f("invoiceID"));
    $db2->query($q);
  }


  $q = prepare("SELECT invoiceID FROM invoiceItem WHERE status = 'allocated'");
  $db = new db_alloc();
  $db->query($q);

  while ($db->next_record()) {
    $q = prepare("UPDATE invoice SET invoiceStatus = 'allocate' WHERE invoiceID = %d",$db->f("invoiceID"));
    $db2->query($q);
  }


  $q = prepare("SELECT invoiceID FROM invoiceItem WHERE status = 'pending'");
  $db = new db_alloc();
  $db->query($q);

  while ($db->next_record()) {
    $q = prepare("UPDATE invoice SET invoiceStatus = 'edit' WHERE invoiceID = %d",$db->f("invoiceID"));
    $db2->query($q);
  }
?>
