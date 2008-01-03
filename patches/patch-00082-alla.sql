-- Changed default zeroes, to default nulls.
ALTER TABLE invoiceItem MODIFY invoiceID int(11) NOT NULL;
ALTER TABLE invoiceItem MODIFY timeSheetID int(11) DEFAULT NULL;
ALTER TABLE invoiceItem MODIFY timeSheetItemID int(11) DEFAULT NULL;
ALTER TABLE invoiceItem MODIFY expenseFormID int(11) DEFAULT NULL;
ALTER TABLE invoiceItem MODIFY transactionID int(11) DEFAULT NULL;

ALTER TABLE expenseForm MODIFY clientID int(11) DEFAULT NULL;
