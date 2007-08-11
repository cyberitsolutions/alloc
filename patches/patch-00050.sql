-- add perm for token
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `comment`, `actions`) VALUES ('token',0,0,'','Y',NULL,'Allow everyone to do anything with tokens.',15);

