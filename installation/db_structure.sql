

DROP TABLE IF EXISTS absence;
CREATE TABLE absence (
  absenceID integer NOT NULL auto_increment PRIMARY KEY,
  dateFrom date default NULL,
  dateTo date default NULL,
  absenceType varchar(255) default NULL,
  contactDetails text,
  personID integer NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS announcement;
CREATE TABLE announcement (
  announcementID integer NOT NULL auto_increment PRIMARY KEY,
  heading varchar(255) default NULL,
  body text,
  personID integer NOT NULL,
  displayFromDate date default NULL,
  displayToDate date default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS client;
CREATE TABLE client (
  clientID integer NOT NULL auto_increment PRIMARY KEY,
  clientName varchar(255) NOT NULL default '',
  clientStreetAddressOne varchar(255) default NULL,
  clientStreetAddressTwo varchar(255) default NULL,
  clientSuburbOne varchar(255) default NULL,
  clientSuburbTwo varchar(255) default NULL,
  clientStateOne varchar(255) default NULL,
  clientStateTwo varchar(255) default NULL,
  clientPostcodeOne varchar(255) default NULL,
  clientPostcodeTwo varchar(255) default NULL,
  clientPhoneOne varchar(255) default NULL,
  clientFaxOne varchar(255) default NULL,
  clientCountryOne varchar(255) default NULL,
  clientCountryTwo varchar(255) default NULL,
  clientComment text,
  clientModifiedTime datetime DEFAULT NULL,
  clientModifiedUser integer DEFAULT NULL,
  clientStatus varchar(255) NOT NULL default 'Current',
  clientCategory integer DEFAULT 1,
  clientCreatedTime datetime default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS clientContact;
CREATE TABLE clientContact (
  clientContactID integer NOT NULL auto_increment PRIMARY KEY,
  clientID integer NOT NULL,
  clientContactName varchar(255) default NULL,
  clientContactStreetAddress varchar(255) default NULL,
  clientContactSuburb varchar(255) default NULL,
  clientContactState varchar(255) default NULL,
  clientContactPostcode varchar(255) default NULL,
  clientContactPhone varchar(255) default NULL,
  clientContactMobile varchar(255) default NULL,
  clientContactFax varchar(255) default NULL,
  clientContactEmail varchar(255) default NULL,
  clientContactOther text,
  clientContactCountry varchar(255) default NULL,
  primaryContact boolean default false,
  clientContactActive boolean default true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS comment;
CREATE TABLE comment (
  commentID integer NOT NULL auto_increment PRIMARY KEY,
  commentMaster varchar(255) NOT NULL default '',
  commentMasterID integer NOT NULL,
  commentType varchar(255) NOT NULL default '',
  commentLinkID integer NOT NULL,
  commentCreatedTime datetime default NULL,
  commentCreatedUser integer default NULL,
  commentModifiedTime datetime DEFAULT NULL,
  commentModifiedUser integer DEFAULT NULL,
  commentCreatedUserClientContactID integer DEFAULT NULL,
  commentCreatedUserText varchar(255) DEFAULT NULL,
  commentEmailRecipients TEXT DEFAULT NULL,
  commentEmailUID VARCHAR(255) DEFAULT NULL,
  commentEmailMessageID TEXT DEFAULT NULL,
  commentMimeParts TEXT DEFAULT NULL,
  comment TEXT) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS config;
CREATE TABLE config (
  configID integer NOT NULL auto_increment PRIMARY KEY,
  name varchar(255) NOT NULL DEFAULT '',
  value text NOT NULL,
  type varchar(255) NOT NULL default 'text'
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS error;
CREATE TABLE error (
  errorID varchar(255) NOT NULL PRIMARY KEY
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS exchangeRate;
CREATE TABLE exchangeRate (
  exchangeRateID integer NOT NULL auto_increment PRIMARY KEY,
  exchangeRateCreatedDate date NOT NULL,
  exchangeRateCreatedTime datetime NOT NULL,
  fromCurrency varchar(3) NOT NULL,
  toCurrency   varchar(3) NOT NULL,
  exchangeRate DECIMAL(14,5) NOT NULL DEFAULT 0
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS expenseForm;
CREATE TABLE expenseForm (
  expenseFormID integer NOT NULL auto_increment PRIMARY KEY,
  clientID integer DEFAULT NULL,
  expenseFormModifiedUser integer DEFAULT NULL,
  expenseFormModifiedTime datetime DEFAULT NULL,
  paymentMethod varchar(255) default NULL,
  reimbursementRequired boolean NOT NULL default false,
  expenseFormCreatedUser integer DEFAULT NULL,
  expenseFormCreatedTime datetime DEFAULT NULL,
  transactionRepeatID integer DEFAULT NULL,
  expenseFormFinalised boolean NOT NULL default false,
  seekClientReimbursement boolean NOT NULL default false,
  expenseFormComment text default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS history;
CREATE TABLE history (
  historyID integer NOT NULL auto_increment PRIMARY KEY,
  the_time datetime NOT NULL,
  the_place varchar(255) NOT NULL default '',
  the_args varchar(255) default NULL,
  personID integer NOT NULL,
  the_label varchar(255) default ''
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS indexQueue;
CREATE TABLE indexQueue (
  indexQueueID integer NOT NULL auto_increment PRIMARY KEY,
  entity varchar(255) NOT NULL,
  entityID integer NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS invoice;
CREATE TABLE invoice (
  invoiceID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceRepeatID integer DEFAULT NULL,
  invoiceRepeatDate date DEFAULT NULL,
  clientID integer NOT NULL,
  projectID integer DEFAULT NULL,
  tfID integer NOT NULL,
  invoiceDateFrom date,
  invoiceDateTo date,
  invoiceNum integer NOT NULL,
  invoiceName varchar(255) NOT NULL default '',
  invoiceStatus varchar(255) NOT NULL DEFAULT 'edit',
  currencyTypeID varchar(3) NOT NULL,
  maxAmount BIGINT DEFAULT 0,
  invoiceCreatedTime datetime default NULL,
  invoiceCreatedUser integer default NULL,
  invoiceModifiedTime datetime default NULL,
  invoiceModifiedUser integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS invoiceItem;
CREATE TABLE invoiceItem (
  invoiceItemID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceID integer NOT NULL,
  timeSheetID integer DEFAULT NULL,
  timeSheetItemID integer DEFAULT NULL,
  expenseFormID integer DEFAULT NULL,
  transactionID integer DEFAULT NULL,
  productSaleID INTEGER DEFAULT NULL,
  productSaleItemID INTEGER DEFAULT NULL,
  iiMemo text DEFAULT NULL,
  iiQuantity DECIMAL(19,2) DEFAULT NULL,
  iiUnitPrice BIGINT DEFAULT NULL,
  iiAmount BIGINT DEFAULT NULL,
  iiTax DECIMAL(9,2) DEFAULT '0.00',
  iiDate date DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS invoiceRepeat;
CREATE TABLE invoiceRepeat (
  invoiceRepeatID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceID integer NOT NULL,
  personID integer NOT NULL,
  message TEXT DEFAULT NULL,
  active BOOLEAN DEFAULT true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS invoiceRepeatDate;
CREATE TABLE invoiceRepeatDate (
  invoiceRepeatDateID integer NOT NULL auto_increment PRIMARY KEY,
  invoiceRepeatID integer NOT NULL,
  invoiceDate date NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS item;
CREATE TABLE item (
  itemID integer NOT NULL auto_increment PRIMARY KEY,
  itemName varchar(255) default '',
  itemNotes text,
  itemModifiedTime datetime DEFAULT NULL,
  itemModifiedUser integer DEFAULT NULL,
  itemType varchar(255) NOT NULL default 'cd',
  itemAuthor varchar(255) default '',
  personID integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS loan;
CREATE TABLE loan (
  loanID integer NOT NULL auto_increment PRIMARY KEY,
  itemID integer NOT NULL,
  personID integer NOT NULL,
  loanModifiedUser integer DEFAULT NULL,
  loanModifiedTime datetime DEFAULT NULL,
  dateBorrowed date NOT NULL,
  dateToBeReturned date default NULL,
  dateReturned date default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS patchLog;
CREATE TABLE patchLog (
  patchLogID integer NOT NULL auto_increment PRIMARY KEY,
  patchName varchar(255) NOT NULL DEFAULT '',
  patchDesc text,
  patchDate datetime NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS permission;
CREATE TABLE permission (
  permissionID integer NOT NULL auto_increment PRIMARY KEY,
  tableName varchar(255) default NULL,
  entityID integer default NULL,
  roleName varchar(255) default NULL,
  sortKey integer default '100',
  actions integer default NULL,
  comment text
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS person;
CREATE TABLE person (
  personID integer NOT NULL auto_increment PRIMARY KEY,
  username varchar(32) NOT NULL,
  password varchar(255) NOT NULL default '',
  perms varchar(255) default NULL,
  emailAddress varchar(255) default NULL,
  availability text,
  areasOfInterest text,
  comments text,
  managementComments text,
  lastLoginDate datetime default NULL,
  personModifiedUser integer DEFAULT NULL,
  firstName varchar(255) default NULL,
  surname varchar(255) default NULL,
  preferred_tfID integer default NULL,
  personActive boolean default true,
  sessData text,
  phoneNo1 varchar(255) default '',
  phoneNo2 varchar(255) default '',
  emergencyContact varchar(255) default '',
  defaultTimeSheetRate BIGINT DEFAULT NULL,
  defaultTimeSheetRateUnitID integer DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS project;
CREATE TABLE project (
  projectID integer NOT NULL auto_increment PRIMARY KEY,
  projectName varchar(255) NOT NULL default '',
  projectComments text,
  clientID integer DEFAULT NULL,
  clientContactID integer default NULL,
  projectCreatedTime datetime default NULL,
  projectCreatedUser integer default NULL,
  projectModifiedTime datetime DEFAULT NULL,
  projectModifiedUser integer DEFAULT NULL,
  projectType varchar(255) default NULL,
  projectClientName varchar(255) default NULL,
  projectClientPhone varchar(255) default NULL,
  projectClientMobile varchar(255) default NULL,
  projectClientEMail text,
  projectClientAddress text,
  dateTargetStart date default NULL,
  dateTargetCompletion date default NULL,
  dateActualStart date default NULL,
  dateActualCompletion date default NULL,
  projectBudget BIGINT DEFAULT NULL,
  currencyTypeID varchar(3) NOT NULL,
  projectShortName varchar(255) default NULL UNIQUE,
  projectStatus varchar(255) NOT NULL default 'Current',
  projectPriority integer default NULL,
  cost_centre_tfID integer default NULL,
  customerBilledDollars BIGINT DEFAULT NULL,
  defaultTaskLimit DECIMAL(7,2) DEFAULT NULL,
  defaultTimeSheetRate BIGINT DEFAULT NULL,
  defaultTimeSheetRateUnitID integer DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS projectCommissionPerson;
CREATE TABLE projectCommissionPerson (
  projectCommissionPersonID integer NOT NULL auto_increment PRIMARY KEY,
  projectID integer NOT NULL,
  personID integer DEFAULT NULL,
  commissionPercent decimal(5,3) default '0.000',
  tfID integer NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS projectPerson;
CREATE TABLE projectPerson (
  projectPersonID integer NOT NULL auto_increment PRIMARY KEY,
  projectID integer NOT NULL,
  personID integer NOT NULL,
  roleID integer NOT NULL,
  emailType varchar(255) default NULL,
  rate BIGINT DEFAULT NULL,
  rateUnitID integer default NULL,
  projectPersonModifiedUser integer DEFAULT NULL,
  emailDateRegex varchar(255) default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS role;
CREATE TABLE role (
  roleID integer NOT NULL auto_increment PRIMARY KEY,
  roleName varchar(255) default NULL,
  roleHandle varchar(255) default NULL,
  roleLevel varchar(255) NOT NULL,
  roleSequence integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS reminder;
CREATE TABLE reminder (
  reminderID integer NOT NULL auto_increment PRIMARY KEY,
  reminderType varchar(255) default NULL,
  reminderLinkID integer NOT NULL,
  reminderTime datetime DEFAULT NULL,
  reminderHash varchar(255) DEFAULT NULL,
  reminderRecuringInterval varchar(255) NOT NULL default 'No',
  reminderRecuringValue integer NOT NULL default '0',
  reminderAdvNoticeSent boolean NOT NULL default false,
  reminderAdvNoticeInterval varchar(255) NOT NULL default 'No',
  reminderAdvNoticeValue integer NOT NULL default '0',
  reminderSubject varchar(255) NOT NULL default '',
  reminderContent text,
  reminderCreatedTime datetime DEFAULT NULL,
  reminderCreatedUser integer DEFAULT NULL,
  reminderModifiedTime datetime DEFAULT NULL,
  reminderModifiedUser integer DEFAULT NULL,
  reminderActive BOOLEAN NOT NULL DEFAULT true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS reminderRecipient;
CREATE TABLE reminderRecipient (
  reminderRecipientID integer NOT NULL auto_increment PRIMARY KEY,
  reminderID integer NOT NULL,
  personID integer,
  metaPersonID integer
) ENGINE=InnoDB PACK_KEYS = 0;

DROP TABLE IF EXISTS sentEmailLog;
CREATE TABLE sentEmailLog (
  sentEmailLogID integer NOT NULL auto_increment PRIMARY KEY,
  sentEmailTo text NOT NULL,
  sentEmailSubject varchar(255),
  sentEmailBody text,
  sentEmailHeader text,
  sentEmailType varchar(255) DEFAULT NULL,
  sentEmailLogCreatedTime datetime default NULL,
  sentEmailLogCreatedUser integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS sess;
CREATE TABLE sess (
  sessID varchar(32) NOT NULL default '' PRIMARY KEY,
  personID integer NOT NULL,
  sessData text
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS skill;
CREATE TABLE skill (
  skillID integer NOT NULL auto_increment PRIMARY KEY,
  skillName varchar(40) NOT NULL default '',
  skillDescription text,
  skillClass varchar(40) NOT NULL default ''
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS proficiency;
CREATE TABLE proficiency (
  proficiencyID integer NOT NULL auto_increment PRIMARY KEY,
  personID integer NOT NULL,
  skillID integer NOT NULL,
  skillProficiency varchar(255) NOT NULL default 'Novice'
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS task;
CREATE TABLE task (
  taskID integer NOT NULL auto_increment PRIMARY KEY,
  taskName varchar(255) NOT NULL default '',
  taskDescription text,
  creatorID integer NOT NULL,
  closerID integer default NULL,
  priority integer NOT NULL default '0',
  timeLimit decimal(7,2) DEFAULT NULL,
  timeBest decimal(7,2) DEFAULT NULL,
  timeWorst decimal(7,2) DEFAULT NULL,
  timeExpected decimal(7,2) DEFAULT NULL,
  dateCreated datetime NOT NULL,
  dateAssigned datetime default NULL,
  dateClosed datetime default NULL,
  dateTargetCompletion date default NULL,
  projectID integer DEFAULT NULL,
  dateActualCompletion date default NULL,
  dateActualStart date default NULL,
  dateTargetStart date default NULL,
  personID integer default NULL,
  managerID integer default NULL,
  parentTaskID integer DEFAULT NULL,
  taskTypeID varchar(255) NOT NULL,
  taskModifiedUser integer DEFAULT NULL,
  duplicateTaskID integer default NULL,
  estimatorID integer DEFAULT NULL,
  taskStatus varchar(255) NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS pendingTask;
CREATE TABLE pendingTask (
  taskID integer NOT NULL,
  pendingTaskID integer NOT NULL,
  PRIMARY KEY(taskID, pendingTaskID)
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS auditItem;
CREATE TABLE auditItem (
  auditItemID integer NOT NULL auto_increment PRIMARY KEY,
  entityName varchar(255) default NULL,
  entityID integer NOT NULL,
  personID integer NOT NULL,
  dateChanged datetime NOT NULL,
  changeType varchar(255) NOT NULL default 'FieldChange',
  fieldName varchar(255) default NULL,
  oldValue text
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS interestedParty;
CREATE TABLE interestedParty (
  interestedPartyID integer NOT NULL auto_increment PRIMARY KEY,
  entity VARCHAR(255) NOT NULL,
  entityID integer NOT NULL,
  fullName text,
  emailAddress text NOT NULL,
  personID integer DEFAULT NULL,
  clientContactID integer DEFAULT NULL,
  external boolean DEFAULT NULL,
  interestedPartyCreatedUser integer DEFAULT NULL,
  interestedPartyCreatedTime datetime DEFAULT NULL,
  interestedPartyActive boolean DEFAULT true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS commentTemplate;
CREATE TABLE commentTemplate (
  commentTemplateID integer NOT NULL auto_increment PRIMARY KEY,
  commentTemplateName varchar(255) DEFAULT NULL,
  commentTemplateText text,
  commentTemplateType varchar(255) DEFAULT NULL,
  commentTemplateModifiedTime datetime DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS tf;
CREATE TABLE tf (
  tfID integer NOT NULL auto_increment PRIMARY KEY,
  tfName varchar(255) NOT NULL default '',
  tfComments text,
  tfModifiedTime datetime DEFAULT NULL,
  tfModifiedUser integer DEFAULT NULL,
  qpEmployeeNum integer default NULL,
  quickenAccount varchar(255) default NULL,
  tfActive boolean NOT NULL DEFAULT true
  ) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS tfPerson;
CREATE TABLE tfPerson (
  tfPersonID integer NOT NULL auto_increment PRIMARY KEY,
  tfID integer NOT NULL,
  personID integer NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS timeSheet;
CREATE TABLE timeSheet (
  timeSheetID integer NOT NULL auto_increment PRIMARY KEY,
  projectID integer NOT NULL,
  dateFrom date default NULL,
  dateTo date default NULL,
  status varchar(255) default NULL,
  personID integer NOT NULL,
  approvedByManagerPersonID integer default NULL,
  approvedByAdminPersonID integer default NULL,
  dateSubmittedToManager date default NULL,
  dateSubmittedToAdmin date default NULL,
  dateRejected date default NULL,
  invoiceDate date default NULL,
  billingNote text,
  recipient_tfID integer default NULL,
  customerBilledDollars BIGINT DEFAULT NULL,
  currencyTypeID VARCHAR(3) NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS timeSheetItem;
CREATE TABLE timeSheetItem (
  timeSheetItemID integer NOT NULL auto_increment PRIMARY KEY,
  timeSheetID integer NOT NULL,
  dateTimeSheetItem date default NULL,
  timeSheetItemDuration decimal(9,2) default '0.00',
  timeSheetItemDurationUnitID integer default NULL,
  description text,
  location text,
  personID integer NOT NULL,
  taskID integer default NULL,
  rate BIGINT DEFAULT 0,
  commentPrivate boolean default false,
  comment text,
  multiplier decimal(9,2) default 1.00 NOT NULL,
  emailUID varchar(255) DEFAULT NULL,
  emailMessageID varchar(255) DEFAULT NULL,
  timeSheetItemCreatedTime datetime default NULL,
  timeSheetItemCreatedUser integer default NULL,
  timeSheetItemModifiedTime datetime DEFAULT NULL,
  timeSheetItemModifiedUser integer DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS tsiHint;
CREATE TABLE tsiHint (
  tsiHintID integer NOT NULL auto_increment PRIMARY KEY,
  date date default NULL,
  duration decimal(9,2) default '0.00',
  personID integer NOT NULL,
  taskID integer default NULL,
  comment text,
  tsiHintCreatedTime datetime default NULL,
  tsiHintCreatedUser integer default NULL,
  tsiHintModifiedTime datetime DEFAULT NULL,
  tsiHintModifiedUser integer DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS timeUnit;
CREATE TABLE timeUnit (
  timeUnitID integer NOT NULL auto_increment PRIMARY KEY,
  timeUnitName varchar(30) default NULL,
  timeUnitLabelA varchar(30) default NULL,
  timeUnitLabelB varchar(30) default NULL,
  timeUnitSeconds integer default NULL,
  timeUnitActive boolean default false,
  timeUnitSequence integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS token;
CREATE TABLE token (
  tokenID integer NOT NULL auto_increment PRIMARY KEY,
  tokenHash VARCHAR(255) NOT NULL DEFAULT '',
  tokenEntity VARCHAR(32) DEFAULT '',
  tokenEntityID integer,
  tokenActionID integer NOT NULL,
  tokenExpirationDate datetime DEFAULT NULL,
  tokenUsed integer DEFAULT 0,
  tokenMaxUsed integer DEFAULT 0,
  tokenActive boolean DEFAULT false,
  tokenCreatedBy integer NOT NULL,
  tokenCreatedDate datetime NOT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS tokenAction;
CREATE TABLE tokenAction (
  tokenActionID integer NOT NULL auto_increment PRIMARY KEY,
  tokenAction VARCHAR(32) NOT NULL DEFAULT '',
  tokenActionType VARCHAR(32),
  tokenActionMethod VARCHAR(32)
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS transaction;
CREATE TABLE transaction (
  transactionID integer NOT NULL auto_increment PRIMARY KEY,
  companyDetails text NOT NULL,
  product varchar(255) NOT NULL default '',
  amount BIGINT NOT NULL DEFAULT 0,
  currencyTypeID VARCHAR(3) NOT NULL,
  destCurrencyTypeID varchar(3) NOT NULL,
  exchangeRate DECIMAL (14,5) NOT NULL DEFAULT 1,
  status varchar(255) NOT NULL DEFAULT 'pending',
  dateApproved DATE DEFAULT NULL,
  expenseFormID integer DEFAULT NULL,
  tfID integer NOT NULL,
  fromTfID integer NOT NULL,
  projectID integer DEFAULT NULL,
  transactionModifiedUser integer DEFAULT NULL,
  transactionModifiedTime datetime DEFAULT NULL,
  quantity integer NOT NULL default '1',
  transactionCreatedUser integer DEFAULT NULL,
  transactionCreatedTime datetime DEFAULT NULL,
  transactionDate date NOT NULL,
  invoiceID integer DEFAULT NULL,
  invoiceItemID integer default NULL,
  transactionType varchar(255) NOT NULL,
  timeSheetID integer default NULL,
  productSaleID integer default NULL,
  productSaleItemID integer default NULL,
  productCostID integer default NULL,
  transactionRepeatID integer default NULL,
  transactionGroupID integer default NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS transactionRepeat;
CREATE TABLE transactionRepeat (
  transactionRepeatID integer NOT NULL auto_increment PRIMARY KEY,
  tfID integer NOT NULL,
  fromTfID integer NOT NULL,
  payToName text NOT NULL,
  payToAccount text NOT NULL,
  companyDetails text NOT NULL,
  emailOne varchar(255) default '',
  emailTwo varchar(255) default '',
  transactionRepeatModifiedUser integer DEFAULT NULL,
  transactionRepeatModifiedTime datetime DEFAULT NULL,
  transactionRepeatCreatedUser integer DEFAULT NULL,
  transactionRepeatCreatedTime datetime DEFAULT NULL,
  transactionStartDate date NOT NULL,
  transactionFinishDate date NOT NULL,
  paymentBasis varchar(255) NOT NULL default '',
  amount BIGINT NOT NULL DEFAULT 0,
  currencyTypeID VARCHAR(3) NOT NULL,
  product varchar(255) NOT NULL default '',
  status varchar(255) NOT NULL default 'pending',
  transactionType varchar(255) NOT NULL,
  reimbursementRequired boolean NOT NULL default false
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS product;
CREATE TABLE product (
  productID integer NOT NULL auto_increment PRIMARY KEY,
  productName varchar(255) NOT NULL DEFAULT '',
  sellPrice BIGINT NOT NULL DEFAULT 0,
  sellPriceCurrencyTypeID varchar(3) NOT NULL,
  sellPriceIncTax boolean NOT NULL default false,
  description varchar(255),
  comment TEXT,
  productActive boolean NOT NULL default true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS productCost;
CREATE TABLE productCost (
  productCostID integer NOT NULL auto_increment PRIMARY KEY,
  productID integer NOT NULL,
  tfID integer NOT NULL,
  amount BIGINT NOT NULL DEFAULT 0,
  currencyTypeID VARCHAR(3) NOT NULL,
  isPercentage boolean NOT NULL default false,
  description varchar(255),
  tax boolean DEFAULT NULL,
  productCostActive boolean NOT NULL DEFAULT true
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS productSale;
CREATE TABLE productSale (
  productSaleID integer NOT NULL auto_increment PRIMARY KEY,
  clientID integer DEFAULT NULL,
  projectID integer DEFAULT NULL,
  personID integer DEFAULT NULL,
  tfID INTEGER NOT NULL,
  status varchar(255) NOT NULL,
  productSaleCreatedTime datetime default NULL,
  productSaleCreatedUser integer default NULL,
  productSaleModifiedTime datetime default NULL,
  productSaleModifiedUser integer default NULL,
  productSaleDate date default NULL,
  extRef VARCHAR(255) DEFAULT NULL,
  extRefDate date DEFAULT NULL
) ENGINE=InnoDB PACK_KEYS=0;

DROP TABLE IF EXISTS productSaleItem;
CREATE TABLE productSaleItem (
  productSaleItemID integer NOT NULL auto_increment PRIMARY KEY,
  productID integer NOT NULL,
  productSaleID integer NOT NULL,
  sellPrice BIGINT NOT NULL DEFAULT 0,
  sellPriceCurrencyTypeID varchar(3) NOT NULL,
  sellPriceIncTax boolean NOT NULL default false,
  quantity DECIMAL(19,2) NOT NULL DEFAULT 1,
  description varchar(255)
) ENGINE=InnoDB PACK_KEYS=0;


-- Meta data tables

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
  currencyTypeID varchar(3) PRIMARY KEY,
  currencyTypeLabel VARCHAR(255) DEFAULT NULL,
  currencyTypeName VARCHAR(255) DEFAULT NULL,
  numberToBasic integer DEFAULT 0,
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



DROP TABLE IF EXISTS taskType;
CREATE TABLE taskType (
  taskTypeID varchar(255) PRIMARY KEY,
  taskTypeSeq integer NOT NULL,
  taskTypeActive boolean default true
) ENGINE=InnoDB PACK_KEYS=0;



DROP TABLE IF EXISTS taskStatus;
CREATE TABLE taskStatus (
  taskStatusID varchar(255) PRIMARY KEY,
  taskStatusLabel varchar(255) DEFAULT NULL,
  taskStatusColour varchar(255) DEFAULT NULL,
  taskStatusSeq integer NOT NULL,
  taskStatusActive boolean default true
) ENGINE=InnoDB PACK_KEYS=0;



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



DROP TABLE IF EXISTS timeSheetItemMultiplier;
CREATE TABLE timeSheetItemMultiplier (
  timeSheetItemMultiplierID decimal(9,2) PRIMARY KEY,
  timeSheetItemMultiplierName varchar(255),
  timeSheetItemMultiplierSeq integer NOT NULL,
  timeSheetItemMultiplierActive boolean DEFAULT true
)ENGINE=InnoDB PACK_KEYS=0;




