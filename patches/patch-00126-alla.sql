
-- change timeSheet.customerBilledDollars to DEFAULT NULL
ALTER TABLE timeSheet CHANGE customerBilledDollars customerBilledDollars DECIMAL(19,2) DEFAULT NULL;
