-- Permit blanks for projectPerson.rate
ALTER TABLE projectPerson CHANGE rate rate BIGINT DEFAULT NULL;
