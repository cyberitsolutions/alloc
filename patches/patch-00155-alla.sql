-- turns out this foreign constraint is not required.
ALTER TABLE reminder DROP FOREIGN KEY reminder_metaPerson;
