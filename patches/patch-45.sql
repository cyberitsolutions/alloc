-- allow everyone to read taskCommentTemplates
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `comment`, `actions`) 
VALUES ('taskCommentTemplate',0,0,'','Y',NULL,'Allow everyone to read taskCommentTemplates',1);

