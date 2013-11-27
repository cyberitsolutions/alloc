
-- create invoiceEntity table to track relations ships between time sheets and invoices
DROP TABLE IF EXISTS invoiceEntity;
CREATE TABLE invoiceEntity (
  invoiceEntityID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceID integer NOT NULL,
  timeSheetID integer DEFAULT NULL,
  expenseFormID integer DEFAULT NULL,
  productSaleID integer DEFAULT NULL,
  useItems BOOLEAN DEFAULT false
) ENGINE=InnoDB PACK_KEYS=0;

ALTER TABLE invoiceEntity ADD CONSTRAINT invoiceEntity_invoiceID FOREIGN KEY (invoiceID) REFERENCES invoice (invoiceID);
ALTER TABLE invoiceEntity ADD CONSTRAINT invoiceEntity_timeSheetID FOREIGN KEY (timeSheetID) REFERENCES timeSheet (timeSheetID);
ALTER TABLE invoiceEntity ADD CONSTRAINT invoiceEntity_expenseFormID FOREIGN KEY (expenseFormID) REFERENCES expenseForm (expenseFormID);
ALTER TABLE invoiceEntity ADD CONSTRAINT invoiceEntity_productSaleID FOREIGN KEY (productSaleID) REFERENCES productSale (productSaleID);

INSERT INTO invoiceEntity (invoiceID,timeSheetID)
SELECT invoiceID,timeSheetID FROM invoiceItem WHERE timeSheetID IS NOT NULL AND timeSheetItemID IS NULL GROUP BY timeSheetID;

INSERT INTO invoiceEntity (invoiceID,timeSheetID,useItems)
SELECT invoiceID,timeSheetID,true FROM invoiceItem WHERE timeSheetID IS NOT NULL AND timeSheetItemID IS NOT NULL GROUP BY timeSheetID;

INSERT INTO invoiceEntity (invoiceID,expenseFormID)
SELECT invoiceID,expenseFormID FROM invoiceItem WHERE expenseFormID IS NOT NULL AND transactionID IS NULL GROUP BY expenseFormID;

INSERT INTO invoiceEntity (invoiceID,expenseFormID,useItems)
SELECT invoiceID,expenseFormID,true FROM invoiceItem WHERE expenseFormID IS NOT NULL AND transactionID IS NOT NULL GROUP BY expenseFormID;

INSERT INTO invoiceEntity (invoiceID,productSaleID)
SELECT invoiceID,productSaleID FROM invoiceItem WHERE productSaleID IS NOT NULL AND productSaleItemID IS NULL GROUP BY productSaleID;

INSERT INTO invoiceEntity (invoiceID,productSaleID,useItems)
SELECT invoiceID,productSaleID,true FROM invoiceItem WHERE productSaleID IS NOT NULL AND productSaleItemID IS NOT NULL GROUP BY productSaleID;

INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
VALUES ('invoiceEntity',0 ,'',NULL ,1+2+4+8,'Anyone can create a relationship for invoiceEntity.');
