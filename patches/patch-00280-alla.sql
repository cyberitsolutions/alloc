
-- create new table
DROP TABLE IF EXISTS audit;
CREATE TABLE audit (
  auditID integer NOT NULL auto_increment PRIMARY KEY,
  taskID integer DEFAULT NULL,
  projectID integer DEFAULT NULL,
  personID integer NOT NULL,
  dateChanged datetime NOT NULL,
  field varchar(255) default NULL,
  value text
) ENGINE=InnoDB PACK_KEYS=0;

-- add constraints
ALTER TABLE audit ADD CONSTRAINT audit_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE audit ADD CONSTRAINT audit_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);
ALTER TABLE audit ADD CONSTRAINT audit_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);


-- port data over from auditItem to audit
-- remove records that refer to project and tasks that no longer exists.
-- Otherwise the referential integrity will prevent the data being ported over.
DELETE FROM auditItem WHERE entityName = 'task' AND entityID IN
  (SELECT p.entityID FROM (SELECT entityID FROM auditItem LEFT JOIN task ON task.taskID = auditItem.entityID
                            WHERE entityName = 'task' AND taskID IS NULL GROUP BY entityID) AS p);

DELETE FROM auditItem WHERE entityName = 'project' AND entityID IN
  (SELECT p.entityID FROM (SELECT entityID FROM auditItem LEFT JOIN project ON project.projectID = auditItem.entityID
                            WHERE entityName = 'project' AND projectID IS NULL GROUP BY entityID) AS p);

INSERT INTO audit (taskID,personID,dateChanged,field,value)
SELECT entityID, personID, dateChanged, fieldName, oldValue FROM auditItem WHERE entityName = 'task';

INSERT INTO audit (projectID,personID,dateChanged,field,value)
SELECT entityID, personID, dateChanged, fieldName, oldValue FROM auditItem WHERE entityName = 'project';

-- nuke old tables
DROP TABLE auditItem;
DROP TABLE changeType;

-- fix permissions
DELETE FROM permission WHERE tableName = 'auditItem';
INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
VALUES ('audit'                    ,0  ,''         ,NULL ,8+1        ,'Allow all to create and read audit items.');

-- add created records
INSERT INTO audit (taskID,personID,dateChanged,field,value)
SELECT taskID, creatorID, dateCreated, "created", "The task was created."
FROM task WHERE (creatorID IS NOT NULL);

INSERT INTO audit (projectID,personID,dateChanged,field,value)
SELECT projectID, IFNULL(projectCreatedUser,projectModifiedUser), IFNULL(projectCreatedTime,projectModifiedTime), "created", "The project was created."
FROM project WHERE (projectCreatedUser IS NOT NULL OR projectModifiedUser IS NOT NULL);
