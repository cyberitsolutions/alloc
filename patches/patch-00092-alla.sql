-- Add an index on the timeSheetItem for the taskID field
ALTER TABLE timeSheetItem ADD INDEX idx_taskID (taskID);

-- Add an index on the projectPerson for the projectID and personID
ALTER TABLE projectPerson ADD INDEX idx_person_project (projectID,personID);
