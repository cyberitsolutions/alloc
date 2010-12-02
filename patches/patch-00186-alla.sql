-- Add new numberToBasic field to currencyType
ALTER TABLE currencyType ADD numberToBasic integer DEFAULT 0 AFTER currencyTypeName;

-- Set the currency precision
UPDATE currencyType SET numberToBasic = 2;
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'BHD';
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'IQD';
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'KWD';
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'LYD';
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'OMR';
UPDATE currencyType SET numberToBasic = 3 WHERE currencyTypeID = 'TND';
UPDATE currencyType SET numberToBasic = 1 WHERE currencyTypeID = 'VND';
