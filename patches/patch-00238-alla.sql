
-- new task status for blocked tasks
insert into taskStatus (taskStatusID,taskStatusLabel,taskStatusColour,taskStatusSeq,taskStatusActive) values ("pending_tasks","Pending: Tasks", "#f9ca7f", 55, 1);


DROP TABLE IF EXISTS pendingTask;
CREATE TABLE pendingTask (
  taskID integer NOT NULL,
  pendingTaskID integer NOT NULL,
  PRIMARY KEY(taskID, pendingTaskID)
) ENGINE=InnoDB PACK_KEYS=0;


ALTER TABLE pendingTask ADD CONSTRAINT pendingTask_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);
ALTER TABLE pendingTask ADD CONSTRAINT pendingTask_pendingTaskID FOREIGN KEY (pendingTaskID) REFERENCES task (taskID);

