-- Add index to timeSheetItem table for timeSheetID
ALTER TABLE timeSheetItem ADD INDEX idx_timeSheetID (timeSheetID);
