
-- nuke modified time/dates (should already be contained in created time/users fields)
UPDATE comment SET commentModifiedUser = NULL;
UPDATE comment SET commentModifiedTime = NULL;

