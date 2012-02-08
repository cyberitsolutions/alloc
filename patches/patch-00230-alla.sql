-- External reference fields for productSale.
ALTER TABLE productSale ADD extRef VARCHAR(255) DEFAULT NULL;
ALTER TABLE productSale ADD extRefDate date DEFAULT NULL;

