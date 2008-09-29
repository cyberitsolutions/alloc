-- Nuke unused table
DROP TABLE IF EXISTS projectModificationNote;
DELETE FROM permission WHERE tableName = "projectModificationNote";
