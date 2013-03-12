-- add fields to project to track creation and modification

ALTER TABLE project ADD projectCreatedTime datetime default NULL AFTER clientContactID;
ALTER TABLE project ADD projectCreatedUser integer default NULL AFTER projectCreatedTime;
ALTER TABLE project ADD projectModifiedTime datetime DEFAULT NULL AFTER projectCreatedUser;
