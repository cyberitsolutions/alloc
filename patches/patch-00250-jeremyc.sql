
-- move reminder recipients into their own table to permit multiple recipients for the one reminder.
CREATE TABLE reminderRecipient (
reminderRecipientID integer NOT NULL auto_increment PRIMARY KEY,
reminderID integer NOT NULL,
personID integer,
metaPersonID integer
) ENGINE=InnoDB PACK_KEYS = 0;
ALTER TABLE reminderRecipient ADD CONSTRAINT reminderRecipient_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE reminderRecipient ADD CONSTRAINT reminderRecipient_reminderID FOREIGN KEY (reminderID) REFERENCES reminder (reminderID);

INSERT INTO reminderRecipient (reminderID, personID, metaPersonID) SELECT reminderID, personID, metaPerson FROM reminder;
INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment) VALUES ('reminderRecipient' ,0  ,'' ,NULL ,1+2+4+8 ,NULL);

-- need to nuke indexes before dropping columns
ALTER TABLE reminder DROP FOREIGN KEY reminder_personID;
ALTER TABLE reminder DROP KEY reminder_personID;

ALTER TABLE reminder DROP personID;
ALTER TABLE reminder DROP metaPerson;

