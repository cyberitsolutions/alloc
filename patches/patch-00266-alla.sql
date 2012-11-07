
-- add new field invoice.tfID
ALTER TABLE invoice ADD tfID integer NOT NULL AFTER projectID;
UPDATE invoice SET tfID = (SELECT value FROM config WHERE name = 'mainTfID');
ALTER TABLE invoice ADD CONSTRAINT invoice_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);

