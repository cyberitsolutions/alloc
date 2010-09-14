
-- Fix the case of the client statuses
UPDATE clientStatus SET clientStatusID = 'Archived' WHERE clientStatusID = 'archived';
UPDATE clientStatus SET clientStatusID = 'Current' WHERE clientStatusID = 'current';
UPDATE clientStatus SET clientStatusID = 'Potential' WHERE clientStatusID = 'potential';

-- Just in case the database cascade doesn't work, update the client records
UPDATE client SET clientStatus = 'Archived' WHERE clientStatus = 'archived';
UPDATE client SET clientStatus = 'Current' WHERE clientStatus = 'current';
UPDATE client SET clientStatus = 'Potential' WHERE clientStatus = 'potential';


