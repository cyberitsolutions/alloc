-- Changing type slightly so that structure conciles with db_struct
ALTER TABLE config CHANGE name name VARCHAR(255) NOT NULL;

-- Nuke old table
DROP TABLE IF EXISTS `eventFilter`;

-- Will have to add this to tables when creating them..
ALTER TABLE token PACK_KEYS=0;
ALTER TABLE tokenAction PACK_KEYS=0;


