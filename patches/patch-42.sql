-- Add clientID to expenseForm table
ALTER TABLE expenseForm ADD clientID int(11) DEFAULT 0 AFTER expenseFormID;
