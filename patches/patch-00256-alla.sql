insert into permission (tableName,entityID,roleName,actions,comment) values
                       ("inbox",0,"manage",1+2+4+8,"Manager can change inbox emails.");
insert into permission (tableName,entityID,roleName,actions,comment) values
                       ("inbox",0,"admin",1+2+4+8,"Admin can change inbox emails.");
insert into permission (tableName,entityID,roleName,actions,comment) values
                       ("inbox",0,"god",1+2+4+8,"Super-user can change inbox emails.");
