-- Supercede emailUID, instead use email's message id.
ALTER TABLE timeSheetItem ADD emailMessageID varchar(255) DEFAULT NULL;
