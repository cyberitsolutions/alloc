
ALTER TABLE client DROP clientComment;
ALTER TABLE client ADD clientURL TEXT DEFAULT NULL;
