-- Change type of timeEstimate so that null can be stores
ALTER TABLE task CHANGE timeEstimate timeEstimate DECIMAL(4,2) DEFAULT NULL;

