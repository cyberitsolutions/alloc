-- new standard creator/date fields for the reminder table
ALTER TABLE reminder ADD reminderCreatedTime DATETIME NOT NULL AFTER reminderContent; 
ALTER TABLE reminder ADD reminderCreatedUser INTEGER NOT NULL AFTER reminderCreatedTime;
UPDATE reminder SET reminderModifiedUser = 1 WHERE reminderModifiedUser IS NULL OR reminderModifiedUser = '' OR reminderModifiedUser = 0;
UPDATE reminder SET reminderCreatedUser = reminderModifiedUser;
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderCreatedUser FOREIGN KEY (reminderCreatedUser) REFERENCES person (personID);
