
-- Add currency to timeSheet table
ALTER TABLE timeSheet ADD currencyTypeID VARCHAR(255) DEFAULT NULL AFTER customerBilledDollars; 

-- Add a constraint from timeSheet.currencyTypeID to currencyType.currencyTypeID
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);

-- Update the timeSheet.currencyTypeID field from its related project
UPDATE timeSheet,project SET timeSheet.currencyTypeID = project.currencyType WHERE timeSheet.projectID = project.projectID;
