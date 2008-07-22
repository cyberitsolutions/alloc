
-- create the savedView table for saved list filters
CREATE TABLE `savedView` (
  `savedViewID` int(10) unsigned NOT NULL auto_increment,
  `personID` int(10) unsigned NOT NULL,
  `formName` varchar(32) NOT NULL,
  `viewName` varchar(255) NOT NULL,
  `formView` text,
  PRIMARY KEY  (`savedViewID`)
) TYPE=MyISAM PACK_KEYS=0;


-- add permissions to allow all users to save list filters
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `comment`, `actions`) VALUES ('savedView', 0, 0, '', 'Y', 100, 'Allow people to view, save, edit and delete list filters.', 15);
