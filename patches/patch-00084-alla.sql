-- Add tax type to transaction
ALTER TABLE transaction MODIFY transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','tax') NOT NULL; 

