-- whack an emailUID field onto timeSheetItem to allow tracking of which items by which email
ALTER TABLE timeSheetItem ADD emailUID varchar(255) DEFAULT NULL;
