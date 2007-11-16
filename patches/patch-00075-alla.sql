-- Remove generate type invoice
ALTER TABLE invoice CHANGE invoiceStatus invoiceStatus enum('edit','reconcile','finished') NOT NULL DEFAULT 'edit';

-- New permission to allow users to update related invoice item records.
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoiceItem',-1,0,'','Y',NULL,'This allows time sheet users to update the related invoice item record.',11);

-- New permission to allow users to update related invoice records.
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoice',-1,0,'','Y',NULL,'User needs to be able to update invoice because updating an invoiceItem, changes the dates on the invoice itself.',3);






