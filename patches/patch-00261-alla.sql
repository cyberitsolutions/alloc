
-- permit managers to be able to read/write employee management fields.
INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
VALUES ('person'                   ,0  ,'manage'   ,NULL ,1+2+4+8+256+512+1024       ,NULL);
