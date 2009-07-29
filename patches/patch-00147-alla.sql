-- add referential contraint from timeSheetItem.multiplier to timeSheetItemMultiplier.timeSheetItemMultiplierID
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_multiplier FOREIGN KEY (multiplier) REFERENCES timeSheetItemMultiplier (timeSheetItemMultiplierID) ON UPDATE CASCADE;
