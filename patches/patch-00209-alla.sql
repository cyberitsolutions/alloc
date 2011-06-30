-- New table to allow asynchronous search index building
CREATE TABLE indexQueue (
  indexQueueID integer NOT NULL auto_increment PRIMARY KEY,
  entity varchar(255) NOT NULL,
  entityID integer NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

-- Add unique index across entity and entityID
CREATE UNIQUE INDEX entity_entityID ON indexQueue (entity,entityID);

