
DELETE FROM permission WHERE tableName = 'interestedParty';

INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, actions, comment)
VALUES
 ('interestedParty',0,NULL,'',true,NULL,9,NULL)
,('interestedParty',0,NULL,'manage',true,NULL,15,NULL)
,('interestedParty',0,NULL,'admin',true,NULL,15,NULL)
,('interestedParty',-1,NULL,'',true,NULL,15,NULL);
