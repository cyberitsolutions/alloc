-- rename projectPersonRole to role and all its fields too
ALTER TABLE projectPersonRole RENAME TO role;
ALTER TABLE role CHANGE projectPersonRoleID roleID int(11) NOT NULL auto_increment;
ALTER TABLE role CHANGE projectPersonRoleName roleName varchar(255) default NULL;
ALTER TABLE role CHANGE projectPersonRoleHandle roleHandle varchar(255) default NULL;
ALTER TABLE role CHANGE projectPersonRoleSortKey roleSequence int(11) default NULL;

-- add new roleLevel field to determine at which level this role applies
ALTER TABLE role add roleLevel ENUM('person','project') NOT NULL AFTER roleHandle;

-- Update existing entries to project
UPDATE role SET roleLevel = 'project';

-- Stick in new person level roles
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (4,'Super User','god', 'person', 10);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (5,'Finance Admin','admin', 'person', 20);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (6,'Project Manager','manage', 'person', 30);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (7,'Employee','employee','person', 40);
 
-- Fix up projectPerson table too
ALTER TABLE projectPerson CHANGE projectPersonRoleID roleID int(11) NOT NULL DEFAULT 0;

