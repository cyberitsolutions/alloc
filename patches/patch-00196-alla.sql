-- Make the currency fields NOT NULL
ALTER TABLE invoice CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE project CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE timeSheet CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE transaction CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE transaction CHANGE destCurrencyTypeID destCurrencyTypeID varchar(3) NOT NULL;
ALTER TABLE transactionRepeat CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE product CHANGE sellPriceCurrencyTypeID sellPriceCurrencyTypeID varchar(3) NOT NULL;
ALTER TABLE productCost CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
ALTER TABLE productSaleItem CHANGE sellPriceCurrencyTypeID sellPriceCurrencyTypeID varchar(3) NOT NULL;
ALTER TABLE currencyType CHANGE currencyTypeID currencyTypeID varchar(3) NOT NULL;
