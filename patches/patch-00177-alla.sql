
-- drop task.taskSubStatus
ALTER TABLE task DROP taskSubStatus;


DELETE FROM config WHERE name = 'taskStatusOptions';
