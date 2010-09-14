
-- Fix up the case of the project statuses
UPDATE projectStatus SET projectStatusID = 'Current' WHERE projectStatusID = 'current';
UPDATE projectStatus SET projectStatusID = 'Archived' WHERE projectStatusID = 'archived';
UPDATE projectStatus SET projectStatusID = 'Potential' WHERE projectStatusID = 'potential';

-- Just in case the database cascade doesn't work, update the project records
UPDATE project SET projectStatus = 'Current' WHERE projectStatus = 'current';
UPDATE project SET projectStatus = 'Archived' WHERE projectStatus = 'archived';
UPDATE project SET projectStatus = 'Potential' WHERE projectStatus = 'potential';
