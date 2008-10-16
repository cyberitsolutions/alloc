-- add invoice.maxAmount for pre-paid invoices
ALTER TABLE invoice ADD maxAmount decimal(19,2) DEFAULT 0;

-- add invoice.projectID
ALTER TABLE invoice ADD projectID int(11) DEFAULT NULL AFTER clientID;

-- add prepaid to list of projectTypes
ALTER TABLE project CHANGE projectType projectType enum('contract','job','project','prepaid') DEFAULT NULL;
