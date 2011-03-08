-- Add 'task estimator' field to tasks

ALTER TABLE task ADD estimatorID INT DEFAULT NULL AFTER duplicateTaskID;
ALTER TABLE task ADD CONSTRAINT task_estimatorID FOREIGN KEY (estimatorID) REFERENCES person (personID);

