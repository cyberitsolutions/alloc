-- Make invoice.invoiceNum into a unique key
ALTER TABLE invoice ADD UNIQUE KEY (invoiceNum);

-- Add status to invoice table
ALTER TABLE invoice ADD invoiceStatus enum('edit','generate','reconcile','finished') NOT NULL DEFAULT 'edit';

-- Add a clientID to invoice table
ALTER TABLE invoice ADD clientID int(11) NOT NULL AFTER invoiceID;

-- Fix up date fields on invoice table
ALTER TABLE invoice CHANGE invoiceDate invoiceDateFrom date;
ALTER TABLE invoice ADD invoiceDateTo date AFTER invoiceDateFrom;

-- Nuke timeSheet.invoiceItemID
ALTER TABLE timeSheet DROP invoiceItemID;

-- Nuke timeSheet.invoiceNum
ALTER TABLE timeSheet DROP invoiceNum;

-- Add an indexes to the transaction table
ALTER TABLE transaction ADD INDEX idx_tfID (tfID);
ALTER TABLE transaction ADD INDEX idx_invoiceItemID (invoiceItemID);

-- Fix status field on transaction table
ALTER TABLE transaction CHANGE status status enum('pending','rejected','approved') NOT NULL DEFAULT 'pending';

-- Add invoiceID to transaction
ALTER TABLE transaction ADD invoiceID int(11) DEFAULT NULL AFTER transactionDate;

-- Add an indexes to the tfPerson table
ALTER TABLE tfPerson ADD INDEX idx_tfID (tfID);

