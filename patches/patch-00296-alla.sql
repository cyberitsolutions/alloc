-- reapply the db_triggers.sql file, but telling the updater system we need to apply patch 242 again.
DELETE FROM patchLog WHERE patchName = 'patch-00242-alla.php';
