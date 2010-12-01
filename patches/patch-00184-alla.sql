
-- Have to temporarily drop the constraint
ALTER TABLE project DROP FOREIGN KEY project_currencyType;

-- Rename project.currencyType to currencyTypeID
ALTER TABLE project CHANGE currencyType currencyTypeID VARCHAR(255) DEFAULT NULL;

-- Readd the constraint
ALTER TABLE project ADD CONSTRAINT project_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
