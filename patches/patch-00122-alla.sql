-- Make the Email To field longer for comments
ALTER TABLE comment CHANGE commentEmailRecipients commentEmailRecipients TEXT DEFAULT NULL;
