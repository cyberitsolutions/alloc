
-- Add currencyTypeID fields to product
ALTER TABLE product ADD buyCostCurrencyTypeID varchar(255) DEFAULT NULL AFTER buyCost;
ALTER TABLE product ADD sellPriceCurrencyTypeID varchar(255) DEFAULT NULL AFTER sellPrice;

-- and productSaleItem
ALTER TABLE productSaleItem ADD buyCostCurrencyTypeID varchar(255) DEFAULT NULL AFTER buyCost;
ALTER TABLE productSaleItem ADD sellPriceCurrencyTypeID varchar(255) DEFAULT NULL AFTER sellPrice;

