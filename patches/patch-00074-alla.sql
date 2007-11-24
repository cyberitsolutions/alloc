-- Change expenseForm.enteredBy to expenseFormCreatedUser
ALTER TABLE expenseForm CHANGE enteredBy expenseFormCreatedUser int(11) DEFAULT NULL;

-- Add expenseFormCreatedTime
ALTER TABLE expenseForm ADD expenseFormCreatedTime datetime DEFAULT NULL AFTER expenseFormCreatedUser;

-- Update empty fields
UPDATE expenseForm set expenseFormCreatedUser = expenseFormModifiedUser WHERE expenseFormCreatedUser IS NULL or expenseFormCreatedUser = 0;
UPDATE expenseForm set expenseFormCreatedTime = expenseFormModifiedTime WHERE expenseFormCreatedTime IS NULL;

-- Same for transaction
UPDATE transaction set transactionCreatedUser = transactionModifiedUser WHERE transactionCreatedUser IS NULL or transactionCreatedUser = 0;
UPDATE transaction SET transactionCreatedTime = IF(transactionDate,transactionDate,transactionModifiedTime) WHERE transactionCreatedTime IS NULL;



