-- New field to allow deletion (actually deactivation) of product costs, regardless of referential integrity constraints
ALTER TABLE productCost ADD productCostActive boolean NOT NULL DEFAULT true;
