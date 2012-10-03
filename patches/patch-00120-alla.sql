-- Drop productSaleTransaction table
DROP TABLE productSaleTransaction;

-- Change transaction.transactionType from product to sale
ALTER TABLE transaction CHANGE transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','tax','sale') NOT NULL;

-- Update old transaction.transactionType records
UPDATE transaction SET transactionType = 'sale' WHERE transactionType = 'product';

-- Add fromTfID to productCost
ALTER TABLE productCost ADD fromTfID int(11) NOT NULL AFTER productID;

-- Change productCost.tfID into NOT NULL
ALTER TABLE productCost CHANGE tfID tfID int(11) NOT NULL;

-- Make productCost.isPercentage NOT NULL
ALTER TABLE productCost CHANGE isPercentage isPercentage tinyint(1) NOT NULL;

-- Add new fields to track whether prices are tax inclusive
ALTER TABLE product ADD buyCostIncTax tinyint(1) NOT NULL AFTER buyCost;
ALTER TABLE product ADD sellPriceIncTax tinyint(1) NOT NULL AFTER sellPrice;

-- Allow productSale.projectID to be null
ALTER TABLE productSale CHANGE projectID projectID int(11) DEFAULT NULL;

-- Add productSale.clientID
ALTER TABLE productSale ADD clientID int(11) DEFAULT NULL AFTER productSaleID;

-- Fix up productSale.status
ALTER TABLE productSale CHANGE status status enum('edit','allocate','admin','finished') NOT NULL;

-- Change the transaction.productSaleItemID to productSaleID and add indexes to transaction table
ALTER TABLE transaction CHANGE productSaleItemID productSaleID int(11) DEFAULT NULL;
ALTER TABLE transaction ADD INDEX idx_productSaleID (productSaleID);

-- Okay turns out we need a productSaleItemID as well on the transaction table.
ALTER TABLE transaction ADD productSaleItemID int(11) DEFAULT NULL AFTER productSaleID;
ALTER TABLE transaction ADD INDEX idx_productSaleItemID (productSaleItemID);

-- Add new fields to track whether productSaleItem prices are tax inclusive
ALTER TABLE productSaleItem ADD buyCostIncTax tinyint(1) NOT NULL AFTER buyCost;
ALTER TABLE productSaleItem ADD sellPriceIncTax tinyint(1) NOT NULL AFTER sellPrice;

-- Replace permissions for product
DELETE FROM permission WHERE tableName LIKE 'product%';

INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `actions`, `comment`)
VALUES
 ('product'                  ,0  ,0 ,''         ,'Y' ,0    ,1     ,NULL)
,('product'                  ,0  ,0 ,'manage'   ,'Y' ,100  ,15    ,NULL)
,('product'                  ,0  ,0 ,'admin'    ,'Y' ,100  ,15    ,NULL)

,('productCost'              ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productCost'              ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productCost'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('productSale'              ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSale'              ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSale'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('productSaleItem'          ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSaleItem'          ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSaleItem'          ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)
,('tf'                       ,0  ,0 ,'manage'   ,'Y' ,NULL ,1     ,NULL)
,('transaction'              ,0  ,0 ,'manage'   ,'Y' ,NULL ,8192  ,"Manager create pending transaction.")
;




