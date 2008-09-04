-- Update the names of the tf's: wagesTfID->outTfID, invoicesTfID->inTfID, cybersourceTfID->mainTfID
UPDATE config SET name = "mainTfID" WHERE name = "cybersourceTfID";
UPDATE config SET name = "outTfID" WHERE name = "wagesTfID";
UPDATE config SET name = "inTfID" WHERE name = "invoicesTfID";

