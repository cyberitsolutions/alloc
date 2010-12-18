
-- Add productSale.personID
ALTER TABLE productSale ADD personID INTEGER DEFAULT NULL AFTER projectID;

-- Add constraint
ALTER TABLE productSale ADD CONSTRAINT productSale_personID FOREIGN KEY (personID) REFERENCES person (personID);
