
-- new table for taskStatus relationship
CREATE TABLE taskStatus (
  taskStatusID varchar(255) PRIMARY KEY,
  taskStatusLabel varchar(255) DEFAULT NULL,
  taskStatusColour varchar(255) DEFAULT NULL,
  taskStatusSeq integer NOT NULL,
  taskStatusActive boolean default true
) ENGINE=InnoDB PACK_KEYS=0;

-- data
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("open_notstarted"  ,"Open: Not Started" ,"#b0d9b0", 10,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("open_inprogress"  ,"Open: In Progress" ,"#66f066", 20,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_info"     ,"Pending: Info"     ,"#f9ca7f", 30,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_manager"  ,"Pending: Manager"  ,"#f9ca7f", 40,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_client"   ,"Pending: Client"   ,"#f9ca7f", 50,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_invalid"   ,"Closed: Invalid"   ,"#e0e0e0", 60,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_duplicate" ,"Closed: Duplicate" ,"#e0e0e0", 70,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_incomplete","Closed: Incomplete","#e0e0e0", 80,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_complete"  ,"Closed: Completed" ,"#e0e0e0", 90,true);

-- fix bad data
UPDATE task SET taskStatus = 'open', taskSubStatus = 'notstarted' WHERE taskStatus = 'open_notstarted';
UPDATE task SET taskStatus = 'pending', taskSubStatus = 'info' WHERE taskStatus = 'pending_info';

-- update task table data
UPDATE task SET taskStatus = 'open_notstarted'   WHERE taskSubstatus = 'notstarted';
UPDATE task SET taskStatus = 'open_inprogress'   WHERE taskSubstatus = 'inprogress';
UPDATE task SET taskStatus = 'pending_info'      WHERE taskSubstatus = 'info';
UPDATE task SET taskStatus = 'pending_manager'   WHERE taskSubstatus = 'manager';
UPDATE task SET taskStatus = 'pending_client'    WHERE taskSubstatus = 'client';
UPDATE task SET taskStatus = 'closed_invalid'    WHERE taskSubstatus = 'invalid';
UPDATE task SET taskStatus = 'closed_duplicate'  WHERE taskSubstatus = 'duplicate';
UPDATE task SET taskStatus = 'closed_incomplete' WHERE taskSubstatus = 'incomplete';
UPDATE task SET taskStatus = 'closed_complete'   WHERE taskSubstatus = 'complete';

-- add constraint
ALTER TABLE task ADD CONSTRAINT task_taskStatus FOREIGN KEY (taskStatus) REFERENCES taskStatus (taskStatusID);

-- update data in the auditItem table
UPDATE auditItem SET oldValue = 'open_notstarted'   WHERE fieldName = 'taskSubStatus' AND oldValue = 'notstarted';
UPDATE auditItem SET oldValue = 'open_inprogress'   WHERE fieldName = 'taskSubStatus' AND oldValue = 'inprogress';
UPDATE auditItem SET oldValue = 'pending_info'      WHERE fieldName = 'taskSubStatus' AND oldValue = 'info';
UPDATE auditItem SET oldValue = 'pending_manager'   WHERE fieldName = 'taskSubStatus' AND oldValue = 'manager';
UPDATE auditItem SET oldValue = 'pending_client'    WHERE fieldName = 'taskSubStatus' AND oldValue = 'client';
UPDATE auditItem SET oldValue = 'closed_invalid'    WHERE fieldName = 'taskSubStatus' AND oldValue = 'invalid';
UPDATE auditItem SET oldValue = 'closed_duplicate'  WHERE fieldName = 'taskSubStatus' AND oldValue = 'duplicate';
UPDATE auditItem SET oldValue = 'closed_incomplete' WHERE fieldName = 'taskSubStatus' AND oldValue = 'incomplete';
UPDATE auditItem SET oldValue = 'closed_complete'   WHERE fieldName = 'taskSubStatus' AND oldValue = 'complete';

-- fix the fieldname from taskSubStatus to taskStatus
UPDATE auditItem SET fieldName = 'taskStatus' WHERE fieldName = 'taskSubStatus';

