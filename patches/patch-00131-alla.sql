ALTER TABLE transaction ADD transactionGroupID INT(11) DEFAULT NULL AFTER transactionRepeatID;
ALTER TABLE transaction ADD INDEX idx_transactionGroupID (transactionGroupID);

