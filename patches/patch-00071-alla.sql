-- Add CreatedTime and CreatedUser fields to transaction and transactionRepeat
ALTER TABLE transaction CHANGE dateEntered transactionCreatedTime datetime DEFAULT NULL;
ALTER TABLE transaction ADD transactionCreatedUser int(11) DEFAULT NULL AFTER quantity;
UPDATE transaction SET transactionCreatedUser = transactionModifiedUser;

ALTER TABLE transactionRepeat CHANGE dateEntered transactionRepeatCreatedTime datetime DEFAULT NULL;
ALTER TABLE transactionRepeat ADD transactionRepeatCreatedUser int(11) DEFAULT NULL AFTER transactionRepeatModifiedTime;
UPDATE transactionRepeat SET transactionRepeatCreatedUser = transactionRepeatModifiedUser;

