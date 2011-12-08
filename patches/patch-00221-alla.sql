-- Allow anyone to create sales that they own
UPDATE permission SET roleName = '',actions=15 WHERE tableName = 'productSale' AND entityID = '-1';
UPDATE permission SET roleName = '',actions=15 WHERE tableName = 'productSaleItem' AND entityID = '-1';
