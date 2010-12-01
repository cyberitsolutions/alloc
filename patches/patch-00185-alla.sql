
-- Add currency to invoice table
ALTER TABLE invoice ADD currencyTypeID varchar(255) default NULL AFTER invoiceStatus;

-- Add a constraint from invoice.currencyTypeID to currencyType.currencyTypeID
ALTER TABLE invoice ADD CONSTRAINT invoice_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);

-- Update the invoice.currencyTypeID field from its related project
UPDATE invoice,project SET invoice.currencyTypeID = project.currencyTypeID WHERE invoice.projectID = project.projectID;
