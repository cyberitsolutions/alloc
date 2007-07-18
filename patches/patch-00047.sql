-- Add an index to the timeSheetID on the transaction table (changed a 2.70sec query to a 0.01sec)
ALTER TABLE transaction ADD INDEX idx_timeSheetID (timeSheetID);

