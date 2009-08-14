DROP TABLE IF EXISTS taskSearchable;
CREATE TABLE taskSearchable (
  taskID integer NOT NULL PRIMARY KEY,
  taskName varchar(255) NOT NULL default '',
  projectID integer DEFAULT NULL,
  FULLTEXT KEY `taskSearchable_taskName` (`taskName`)
) ENGINE=MyISAM;

INSERT INTO taskSearchable SELECT taskID, taskName, projectID FROM task;

CREATE INDEX projectID ON taskSearchable (projectID);
CREATE INDEX taskName ON taskSearchable (taskName);

INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, actions, comment) 
VALUES ('taskSearchable',0,NULL ,'',true,NULL,15,NULL);
