-- Created by fields for timeSheetItem
ALTER TABLE timeSheetItem ADD timeSheetItemCreatedTime datetime default NULL;
ALTER TABLE timeSheetItem ADD timeSheetItemCreatedUser integer default NULL AFTER timeSheetItemCreatedTime;
ALTER TABLE timeSheetItem ADD timeSheetItemModifiedTime datetime DEFAULT NULL AFTER timeSheetItemCreatedUser;
ALTER TABLE timeSheetItem ADD timeSheetItemModifiedUser integer DEFAULT NULL AFTER timeSheetItemModifiedTime;

-- Update fields
UPDATE timeSheetItem SET timeSheetItemCreatedTime = dateTimeSheetItem;
UPDATE timeSheetItem SET timeSheetItemCreatedUser = personID;
