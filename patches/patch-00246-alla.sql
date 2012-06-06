
-- sort out some permissions
insert into permission (tableName,entityID,roleName,actions) values ("permission",0,"god",15);
insert into permission (tableName,entityID,roleName,actions) values ("commentTemplate",0,"manage",15);

DELETE FROM permission where tableName = 'config';
insert into permission (tableName,entityID,roleName,actions) values ("config",0,"god",15);
