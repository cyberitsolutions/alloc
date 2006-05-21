-- Alter transactionType values in transactionRepeat table
alter table transactionRepeat change transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance') default 'invoice';
alter table transaction change transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance') default 'invoice';
