
-- Fix up some data that went bad
UPDATE transactionRepeat set paymentBasis = 'weekly' where paymentBasis = 0;
UPDATE transactionRepeat set paymentBasis = 'fortnightly' where paymentBasis = 1;
UPDATE transactionRepeat set paymentBasis = 'monthly' where paymentBasis = 2;
UPDATE transactionRepeat set paymentBasis = 'quarterly' where paymentBasis = 3;
UPDATE transactionRepeat set paymentBasis = 'yearly' where paymentBasis = 4;
