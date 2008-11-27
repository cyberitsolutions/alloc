
-- add special case meta-people to the reminders table
ALTER TABLE `reminder` MODIFY `personID` int(11) NULL default NULL;
ALTER TABLE `reminder` ADD `metaPerson` int(11) NULL default NULL;

