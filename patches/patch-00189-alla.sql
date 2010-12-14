
-- Add new table for tracking the exchange rate of currencies
CREATE TABLE exchangeRate (
  exchangeRateID integer NOT NULL auto_increment PRIMARY KEY,
  exchangeRateCreatedDate date NOT NULL,
  exchangeRateCreatedTime datetime NOT NULL,
  fromCurrency varchar(3) NOT NULL,
  toCurrency   varchar(3) NOT NULL,
  exchangeRate DECIMAL(14,5) NOT NULL DEFAULT 0
) ENGINE=InnoDB PACK_KEYS=0;

-- Add new fields to transaction to permit tracking an accurate exchange rate
ALTER TABLE transaction ADD destCurrencyTypeID varchar(255) DEFAULT NULL AFTER currencyTypeID;
ALTER TABLE transaction ADD exchangeRate DECIMAL (14,5) NOT NULL DEFAULT 1 AFTER destCurrencyTypeID;

-- Update existing transactions
UPDATE transaction,config SET transaction.destCurrencyTypeID = config.value WHERE config.name = 'currency';

-- Add unique constraint to exchangeRate
CREATE UNIQUE INDEX date_currency ON exchangeRate (exchangeRateCreatedDate,fromCurrency,toCurrency);

-- Add new field to track when a transaction is approved
ALTER TABLE transaction ADD dateApproved DATE DEFAULT NULL AFTER status;

-- Update field for old data's sake
UPDATE transaction SET dateApproved = transactionDate WHERE dateApproved IS NULL AND status = 'approved';
