-- Fix typo in absence.absenceType
ALTER TABLE absence CHANGE absenceType absenceType enum('Annual Leave','Holiday','Illness','Other') DEFAULT NULL;


-- Stick a new config item in which dictates the first day of the calendar
INSERT INTO config (name,value) VALUES ("calendarFirstDay","Sun");

-- Don't need eventFilter lines in permission table anymore.
DELETE FROM permission WHERE tableName = "eventFilter";

-- New record into permission to allow admin to create absence records for users.
INSERT INTO permission (tableName,entityID,personID,roleName,actions,sortKey,allow,comment) VALUES ('absence','','','admin','15',NULL,'Y','Allow all admin to manipulate all absence records.');

