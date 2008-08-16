-- This should fix weird windows installation bug
ALTER TABLE expenseForm CHANGE expenseFormComment expenseFormComment text DEFAULT NULL;
