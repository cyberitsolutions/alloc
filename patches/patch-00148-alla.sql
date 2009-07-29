-- nuke old-style config table based time sheet multipliers
DELETE FROM config WHERE name = 'timeSheetMultipliers';
