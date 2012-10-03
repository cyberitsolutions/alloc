-- Alter transactionType values in transactionRepeat table
alter table transactionRepeat change transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment') NOT NULL default 'invoice';
alter table transaction change transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment') NOT NULL default 'invoice';
