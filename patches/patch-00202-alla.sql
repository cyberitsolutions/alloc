
-- New field to set default task estimate limit
ALTER TABLE project ADD defaultTaskLimit DECIMAL(7,2) DEFAULT NULL;

-- Add new fields for best, expected and worst
ALTER TABLE task ADD timeBest DECIMAL(7,2) DEFAULT NULL AFTER timeEstimate;
ALTER TABLE task ADD timeExpected DECIMAL(7,2) DEFAULT NULL AFTER timeBest;
ALTER TABLE task ADD timeWorst DECIMAL(7,2) DEFAULT NULL AFTER timeExpected;

-- Rename existing timeEstimate field to timeLimit
ALTER TABLE task CHANGE timeEstimate timeLimit DECIMAL(7,2) DEFAULT NULL;

-- Update the timeExpected because it's really the new timeEstimate field.
UPDATE task SET timeExpected = timeLimit;

-- Update old audit entries
UPDATE auditItem SET fieldName = 'timeLimit' WHERE fieldName = 'timeEstimate';
