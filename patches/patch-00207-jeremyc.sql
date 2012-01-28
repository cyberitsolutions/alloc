--Changes for default engineer rates

INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetRate', '', 'text');
INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetUnit', 1, 'text');

ALTER TABLE project ADD defaultTimeSheetRate BIGINT DEFAULT NULL;
ALTER TABLE project ADD defaultTimeSheetRateUnitID integer DEFAULT NULL;

ALTER TABLE project ADD CONSTRAINT project_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);

ALTER TABLE person ADD defaultTimeSheetRate BIGINT DEFAULT NULL;
ALTER TABLE person ADD defaultTimeSheetRateUnitID integer DEFAULT NULL;

ALTER TABLE person ADD CONSTRAINT person_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);

