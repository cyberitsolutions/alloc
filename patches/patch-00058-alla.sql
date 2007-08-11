-- increase the size of the timeSheetItemDuration field
ALTER TABLE timeSheetItem CHANGE timeSheetItemDuration timeSheetItemDuration DECIMAL(9,2) default '0.00';
