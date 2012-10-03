-- Remove old payment_insurance field
ALTER TABLE timeSheet DROP payment_insurance;

-- deactivate old transaction type of insurance.
UPDATE transactionType SET transactionTypeActive = 0 WHERE transactionTypeID = 'insurance';
