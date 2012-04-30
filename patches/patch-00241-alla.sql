
-- straighten out some perms
UPDATE permission SET roleName = '' WHERE tableName = 'auditItem';
UPDATE permission SET roleName = '' WHERE tableName = 'indexQueue';
