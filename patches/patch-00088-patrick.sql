
-- add a field in timeSheetItem for the multiplier
ALTER TABLE timeSheetItem ADD multiplier tinyint(4) NOT NULL DEFAULT '0';

-- default value for multipliers
INSERT INTO config (name, value, type) VALUES ("timeSheetMultipliers", "a:5:{i:1;a:2:{s:5:\"label\";s:13:\"Standard rate\";s:10:\"multiplier\";s:1:\"1\";}i:2;a:2:{s:5:\"label\";s:15:\"Time and a half\";s:10:\"multiplier\";s:3:\"1.5\";}i:3;a:2:{s:5:\"label\";s:11:\"Double time\";s:10:\"multiplier\";s:1:\"2\";}i:4;a:2:{s:5:\"label\";s:11:\"Triple time\";s:10:\"multiplier\";s:1:\"3\";}i:5;a:2:{s:5:\"label\";s:9:\"No charge\";s:10:\"multiplier\";s:1:\"0\";}}", "array");

