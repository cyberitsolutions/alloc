-- New field to allow sales to have a specfic tf
ALTER TABLE productSale ADD tfID INTEGER NOT NULL AFTER personID;
UPDATE productSale SET tfID = (SELECT value FROM config WHERE name = 'mainTfID');
ALTER TABLE productSale ADD CONSTRAINT productSale_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);

