<?php
// This patch removes patch 162 from the patchLog table.
// Which should enable patch 162 to be run again.

// patch-00162-alla.php simply rebuilds the search index for clients.
// The rebuild of the client search index may be necessary for users that had the
// functionality deployed before the index was feature-complete.

// If the patch 162 isn't found in the patchLog table then it has yet to be applied.

$q = prepare("SELECT * FROM patchLog WHERE patchName = 'patch-00162-alla.php'");
$db = new db_alloc();
$db->query($q);

if ($db->row()) {
  $q = prepare("DELETE FROM patchLog WHERE patchName = 'patch-00162-alla.php'");
  $db->query($q);
}
?>
