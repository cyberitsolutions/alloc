--Changes for default engineer rates

INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetRate', '', 'text');
INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetUnit', 1, 'text');

ALTER TABLE project ADD defaultTimeSheetRate decimal(19,2);
ALTER TABLE project ADD defaultTimeSheetRateUnitID int(11);

ALTER TABLE project ADD CONSTRAINT project_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);

ALTER TABLE person ADD defaultTimeSheetRate decimal(19,2);
ALTER TABLE person ADD defaultTimeSheetRateUnitID int(11);

ALTER TABLE person ADD CONSTRAINT person_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);

