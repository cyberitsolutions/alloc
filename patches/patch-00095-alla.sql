-- Add new field to interestedParty: external
ALTER TABLE interestedParty ADD external tinyint(1) DEFAULT NULL AFTER clientContactID;

-- Update existing interestedParties, so that if they don't have a personID then they are external
UPDATE interestedParty SET external = 1 WHERE personID IS NULL AND entity = 'comment';

