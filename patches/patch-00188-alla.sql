
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-- WARNING
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-- WARNING
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-- DON'T APPLY THIS PATCH UNTIL YOU HAVE LOGGED IN AND GONE TO SETUP ->
-- FINANCE AND SELECTED A DEFAULT CURRENCY AND CLICKED THE BUTTON TO UPDATE
-- YOUR TRANSACTIONS THAT HAVE NO CURRENCY
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


-- The following fields will be converted from decimals like 129.95 to whole
-- numbers like 12995. The functionality relies on your currency being set
-- correctly so it knows how to change that number back into 129.95.
-- 

-- transaction.amount 
UPDATE transaction,currencyType SET transaction.amount = (transaction.amount * POW(10, currencyType.numberToBasic))
WHERE transaction.currencyTypeID = currencyType.currencyTypeID;

ALTER table transaction CHANGE amount amount BIGINT NOT NULL DEFAULT 0;


-- transactionRepeat.amount
UPDATE transactionRepeat,currencyType SET transactionRepeat.amount = (transactionRepeat.amount * POW(10, currencyType.numberToBasic))
WHERE transactionRepeat.currencyTypeID = currencyType.currencyTypeID;

ALTER table transactionRepeat CHANGE amount amount BIGINT NOT NULL DEFAULT 0;


-- productCost.amount
UPDATE productCost,currencyType SET productCost.amount = (productCost.amount * POW(10, currencyType.numberToBasic))
WHERE productCost.currencyTypeID = currencyType.currencyTypeID;

ALTER table productCost CHANGE amount amount BIGINT NOT NULL DEFAULT 0;


-- project.customerBilledDollars
-- project.projectBudget
UPDATE project,currencyType 
  SET project.customerBilledDollars = (project.customerBilledDollars * POW(10, currencyType.numberToBasic)),
      project.projectBudget = (project.projectBudget * POW(10, currencyType.numberToBasic)) 
WHERE project.currencyTypeID = currencyType.currencyTypeID;

ALTER table project CHANGE customerBilledDollars customerBilledDollars BIGINT DEFAULT NULL;
ALTER table project CHANGE projectBudget projectBudget BIGINT DEFAULT NULL;


-- timeSheet.customerBilledDollars
UPDATE timeSheet,currencyType SET timeSheet.customerBilledDollars = (timeSheet.customerBilledDollars * POW(10, currencyType.numberToBasic))
WHERE timeSheet.currencyTypeID = currencyType.currencyTypeID;

ALTER table timeSheet CHANGE customerBilledDollars customerBilledDollars BIGINT DEFAULT NULL;


-- timeSheetItem.rate
UPDATE timeSheetItem,currencyType,timeSheet SET timeSheetItem.rate = (timeSheetItem.rate * POW(10, currencyType.numberToBasic))
WHERE timeSheetItem.timeSheetID = timeSheet.timeSheetID AND timeSheet.currencyTypeID = currencyType.currencyTypeID;

ALTER table timeSheetItem CHANGE rate rate BIGINT DEFAULT 0;


-- projectPerson.rate
UPDATE projectPerson,currencyType,project SET projectPerson.rate = (projectPerson.rate * POW(10, currencyType.numberToBasic))
WHERE projectPerson.projectID = project.projectID AND project.currencyTypeID = currencyType.currencyTypeID;

ALTER table projectPerson CHANGE rate rate BIGINT DEFAULT 0;


-- invoice.maxAmount
UPDATE invoice,currencyType SET invoice.maxAmount = (invoice.maxAmount * POW(10, currencyType.numberToBasic))
WHERE invoice.currencyTypeID = currencyType.currencyTypeID;

ALTER table invoice CHANGE maxAmount maxAmount BIGINT DEFAULT 0;


-- invoiceItem.iiUnitPrice
-- invoiceItem.iiAmount
UPDATE invoiceItem,currencyType,invoice
SET invoiceItem.iiUnitPrice = (invoiceItem.iiUnitPrice * POW(10, currencyType.numberToBasic)),
    invoiceItem.iiAmount = (invoiceItem.iiAmount * POW(10, currencyType.numberToBasic))
WHERE invoiceItem.invoiceID = invoice.invoiceID AND invoice.currencyTypeID = currencyType.currencyTypeID;

ALTER table invoiceItem CHANGE iiUnitPrice iiUnitPrice BIGINT DEFAULT NULL;
ALTER table invoiceItem CHANGE iiAmount iiAmount BIGINT DEFAULT NULL;


-- product.buyCost
UPDATE product,currencyType 
SET product.buyCost = (product.buyCost * POW(10, currencyType.numberToBasic))
WHERE buyCostCurrencyTypeID = currencyType.currencyTypeID;

ALTER table product CHANGE buyCost buyCost BIGINT DEFAULT 0;

-- product.sellPrice
UPDATE product,currencyType 
SET product.sellPrice = (product.sellPrice * POW(10, currencyType.numberToBasic))
WHERE sellPriceCurrencyTypeID = currencyType.currencyTypeID;

ALTER table product CHANGE sellPrice sellPrice BIGINT DEFAULT 0;



-- productSaleItem.buyCost
UPDATE productSaleItem,currencyType 
SET productSaleItem.buyCost = (productSaleItem.buyCost * POW(10, currencyType.numberToBasic))
WHERE buyCostCurrencyTypeID = currencyType.currencyTypeID;

ALTER table productSaleItem CHANGE buyCost buyCost BIGINT DEFAULT 0;

-- productSaleItem.sellPrice
UPDATE productSaleItem,currencyType 
SET productSaleItem.sellPrice = (productSaleItem.sellPrice * POW(10, currencyType.numberToBasic))
WHERE sellPriceCurrencyTypeID = currencyType.currencyTypeID;

ALTER table productSaleItem CHANGE sellPrice sellPrice BIGINT DEFAULT 0;


-- Also need to update product.buyCostCurrencyTypeID, in this file, as we can only be sure a currency is set, now.
UPDATE product,config SET product.buyCostCurrencyTypeID = config.value where config.name = 'currency';




