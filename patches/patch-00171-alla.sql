
-- Add a couple of fields to interestedParty to track who created the entry and when
alter table interestedParty add interestedPartyCreatedUser integer DEFAULT NULL AFTER external;
alter table interestedParty add interestedPartyCreatedTime datetime DEFAULT NULL AFTER interestedPartyCreatedUser;
