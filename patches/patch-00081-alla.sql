
-- Change two fields on transaction so that they default to NULL instead of zero
ALTER TABLE transaction MODIFY expenseFormID int(11) DEFAULT NULL;
ALTER TABLE transaction MODIFY projectID int(11) DEFAULT NULL;

UPDATE transaction SET expenseFormID = NULL WHERE expenseFormID = 0;
UPDATE transaction SET projectID = NULL WHERE projectID = 0;
