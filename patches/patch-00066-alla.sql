


-- nuke invoiceItem.status
ALTER TABLE invoiceItem DROP status;

-- Make iiMemo field bigger
ALTER TABLE invoiceItem CHANGE iiMemo iiMemo text;

-- Add a IDs to invoiceItem table
ALTER TABLE invoiceItem ADD timeSheetID int(11) DEFAULT 0 AFTER invoiceID;
ALTER TABLE invoiceItem ADD timeSheetItemID int(11) DEFAULT 0 AFTER timeSheetID;
ALTER TABLE invoiceItem ADD expenseFormID int(11) DEFAULT 0 AFTER timeSheetItemID;
ALTER TABLE invoiceItem ADD transactionID int(11) DEFAULT 0 AFTER expenseFormID;

-- Add iiDate to invoiceItem
ALTER TABLE invoiceItem ADD iiDate date DEFAULT NULL AFTER iiAmount;

-- Add an index to the invoiceID on the invoiceItem table
ALTER TABLE invoiceItem ADD INDEX idx_invoiceID (invoiceID);



