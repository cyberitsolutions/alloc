-- Add new metadata tables and insert data for them
DROP TABLE IF EXISTS absenceType;
CREATE TABLE absenceType (
  absenceTypeID varchar(255) PRIMARY KEY,
  absenceTypeSeq integer NOT NULL,
  absenceTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS clientStatus;
CREATE TABLE clientStatus (
  clientStatusID varchar(255) PRIMARY KEY,
  clientStatusSeq integer NOT NULL,
  clientStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS configType;
CREATE TABLE configType (
  configTypeID varchar(255) PRIMARY KEY,
  configTypeSeq integer NOT NULL,
  configTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS invoiceStatus;
CREATE TABLE invoiceStatus (
  invoiceStatusID varchar(255) PRIMARY KEY,
  invoiceStatusSeq integer NOT NULL,
  invoiceStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS itemType;
CREATE TABLE itemType (
  itemTypeID varchar(255) PRIMARY KEY,
  itemTypeSeq integer NOT NULL,
  itemTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS projectType;
CREATE TABLE projectType (
  projectTypeID varchar(255) PRIMARY KEY,
  projectTypeSeq integer NOT NULL,
  projectTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS currencyType;
CREATE TABLE currencyType (
  currencyTypeID varchar(255) PRIMARY KEY,
  currencyTypeSeq integer NOT NULL,
  currencyTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS projectStatus;
CREATE TABLE projectStatus (
  projectStatusID varchar(255) PRIMARY KEY,
  projectStatusSeq integer NOT NULL,
  projectStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS roleLevel;
CREATE TABLE roleLevel (
  roleLevelID varchar(255) PRIMARY KEY,
  roleLevelSeq integer NOT NULL,
  roleLevelActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS reminderRecuringInterval;
CREATE TABLE reminderRecuringInterval (
  reminderRecuringIntervalID varchar(255) PRIMARY KEY,
  reminderRecuringIntervalSeq integer NOT NULL,
  reminderRecuringIntervalActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS reminderAdvNoticeInterval;
CREATE TABLE reminderAdvNoticeInterval (
  reminderAdvNoticeIntervalID varchar(255) PRIMARY KEY,
  reminderAdvNoticeIntervalSeq integer NOT NULL,
  reminderAdvNoticeIntervalActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS sentEmailType;
CREATE TABLE sentEmailType (
  sentEmailTypeID varchar(255) PRIMARY KEY,
  sentEmailTypeSeq integer NOT NULL,
  sentEmailTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS skillProficiency;
CREATE TABLE skillProficiency (
  skillProficiencyID varchar(255) PRIMARY KEY,
  skillProficiencySeq integer NOT NULL,
  skillProficiencyActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS changeType;
CREATE TABLE changeType (
  changeTypeID varchar(255) PRIMARY KEY,
  changeTypeSeq integer NOT NULL,
  changeTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS timeSheetStatus;
CREATE TABLE timeSheetStatus (
  timeSheetStatusID varchar(255) PRIMARY KEY,
  timeSheetStatusSeq integer NOT NULL,
  timeSheetStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS transactionStatus;
CREATE TABLE transactionStatus (
  transactionStatusID varchar(255) PRIMARY KEY,
  transactionStatusSeq integer NOT NULL,
  transactionStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS transactionType;
CREATE TABLE transactionType (
  transactionTypeID varchar(255) PRIMARY KEY,
  transactionTypeSeq integer NOT NULL,
  transactionTypeActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


DROP TABLE IF EXISTS productSaleStatus;
CREATE TABLE productSaleStatus (
  productSaleStatusID varchar(255) PRIMARY KEY,
  productSaleStatusSeq integer NOT NULL,
  productSaleStatusActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;


-- Info for the new metadata tables

INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Annual Leave',1,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Holiday',2,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Illness',3,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Other',4,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('current',1,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('potential',2,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('archived',3,true);
INSERT INTO configType (configTypeID, configTypeSeq, configTypeActive) VALUES ('text',1,true);
INSERT INTO configType (configTypeID, configTypeSeq, configTypeActive) VALUES ('array',2,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('edit',1,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('reconcile',2,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('finished',3,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('cd',1,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('book',2,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('other',3,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('contract',1,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('job',2,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('project',3,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('prepaid',4,true);
INSERT INTO currencyType (currencyTypeID, currencyTypeSeq, currencyTypeActive) VALUES ('AUD',1,true);
INSERT INTO currencyType (currencyTypeID, currencyTypeSeq, currencyTypeActive) VALUES ('USD',2,true);
INSERT INTO currencyType (currencyTypeID, currencyTypeSeq, currencyTypeActive) VALUES ('NZD',3,true);
INSERT INTO currencyType (currencyTypeID, currencyTypeSeq, currencyTypeActive) VALUES ('CAD',4,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('current',1,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('potential',2,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('archived',3,true);
INSERT INTO roleLevel (roleLevelID, roleLevelSeq, roleLevelActive) VALUES ('person',1,true);
INSERT INTO roleLevel (roleLevelID, roleLevelSeq, roleLevelActive) VALUES ('project',2,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('No',1,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('Hour',2,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('Day',3,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('Week',4,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('Month',5,true);
INSERT INTO reminderRecuringInterval (reminderRecuringIntervalID, reminderRecuringIntervalSeq, reminderRecuringIntervalActive) VALUES ('Year',6,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('No',1,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Minute',2,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Hour',3,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Day',4,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Week',5,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Month',6,true);
INSERT INTO reminderAdvNoticeInterval (reminderAdvNoticeIntervalID, reminderAdvNoticeIntervalSeq, reminderAdvNoticeIntervalActive) VALUES ('Year',7,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('reminder',1,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('reminder_advnotice',2,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('task_created',3,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('task_closed',4,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('task_comments',5,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('timesheet_submit',6,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('timesheet_reject',7,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('daily_digest',8,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('timesheet_finished',9,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('new_password',10,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('task_reassigned',11,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('orphan',12,true);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('timeSheet_comments',13,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Novice',1,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Junior',2,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Intermediate',3,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Advanced',4,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Senior',5,true);
INSERT INTO changeType (changeTypeID, changeTypeSeq, changeTypeActive) VALUES ('FieldChange',1,true);
INSERT INTO changeType (changeTypeID, changeTypeSeq, changeTypeActive) VALUES ('TaskMarkedDuplicate',2,true);
INSERT INTO changeType (changeTypeID, changeTypeSeq, changeTypeActive) VALUES ('TaskUnmarkedDuplicate',3,true);
INSERT INTO changeType (changeTypeID, changeTypeSeq, changeTypeActive) VALUES ('TaskClosed',4,true);
INSERT INTO changeType (changeTypeID, changeTypeSeq, changeTypeActive) VALUES ('TaskReopened',5,true);
INSERT INTO timeSheetStatus (timeSheetStatusID, timeSheetStatusSeq, timeSheetStatusActive) VALUES ('edit',1,true);
INSERT INTO timeSheetStatus (timeSheetStatusID, timeSheetStatusSeq, timeSheetStatusActive) VALUES ('manager',2,true);
INSERT INTO timeSheetStatus (timeSheetStatusID, timeSheetStatusSeq, timeSheetStatusActive) VALUES ('admin',3,true);
INSERT INTO timeSheetStatus (timeSheetStatusID, timeSheetStatusSeq, timeSheetStatusActive) VALUES ('invoiced',4,true);
INSERT INTO timeSheetStatus (timeSheetStatusID, timeSheetStatusSeq, timeSheetStatusActive) VALUES ('finished',5,true);
INSERT INTO transactionStatus (transactionStatusID, transactionStatusSeq, transactionStatusActive) VALUES ('pending',1,true);
INSERT INTO transactionStatus (transactionStatusID, transactionStatusSeq, transactionStatusActive) VALUES ('rejected',2,true);
INSERT INTO transactionStatus (transactionStatusID, transactionStatusSeq, transactionStatusActive) VALUES ('approved',3,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('invoice',1,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('expense',2,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('salary',3,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('commission',4,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('timesheet',5,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('adjustment',6,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('tax',8,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('sale',9,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('edit',1,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('allocate',2,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('admin',3,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('finished',4,true);


