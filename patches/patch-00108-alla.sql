-- Again: Update existing interestedParties, so that if they don't have a personID then they are external
UPDATE interestedParty SET external = 1 WHERE personID IS NULL AND entity = 'comment';

