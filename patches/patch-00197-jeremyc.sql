-- Speed up task page DB query.
CREATE INDEX idx_interestedParty_entityID ON interestedParty (entityID);
