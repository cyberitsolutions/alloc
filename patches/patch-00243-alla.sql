
-- This patch will force a re-apply of the triggers patch
-- We need to re-apply the triggers because they have changed
-- for the time sheet item / task actual start date bug.
DELETE FROM patchLog WHERE patchName = 'patch-00242-alla.php';
