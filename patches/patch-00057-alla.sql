-- Nuke entries in taskCCList where fullName is incorrectly set to a single space
UPDATE taskCCList SET fullName = NULL WHERE fullName NOT REGEXP "[[:alnum:]]+";


