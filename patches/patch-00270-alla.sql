
-- new table for tracking manager time estimates
DROP TABLE IF EXISTS tsiHint;
CREATE TABLE tsiHint (
  tsiHintID integer NOT NULL auto_increment PRIMARY KEY,
  date date default NULL,
  duration decimal(9,2) default '0.00',
  personID integer NOT NULL,
  taskID integer default NULL,
  comment text,
  tsiHintCreatedTime datetime default NULL,
  tsiHintCreatedUser integer default NULL,
  tsiHintModifiedTime datetime DEFAULT NULL,
  tsiHintModifiedUser integer DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;


CREATE INDEX idx_tsiHinttaskID ON tsiHint (taskID);
CREATE INDEX idx_tsiHintDate ON tsiHint (date);
ALTER TABLE tsiHint ADD CONSTRAINT tsiHint_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE tsiHint ADD CONSTRAINT tsiHint_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);

INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
VALUES
('tsiHint'                  ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL);
