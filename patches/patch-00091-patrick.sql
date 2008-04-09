
-- increase the limit on estimated hours to 99999.99
ALTER TABLE task CHANGE timeEstimate timeEstimate DECIMAL(7,2) DEFAULT NULL;

