
-- create the database table for storing task history
CREATE TABLE `auditItem`
  (
    `auditItemID` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `entityName` VARCHAR(255),
    `entityID` INT UNSIGNED NOT NULL,
    `personID` INT UNSIGNED NOT NULL,
    `dateChanged` DATETIME NOT NULL,
    `changeType` ENUM('FieldChange', 'TaskMarkedDuplicate', 'TaskUnmarkedDuplicate', 'TaskClosed', 'TaskReopened') NOT NULL default 'FieldChange',
    `fieldName` VARCHAR(255) NULL default NULL,
    `oldValue` TEXT NULL default NULL,

    INDEX `idx_entityName` (entityName),
    INDEX `idx_entityID` (entityID)
  );

-- add permissions for auditItem
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `actions`, `comment`) VALUES ("auditItem", 0 , 0, "employee", "Y", NULL, 8 + 1, "Allow everyone to create and read audit items.");

-- fix up any problems with dodgy parentTaskIDs
UPDATE task SET parentTaskID = NULL WHERE parentTaskID = 0;

