
-- Clear out old entries
DELETE FROM permission WHERE tableName = 'taskSearchable';
DELETE FROM permission WHERE tableName = 'taskType';
