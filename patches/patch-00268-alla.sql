
-- Fix up the reminderTime field
ALTER TABLE reminder CHANGE  reminderTime reminderTime datetime DEFAULT NULL;
UPDATE reminder SET reminderTime = NULL WHERE reminderTime = '0000-00-00 00:00:00';
