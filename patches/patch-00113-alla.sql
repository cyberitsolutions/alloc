
-- Changes to bring other peoples databases in sync with the default db installation structure
ALTER TABLE interestedParty CHANGE entityID entityID int(11) NOT NULL;
ALTER TABLE interestedParty CHANGE entity entity varchar(255) NOT NULL;
ALTER TABLE invoice CHANGE clientID clientID int(11) NOT NULL;
ALTER TABLE invoiceItem CHANGE invoiceID invoiceID int(11) NOT NULL;
ALTER TABLE productSale CHANGE projectID projectID int(11) NOT NULL;
ALTER TABLE productSaleItem CHANGE productID productID int(11) NOT NULL;
ALTER TABLE productSaleItem CHANGE productSaleID productSaleID int(11) NOT NULL;
ALTER TABLE productSaleTransaction CHANGE productSaleItemID productSaleItemID int(11) NOT NULL;
ALTER TABLE role CHANGE roleLevel roleLevel enum('person','project') NOT NULL;

ALTER TABLE savedView CHANGE savedViewID savedViewID int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE savedView CHANGE personID personID int(11) NOT NULL;

ALTER TABLE savedView CHANGE formName formName varchar(32) NOT NULL;
ALTER TABLE savedView CHANGE viewName viewName varchar(255) NOT NULL;

ALTER TABLE transaction CHANGE tfID tfID int(11) NOT NULL;
ALTER TABLE transaction CHANGE fromTfID fromTfID int(11) NOT NULL;
ALTER TABLE transaction add index idx_fromTfID (fromTfID);

ALTER TABLE transaction CHANGE transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','tax','product') NOT NULL;
ALTER TABLE transactionRepeat CHANGE transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','tax','product') NOT NULL;

ALTER TABLE transactionRepeat CHANGE fromTfID fromTfID int(11) NOT NULL;
ALTER TABLE transactionRepeat CHANGE tfID tfID int(11) NOT NULL;

ALTER TABLE history ADD INDEX idx_personID (personID);

ALTER TABLE task CHANGE projectID projectID int(11) DEFAULT NULL;
ALTER TABLE task CHANGE parentTaskID parentTaskID int(11) DEFAULT NULL;
