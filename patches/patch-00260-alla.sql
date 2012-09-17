
-- give the product sale item quantity field, a precision to two places
ALTER TABLE productSaleItem CHANGE quantity quantity DECIMAL(19,2) NOT NULL DEFAULT 1;

