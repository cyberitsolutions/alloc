-- Shorten taskType name of Parent/Phase to just Parent
UPDATE taskType set taskTypeName = 'Parent' WHERE taskTypeName = 'Parent/Phase';

