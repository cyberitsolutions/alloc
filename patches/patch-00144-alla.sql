-- Fix up some fields for postgres compatibility
alter table comment change commentEmailRecipients commentEmailRecipients text default null;
alter table history change the_time the_time datetime NOT NULL;
alter table patchLog change patchDate patchDate datetime NOT NULL;
alter table task change dateCreated dateCreated datetime NOT NULL;
alter table transaction change transactionDate transactionDate date NOT NULL;
alter table transactionRepeat change transactionStartDate transactionStartDate date NOT NULL;
alter table transactionRepeat change transactionFinishDate transactionFinishDate date NOT NULL;
alter table loan change dateBorrowed dateBorrowed date NOT NULL;
alter table reminder change reminderTime reminderTime datetime NOT NULL;

CREATE INDEX idx_tfPerson_tfID ON tfPerson (tfID);
ALTER TABLE tfPerson DROP INDEX idx_tfID;

CREATE INDEX idx_timeSheetItem_timeSheetID ON timeSheetItem (timeSheetID);
ALTER TABLE timeSheetItem DROP INDEX idx_timeSheetID;

CREATE INDEX idx_transaction_timeSheetID ON transaction (timeSheetID);
CREATE INDEX idx_transaction_tfID ON transaction (tfID);
ALTER TABLE transaction DROP INDEX idx_timeSheetID;
ALTER TABLE transaction DROP INDEX idx_tfID;

ALTER TABLE token CHANGE tokenCreatedDate tokenCreatedDate datetime NOT NULL;

CREATE INDEX idx_clientID ON project (clientID);
ALTER TABLE project DROP INDEX clientID;

ALTER TABLE client CHANGE clientCreatedTime clientCreatedTime datetime default NULL;

ALTER TABLE project CHANGE projectClientPhone projectClientPhone varchar(255) DEFAULT NULL;
ALTER TABLE project CHANGE projectClientMobile projectClientMobile varchar(255) DEFAULT NULL;

