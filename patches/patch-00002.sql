-- Add repeat to transactionTypes
alter table transaction change transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','repeat') default 'invoice';
