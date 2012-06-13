-- Re-apply DB triggers to fix a bug with editing timesheet
-- items.
DELETE FROM patchLog WHERE patchName = 'patch-00242-alla.php';

