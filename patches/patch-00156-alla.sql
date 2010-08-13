
-- Drop taskType constraint on the task table
ALTER TABLE task DROP FOREIGN KEY task_taskTypeID;

-- Change task.taskTypeID from an int to a varchar
ALTER TABLE task CHANGE taskTypeID taskTypeID varchar(255) NOT NULL;

-- Change the values into their text labels
UPDATE task SET taskTypeID = "Task" WHERE taskTypeID = 1;
UPDATE task SET taskTypeID = "Parent" WHERE taskTypeID = 2;
UPDATE task SET taskTypeID = "Message" WHERE taskTypeID = 3;
UPDATE task SET taskTypeID = "Fault" WHERE taskTypeID = 4;
UPDATE task SET taskTypeID = "Milestone" WHERE taskTypeID = 5;

-- Update the taskType table
ALTER TABLE taskType DROP taskTypeName;
ALTER TABLE taskType CHANGE taskTypeSequence taskTypeSeq INT NOT NULL AFTER taskTypeID;

-- Change task.taskTypeID from an int to a varchar
ALTER TABLE taskType CHANGE taskTypeID taskTypeID varchar(255) NOT NULL;

-- Nuke taskType data
DELETE FROM taskType;

-- Move the labels from the name field over to the id field
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Task'     ,10,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Parent'   ,20,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Message'  ,30,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Fault'    ,40,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Milestone',50,true);

-- Re-add the constraint to the task table
ALTER TABLE task ADD CONSTRAINT task_taskTypeID FOREIGN KEY (taskTypeID) REFERENCES taskType (taskTypeID);

