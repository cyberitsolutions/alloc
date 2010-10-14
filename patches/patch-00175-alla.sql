-- Add couple of indexes to speed up the comment summary query
CREATE INDEX dateTimeSheetItem ON timeSheetItem (dateTimeSheetItem);
CREATE INDEX commentCreatedTime ON comment (commentCreatedTime);
