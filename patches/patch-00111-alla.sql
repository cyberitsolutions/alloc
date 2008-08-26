-- Change project.clientID to be allowed to be NULL
ALTER TABLE project CHANGE clientID clientID int(11) DEFAULT NULL;
