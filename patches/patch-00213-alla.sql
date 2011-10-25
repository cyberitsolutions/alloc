-- New field to track activating and deactivating interested parties
ALTER TABLE interestedParty ADD interestedPartyActive boolean default true AFTER interestedPartyCreatedTime;
