
ALTER TABLE invoiceItem ADD productSaleID INTEGER DEFAULT NULL AFTER transactionID;
ALTER TABLE invoiceItem ADD productSaleItemID INTEGER DEFAULT NULL AFTER productSaleID;
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_productSaleID FOREIGN KEY (productSaleID) REFERENCES productSale (productSaleID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_productSaleItemID FOREIGN KEY (productSaleItemID) REFERENCES productSaleItem (productSaleItemID);


