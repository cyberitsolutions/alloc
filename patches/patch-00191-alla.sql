-- These buyCost fields are not needed
ALTER TABLE product DROP buyCost;
ALTER TABLE product DROP buyCostCurrencyTypeID;
ALTER TABLE product DROP buyCostIncTax;
ALTER TABLE productSaleItem DROP buyCost;
ALTER TABLE productSaleItem DROP buyCostCurrencyTypeID;
ALTER TABLE productSaleItem DROP buyCostIncTax;
