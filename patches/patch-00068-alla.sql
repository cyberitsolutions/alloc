-- Change all the dollar amount fields to DECIMAL(19,2)
alter table transaction CHANGE amount amount DECIMAL(19,2) NOT NULL DEFAULT 0;
alter table transactionRepeat CHANGE amount amount DECIMAL(19,2) NOT NULL DEFAULT 0;

alter table invoiceItem CHANGE iiQuantity iiQuantity DECIMAL(19,2) DEFAULT NULL;
alter table invoiceItem CHANGE iiAmount iiAmount DECIMAL(19,2) DEFAULT NULL;
alter table invoiceItem CHANGE iiUnitPrice iiUnitPrice DECIMAL(19,2) DEFAULT NULL;

alter table project CHANGE projectBudget projectBudget DECIMAL(19,2) DEFAULT NULL;
alter table project CHANGE customerBilledDollars customerBilledDollars DECIMAL(19,2) DEFAULT NULL;

alter table projectPerson CHANGE rate rate DECIMAL(19,2) DEFAULT '0.00';
alter table timeSheetItem CHANGE rate rate DECIMAL(19,2) DEFAULT '0.00';
