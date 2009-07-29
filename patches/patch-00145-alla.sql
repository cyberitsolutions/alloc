-- Fix up the timeSheetItem.multiplier field
ALTER TABLE timeSheetItem CHANGE multiplier multiplier decimal(9,2) default 1.00 NOT NULL;

-- new table for the referential integrity
CREATE TABLE timeSheetItemMultiplier (
  timeSheetItemMultiplierID decimal(9,2) PRIMARY KEY,
  timeSheetItemMultiplierName varchar(255),
  timeSheetItemMultiplierSeq integer NOT NULL,
  timeSheetItemMultiplierActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


