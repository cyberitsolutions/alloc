
-- Nuke unused fields
ALTER TABLE permission DROP FOREIGN KEY `permission_personID`;
ALTER TABLE permission DROP personID;
ALTER TABLE permission DROP allow;
