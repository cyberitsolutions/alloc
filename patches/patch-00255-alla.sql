-- Add new repeating invoices functionality
ALTER TABLE invoice ADD invoiceRepeatID INTEGER DEFAULT NULL AFTER invoiceID;
ALTER TABLE invoice ADD invoiceRepeatDate DATE DEFAULT NULL AFTER invoiceRepeatID;

DROP TABLE IF EXISTS invoiceRepeat;
CREATE TABLE invoiceRepeat (
  invoiceRepeatID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceID integer NOT NULL,
  personID integer NOT NULL,
  message TEXT DEFAULT NULL,
  active BOOLEAN DEFAULT true
) ENGINE=InnoDB PACK_KEYS=0;

ALTER TABLE invoice ADD CONSTRAINT invoice_invoiceRepeatID FOREIGN KEY (invoiceRepeatID) REFERENCES invoiceRepeat (invoiceRepeatID);
ALTER TABLE invoiceRepeat ADD CONSTRAINT invoiceRepeat_invoiceID FOREIGN KEY (invoiceID) REFERENCES invoice (invoiceID);
ALTER TABLE invoiceRepeat ADD CONSTRAINT invoiceRepeat_personID FOREIGN KEY (personID) REFERENCES person (personID);

INSERT INTO permission (tableName,entityID,roleName,actions,comment) VALUES ("invoiceRepeat",0,"admin",15,"Admin controls repeating invoices.");


DROP TABLE IF EXISTS invoiceRepeatDate;
CREATE TABLE invoiceRepeatDate (
  invoiceRepeatDateID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceRepeatID integer NOT NULL,
  invoiceDate date NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;


ALTER TABLE invoiceRepeatDate ADD CONSTRAINT invoiceRepeat_invoiceRepeatID FOREIGN KEY (invoiceRepeatID) REFERENCES invoiceRepeat (invoiceRepeatID);

INSERT INTO permission (tableName,entityID,roleName,actions,comment) VALUES ("invoiceRepeatDate",0,"admin",15,"Admin controls repeating invoices.");


ALTER TABLE invoice ADD invoiceCreatedTime datetime default NULL;
ALTER TABLE invoice ADD invoiceCreatedUser integer default NULL;
ALTER TABLE invoice ADD invoiceModifiedTime datetime default NULL;
ALTER TABLE invoice ADD invoiceModifiedUser integer default NULL;
