-- Move reminder.metaPerson AFTER personID, so that make test_db works again
ALTER TABLE reminder CHANGE metaPerson metaPerson int(11) DEFAULT NULL AFTER personID;
