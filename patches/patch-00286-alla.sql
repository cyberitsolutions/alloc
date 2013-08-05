-- permit god user account to edit the projectType table
INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
  VALUES ('projectType'              ,0  ,'god'      ,NULL ,1+2+4+8    ,NULL);

