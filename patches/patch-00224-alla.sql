
-- New table to forge error messages for mysql
DROP TABLE IF EXISTS error;
CREATE TABLE error (
  errorID varchar(255) NOT NULL PRIMARY KEY
) ENGINE=InnoDB PACK_KEYS=0;


-- Error messages for mysql
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to change time sheet status.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to delete time sheet unless status is edit.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Time sheet is not editable.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not editable.\n\n");


