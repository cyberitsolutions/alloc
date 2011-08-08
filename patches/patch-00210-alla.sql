-- Simplified permissions for transactions and comments
DELETE FROM permission WHERE tableName = 'transaction';
DELETE FROM permission WHERE tableName = 'comment';
DELETE FROM permission WHERE tableName = 'indexQueue';

INSERT INTO permission
 (tableName, entityID, personID, roleName, allow, sortKey, actions, comment)
 VALUES 
 ('transaction'              ,-1 ,NULL ,''         ,true ,NULL ,15    ,'Allow everyone to modify PENDING transactions that they own.')
,('transaction'              ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,'Allow admin to do everything with transactions.')
,('comment'                  ,0  ,NULL ,'employee' ,true ,NULL ,15    ,NULL)
,('comment'                  ,0  ,NULL ,'client'   ,true ,NULL ,15    ,NULL)
,('indexQueue'               ,0  ,NULL ,'employee' ,true ,NULL ,15    ,NULL);




