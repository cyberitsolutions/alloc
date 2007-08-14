-- Rename modified fields on the sentEmailLog table to created, so that they populate again.
ALTER TABLE sentEmailLog CHANGE sentEmailLogModifiedTime sentEmailLogCreatedTime datetime DEFAULT NULL;
ALTER TABLE sentEmailLog CHANGE sentEmailLogModifiedUser sentEmailLogCreatedUser int(11) DEFAULT NULL;



