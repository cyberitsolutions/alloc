-- Add fromTfID to allow proper double-entry bookkeeping.
ALTER TABLE transaction ADD fromTfID int(11) NOT NULL AFTER tfID;

-- Ditto to transactionRepeat table
ALTER TABLE transactionRepeat ADD fromTfID int(11) NOT NULL AFTER tfID;

-- New config value to specify a default source Wages TF and source Invoices TF
INSERT INTO config (name,value,type) VALUES ("wagesTfID","","text");
INSERT INTO config (name,value,type) VALUES ("invoicesTfID","","text");

