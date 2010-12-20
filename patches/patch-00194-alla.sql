
-- New field productSale.productSaleDate
ALTER TABLE productSale ADD productSaleDate date default NULL AFTER productSaleModifiedUser;

-- Copy date created into the new date field. 
UPDATE productSale set productSaleDate = DATE(productSaleCreatedTime);
