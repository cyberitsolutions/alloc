-- Change the taskCCList table into a generic interested parties table
ALTER TABLE taskCCList RENAME TO interestedParty;
ALTER TABLE interestedParty CHANGE taskCCListID interestedPartyID int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE interestedParty CHANGE taskID entityID int(11) NOT NULL;
ALTER TABLE interestedParty ADD entity VARCHAR(255) NOT NULL AFTER interestedPartyID;
ALTER TABLE interestedParty ADD personID int(11) DEFAULT NULL AFTER emailAddress;
ALTER TABLE interestedParty ADD clientContactID int(11) DEFAULT NULL AFTER personID;

-- Update all the old taskCCList entries to be default to entity task
UPDATE interestedParty set entity = 'task';






