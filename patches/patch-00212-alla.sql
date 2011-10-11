-- Add a field that links transactions to productCosts.
ALTER TABLE transaction ADD productCostID INTEGER DEFAULT NULL AFTER productSaleItemID;
CREATE INDEX idx_productCostID ON transaction (productCostID);
ALTER TABLE transaction ADD CONSTRAINT transaction_productCostID FOREIGN KEY (productCostID) REFERENCES productCost (productCostID);
