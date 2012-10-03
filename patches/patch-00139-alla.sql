
-- Change lots of int(n) to boolean to make it more in line with postgres 
ALTER TABLE expenseForm CHANGE reimbursementRequired reimbursementRequired boolean NOT NULL DEFAULT false;
ALTER TABLE expenseForm CHANGE expenseFormFinalised expenseFormFinalised boolean NOT NULL default false;
ALTER TABLE expenseForm CHANGE seekClientReimbursement seekClientReimbursement boolean NOT NULL default false;
ALTER TABLE permission CHANGE allow allow boolean default true;
ALTER TABLE person CHANGE personActive personActive boolean default true;
ALTER TABLE reminder CHANGE reminderAdvNoticeSent reminderAdvNoticeSent boolean NOT NULL default false;
ALTER TABLE interestedParty CHANGE external external boolean DEFAULT NULL;
ALTER TABLE taskType CHANGE taskTypeActive taskTypeActive boolean default true;
ALTER TABLE tf CHANGE tfActive tfActive boolean NOT NULL DEFAULT true;
ALTER TABLE timeSheetItem CHANGE commentPrivate commentPrivate boolean default false;
ALTER TABLE timeUnit CHANGE timeUnitActive timeUnitActive boolean default false;
ALTER TABLE token CHANGE tokenActive tokenActive boolean DEFAULT false;
ALTER TABLE transactionRepeat CHANGE reimbursementRequired reimbursementRequired boolean NOT NULL default false;
ALTER TABLE product CHANGE buyCostIncTax buyCostIncTax boolean NOT NULL default false;
ALTER TABLE product CHANGE sellPriceIncTax sellPriceIncTax boolean NOT NULL default false;
ALTER TABLE productCost CHANGE isPercentage isPercentage boolean NOT NULL default false;
ALTER TABLE productSaleItem CHANGE buyCostIncTax buyCostIncTax boolean NOT NULL default false;
ALTER TABLE productSaleItem CHANGE sellPriceIncTax sellPriceIncTax boolean NOT NULL default false;

-- these take ages on a big sentEmailLog table
ALTER TABLE sentEmailLog ENGINE = InnoDB;
ALTER TABLE sentEmailLog CHANGE sentEmailLogID sentEmailLogID integer NOT NULL auto_increment;
ALTER TABLE sentEmailLog CHANGE sentEmailLogCreatedUser sentEmailLogCreatedUser integer default NULL;
ALTER TABLE sentEmailLog CHANGE sentEmailType sentEmailType varchar(255) DEFAULT NULL;

-- Fix up some stuff on the task table
ALTER TABLE task CHANGE taskStatus taskStatus varchar(255) NOT NULL;
ALTER TABLE task DROP INDEX dateAdded;
ALTER TABLE task DROP INDEX parentTaskID_2;
ALTER TABLE task ADD KEY dateCreated (dateCreated);
ALTER TABLE auditItem CHANGE dateChanged dateChanged datetime NOT NULL;
ALTER TABLE interestedParty CHANGE entity entity varchar(255) NOT NULL;

-- Fix up productCost table
ALTER TABLE productCost CHANGE tfID tfID integer DEFAULT NULL;
ALTER TABLE productCost CHANGE fromTfID fromTfID integer DEFAULT NULL;


