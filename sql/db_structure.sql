
CREATE TABLE absence (
  absenceID int(11) NOT NULL auto_increment,
  dateFrom date default NULL,
  dateTo date default NULL,
  absenceType enum('Annual Leave','Holiday','Illness','Other') default NULL,
  contactDetails text,
  personID int(11) NOT NULL default '0',
  PRIMARY KEY  (absenceID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE announcement (
  announcementID int(11) NOT NULL auto_increment,
  heading varchar(255) default NULL,
  body text,
  personID int(11) NOT NULL default '0',
  displayFromDate date default NULL,
  displayToDate date default NULL,
  PRIMARY KEY  (announcementID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE client (
  clientID int(11) NOT NULL auto_increment,
  clientName varchar(255) NOT NULL default '',
  clientPrimaryContactID int(11) default NULL,
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
  clientModifiedUser int(11) DEFAULT NULL,
  clientStatus enum('current','potential','archived') NOT NULL default 'current',
  clientCreatedTime varchar(11) default NULL,
  PRIMARY KEY  (clientID),
  KEY clientName (clientName)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE clientContact (
  clientContactID int(11) NOT NULL auto_increment,
  clientID int(11) NOT NULL default '0',
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
  PRIMARY KEY  (clientContactID),
  KEY clientID (clientID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE comment (
  commentID int(11) NOT NULL auto_increment,
  commentType varchar(255) NOT NULL default '',
  commentLinkID int(11) NOT NULL default '0',
  commentCreatedTime datetime default NULL,
  commentCreatedUser int(11) default NULL,
  commentModifiedTime datetime DEFAULT NULL,
  commentModifiedUser int(11) DEFAULT NULL,
  commentCreatedUserClientContactID int(11) DEFAULT NULL,
  commentCreatedUserText varchar(255) DEFAULT NULL,
  commentEmailRecipients VARCHAR(255) DEFAULT "",
  commentEmailUID VARCHAR(255) DEFAULT NULL,
  comment TEXT,
  PRIMARY KEY  (commentID),
  KEY commentLinkID (commentLinkID),
  KEY commentType (commentType)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE config (
  configID int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL DEFAULT '',
  value text NOT NULL,
  type enum("text","array") NOT NULL default "text",
  PRIMARY KEY (configID),
  UNIQUE KEY (name)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE expenseForm (
  expenseFormID int(11) NOT NULL auto_increment,
  clientID int(11) DEFAULT NULL,
  expenseFormModifiedUser int(11) DEFAULT NULL,
  expenseFormModifiedTime datetime DEFAULT NULL,
  paymentMethod varchar(255) default NULL,
  reimbursementRequired tinyint(4) NOT NULL default '0',
  expenseFormCreatedUser int(11) DEFAULT NULL,
  expenseFormCreatedTime datetime DEFAULT NULL,
  transactionRepeatID int(11) NOT NULL default '0',
  expenseFormFinalised tinyint(4) NOT NULL default '0',
  seekClientReimbursement int(1) NOT NULL default 0,
  expenseFormComment text default "",
  PRIMARY KEY  (expenseFormID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE history (
  historyID int(11) NOT NULL auto_increment,
  the_time timestamp(14) NOT NULL,
  the_place varchar(255) NOT NULL default '',
  the_args varchar(255) default NULL,
  personID int(11) NOT NULL default '0',
  the_label varchar(255) default '',
  PRIMARY KEY  (historyID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE htmlElement (
  htmlElementID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) NOT NULL DEFAULT 0,
  htmlElementParentID INT(11) DEFAULT 0,
  handle VARCHAR(255) NOT NULL DEFAULT '',
  label VARCHAR(255) DEFAULT NULL,
  helpText TEXT DEFAULT NULL,
  defaultValue VARCHAR(255) DEFAULT NULL,
  sequence INT(11) DEFAULT 0,
  enabled  INT(1) DEFAULT 1,
  PRIMARY KEY (htmlElementID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE htmlAttribute (
  htmlAttributeID INT(11) NOT NULL auto_increment,
  htmlElementID INT(11) NOT NULL DEFAULT 0,
  name VARCHAR(255) DEFAULT NULL,
  value VARCHAR(255) DEFAULT NULL,
  isDefault INT(1) DEFAULT 0,
  PRIMARY KEY (htmlAttributeID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE htmlElementType (
  htmlElementTypeID INT(11) NOT NULL auto_increment,
  handle VARCHAR(255) DEFAULT NULL,
  name VARCHAR(255) DEFAULT NULL,
  hasEndTag INT(1) DEFAULT 1,
  hasChildElement INT(1) DEFAULT 0,
  hasContent INT(1) DEFAULT 0,
  hasValueContent INT(1) DEFAULT 0,
  hasValueAttribute INT(1) DEFAULT 0,
  valueAttributeName VARCHAR(255) DEFAULT NULL,
  hasLabelValue INT(1) DEFAULT 0,
  parentHtmlElementID INT(11) DEFAULT 0,
  PRIMARY KEY  (htmlElementTypeID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE htmlAttributeType (
  htmlAttributeTypeID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) DEFAULT NULL,
  name VARCHAR(255) NOT NULL DEFAULT "",
  defaultValue VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (htmlAttributeTypeID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE invoice (
  invoiceID int(11) NOT NULL auto_increment,
  clientID int(11) NOT NULL,
  invoiceDateFrom date,
  invoiceDateTo date,
  invoiceNum int(11) NOT NULL default '0',
  invoiceName varchar(255) NOT NULL default '',
  invoiceStatus enum('edit','reconcile','finished') NOT NULL DEFAULT 'edit',
  PRIMARY KEY  (invoiceID),
  UNIQUE KEY `invoiceNum` (`invoiceNum`)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE invoiceItem (
  invoiceItemID int(11) NOT NULL auto_increment,
  invoiceID int(11) NOT NULL,
  timeSheetID int(11) DEFAULT NULL,
  timeSheetItemID int(11) DEFAULT NULL,
  expenseFormID int(11) DEFAULT NULL,
  transactionID int(11) DEFAULT NULL,
  iiMemo text DEFAULT NULL,
  iiQuantity DECIMAL(19,2) DEFAULT NULL,
  iiUnitPrice DECIMAL(19,2) DEFAULT NULL,
  iiAmount DECIMAL(19,2) DEFAULT NULL,
  iiDate date DEFAULT NULL,
  INDEX idx_invoiceID (invoiceID),
  PRIMARY KEY (invoiceItemID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE item (
  itemID int(11) NOT NULL auto_increment,
  itemName varchar(255) default '',
  itemNotes text,
  itemModifiedTime datetime DEFAULT NULL,
  itemModifiedUser int(11) DEFAULT NULL,
  itemType enum('cd','book','other') NOT NULL default 'cd',
  itemAuthor varchar(255) default '',
  personID int(11) default '0',
  PRIMARY KEY  (itemID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE loan (
  loanID int(11) NOT NULL auto_increment,
  itemID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  loanModifiedUser int(11) DEFAULT NULL,
  loanModifiedTime datetime DEFAULT NULL,
  dateBorrowed date NOT NULL default '0000-00-00',
  dateToBeReturned date default NULL,
  dateReturned date default NULL,
  PRIMARY KEY  (loanID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE patchLog (
  patchLogID int(11) NOT NULL auto_increment,
  patchName varchar(255) NOT NULL DEFAULT '',
  patchDesc text,
  patchDate timestamp(14) NOT NULL,
  PRIMARY KEY  (patchLogID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE permission (
  tableName varchar(255) default NULL,
  entityID int(11) default NULL,
  personID int(11) default NULL,
  roleName varchar(255) default NULL,
  allow enum('Y','N') default NULL,
  sortKey int(11) default '100',
  comment text,
  actions int(11) default NULL,
  permissionID int(11) NOT NULL auto_increment,
  PRIMARY KEY  (permissionID),
  KEY tableName (tableName)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE person (
  username varchar(32) NOT NULL default '',
  password varchar(255) NOT NULL default '',
  perms varchar(255) default NULL,
  personID int(11) NOT NULL auto_increment,
  emailAddress varchar(255) default NULL,
  availability text,
  areasOfInterest text,
  comments text,
  managementComments text,
  lastLoginDate datetime default NULL,
  personModifiedUser int(11) DEFAULT NULL,
  firstName varchar(255) default NULL,
  surname varchar(255) default NULL,
  preferred_tfID int(11) default NULL,
  personActive tinyint(1) default '1',
  sessData text,
  phoneNo1 varchar(255) default "",
  phoneNo2 varchar(255) default "",
  emergencyContact varchar(255) default "",
  PRIMARY KEY (personID),
  UNIQUE KEY (username)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE project (
  projectID int(11) NOT NULL auto_increment,
  projectName varchar(255) NOT NULL default '',
  projectComments text,
  clientID int(11) NOT NULL default '0',
  clientContactID int(11) default '0',
  projectModifiedUser int(11) DEFAULT NULL,
  projectType enum('contract','job','project') default NULL,
  projectClientName varchar(255) default NULL,
  projectClientPhone varchar(20) default NULL,
  projectClientMobile varchar(20) default NULL,
  projectClientEMail text,
  projectClientAddress text,
  dateTargetStart date default NULL,
  dateTargetCompletion date default NULL,
  dateActualStart date default NULL,
  dateActualCompletion date default NULL,
  projectBudget DECIMAL(19,2) DEFAULT NULL,
  currencyType enum('AUD','USD','NZD','CAD') default NULL,
  projectShortName varchar(255) default NULL,
  projectStatus enum('current','potential','archived') NOT NULL default 'current',
  projectPriority int(11) default NULL,
  is_agency tinyint(4) default NULL,
  cost_centre_tfID int(11) default NULL,
  customerBilledDollars DECIMAL(19,2) DEFAULT NULL,
  PRIMARY KEY  (projectID),
  KEY projectName (projectName),
  KEY clientID (clientID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE projectCommissionPerson (
  projectCommissionPersonID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  commissionPercent decimal(5,3) default '0.000',
  tfID int(11) NOT NULL default '0',
  PRIMARY KEY  (projectCommissionPersonID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE projectModificationNote (
  projectModNoteID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  dateMod date default NULL,
  modDescription text,
  personID int(11) NOT NULL default '0',
  PRIMARY KEY  (projectModNoteID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE projectPerson (
  projectPersonID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  roleID int(11) NOT NULL default '0',
  emailType enum('None','Assigned Tasks','All Tasks') default NULL,
  rate DECIMAL(19,2) DEFAULT '0.00',
  rateUnitID int(3) default NULL,
  projectPersonModifiedUser int(11) DEFAULT NULL,
  emailDateRegex varchar(255) default NULL,
  PRIMARY KEY (projectPersonID),
  INDEX idx_person_project (projectID,personID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE role (
  roleID int(11) NOT NULL auto_increment,
  roleName varchar(255) default NULL,
  roleHandle varchar(255) default NULL,
  roleLevel ENUM('person','project') NOT NULL,
  roleSequence int(11) default NULL,
  PRIMARY KEY  (roleID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE reminder (
  reminderID int(11) NOT NULL auto_increment,
  reminderType varchar(255) default NULL,
  reminderLinkID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  reminderTime datetime NOT NULL default '0000-00-00 00:00:00',
  reminderRecuringInterval enum('No','Hour','Day','Week','Month','Year') NOT NULL default 'No',
  reminderRecuringValue int(11) NOT NULL default '0',
  reminderAdvNoticeSent tinyint(1) NOT NULL default '0',
  reminderAdvNoticeInterval enum('No','Minute','Hour','Day','Week','Month','Year') NOT NULL default 'No',
  reminderAdvNoticeValue int(11) NOT NULL default '0',
  reminderSubject varchar(255) NOT NULL default '',
  reminderContent text,
  reminderModifiedTime datetime DEFAULT NULL,
  reminderModifiedUser int(11) DEFAULT NULL,
  PRIMARY KEY  (reminderID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE sentEmailLog (
  sentEmailLogID int(11) NOT NULL auto_increment,
  sentEmailTo text NOT NULL,
  sentEmailSubject varchar(255),
  sentEmailBody text,
  sentEmailHeader text,
  sentEmailType
  enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','daily_digest','timesheet_finished','new_password', 'task_reassigned','orphan') DEFAULT NULL,
  sentEmailLogCreatedTime datetime default NULL,
  sentEmailLogCreatedUser int(11) default NULL,
  PRIMARY KEY  (sentEmailLogID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE sess (
  sessID varchar(32) NOT NULL default '',
  personID int(11) NOT NULL default '0',
  sessData text,
  PRIMARY KEY  (sessID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE skillList (
  skillID int(11) NOT NULL auto_increment,
  skillName varchar(40) NOT NULL default '',
  skillDescription text,
  skillClass varchar(40) NOT NULL default '',
  PRIMARY KEY  (skillID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE skillProficiencys (
  proficiencyID int(11) NOT NULL auto_increment,
  personID int(11) NOT NULL default '0',
  skillID int(11) NOT NULL default '0',
  skillProficiency enum('Novice','Junior','Intermediate','Advanced','Senior') NOT NULL default 'Novice',
  PRIMARY KEY  (proficiencyID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE task (
  taskID int(11) NOT NULL auto_increment,
  taskName varchar(255) NOT NULL default '',
  taskDescription text,
  creatorID int(11) NOT NULL default '0',
  closerID int(11) default NULL,
  priority tinyint(4) NOT NULL default '0',
  timeEstimate decimal(7,2) DEFAULT NULL,
  dateCreated datetime NOT NULL default '0000-00-00 00:00:00',
  dateAssigned datetime default NULL,
  dateClosed datetime default NULL,
  dateTargetCompletion date default NULL,
  taskComments text,
  projectID int(11) NOT NULL default '0',
  dateActualCompletion date default NULL,
  dateActualStart date default NULL,
  dateTargetStart date default NULL,
  personID int(11) default NULL,
  managerID int(11) default NULL,
  parentTaskID int(11) NOT NULL default '0',
  taskTypeID int(11) NOT NULL default '1',
  taskModifiedUser int(11) DEFAULT NULL,
  taskCommentTemplateID int(11) default NULL,
  duplicateTaskID int(11) default NULL,
  PRIMARY KEY  (taskID),
  KEY taskName (taskName),
  KEY dateAdded (dateCreated),
  KEY projectID (projectID),
  KEY parentTaskID (parentTaskID),
  KEY taskTypeID (taskTypeID),
  KEY parentTaskID_2 (parentTaskID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE interestedParty (
  interestedPartyID int(11) NOT NULL auto_increment,
  entity VARCHAR(255) NOT NULL,
  entityID int(11) NOT NULL,
  fullName text,
  emailAddress text NOT NULL,
  personID int(11) DEFAULT NULL,
  clientContactID int(11) DEFAULT NULL,
  external tinyint(1) DEFAULT NULL,
  PRIMARY KEY  (interestedPartyID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE commentTemplate (
  commentTemplateID int(11) NOT NULL auto_increment,
  commentTemplateName varchar(255) default NULL,
  commentTemplateText text,
  commentTemplateModifiedTime datetime DEFAULT NULL,
  PRIMARY KEY  (commentTemplateID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE taskType (
  taskTypeID int(11) NOT NULL auto_increment,
  taskTypeName varchar(255) default NULL,
  taskTypeActive int(1) default NULL,
  taskTypeSequence int(11) default NULL,
  PRIMARY KEY  (taskTypeID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE tf (
  tfID int(11) NOT NULL auto_increment,
  tfName varchar(255) NOT NULL default '',
  tfComments text,
  tfModifiedTime datetime DEFAULT NULL,
  tfModifiedUser int(11) DEFAULT NULL,
  qpEmployeeNum int(11) default NULL,
  quickenAccount varchar(255) default NULL,
  status enum('active','disabled') default 'active',
  PRIMARY KEY  (tfID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE tfPerson (
  tfPersonID int(11) NOT NULL auto_increment,
  tfID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  INDEX idx_tfID (tfID),
  PRIMARY KEY  (tfPersonID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE timeSheet (
  timeSheetID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  dateFrom date default NULL,
  dateTo date default NULL,
  status enum('edit','manager','admin','invoiced','finished') default NULL,
  personID int(11) NOT NULL default '0',
  approvedByManagerPersonID int(11) default NULL,
  approvedByAdminPersonID int(11) default NULL,
  dateSubmittedToManager date default NULL,
  dateSubmittedToAdmin date default NULL,
  invoiceDate date default NULL,
  billingNote text,
  payment_insurance tinyint(4) default '0',
  recipient_tfID int(11) default NULL,
  customerBilledDollars DECIMAL(19,2) DEFAULT '0.00',
  PRIMARY KEY  (timeSheetID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE timeSheetItem (
  timeSheetItemID int(11) NOT NULL auto_increment,
  timeSheetID int(11) NOT NULL default '0',
  dateTimeSheetItem date default NULL,
  timeSheetItemDuration decimal(9,2) default '0.00',
  timeSheetItemDurationUnitID int(3) default NULL,
  description text,
  location text,
  personID int(11) NOT NULL default '0',
  taskID int(11) default '0',
  rate DECIMAL(19,2) DEFAULT '0.00',
  commentPrivate tinyint(1) default '0',
  comment text,
  multiplier tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY  (timeSheetItemID),
  INDEX idx_taskID (taskID),
  INDEX idx_timeSheetID (timeSheetID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE timeUnit (
  timeUnitID int(11) NOT NULL auto_increment,
  timeUnitName varchar(30) default NULL,
  timeUnitLabelA varchar(30) default NULL,
  timeUnitLabelB varchar(30) default NULL,
  timeUnitSeconds int(11) default NULL,
  timeUnitActive int(1) default NULL,
  timeUnitSequence int(11) default NULL,
  PRIMARY KEY  (timeUnitID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE token (
  tokenID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  tokenHash VARCHAR(255) NOT NULL DEFAULT '',
  tokenEntity VARCHAR(32) DEFAULT '',
  tokenEntityID INT(11),
  tokenActionID INT(11) NOT NULL DEFAULT 0,
  tokenExpirationDate DATETIME DEFAULT NULL,
  tokenUsed INT(11) DEFAULT 0,
  tokenMaxUsed INT(11) DEFAULT 0,
  tokenActive INT(1) DEFAULT 0,
  tokenCreatedBy INT(11) NOT NULL DEFAULT 0,
  tokenCreatedDate DATETIME,
  UNIQUE KEY (tokenHash)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE tokenAction (
  tokenActionID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  tokenAction VARCHAR(32) NOT NULL DEFAULT '',
  tokenActionType VARCHAR(32),
  tokenActionMethod VARCHAR(32)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE transaction (
  transactionID int(11) NOT NULL auto_increment,
  companyDetails text NOT NULL,
  product varchar(255) NOT NULL default '',
  amount DECIMAL(19,2) NOT NULL DEFAULT 0,
  status enum('pending','rejected','approved') NOT NULL DEFAULT 'pending',
  expenseFormID int(11) DEFAULT NULL,
  tfID int(11) NOT NULL default '0',
  projectID int(11) DEFAULT NULL,
  transactionModifiedUser int(11) DEFAULT NULL,
  transactionModifiedTime datetime DEFAULT NULL,
  quantity int(11) NOT NULL default '1',
  transactionCreatedUser int(11) DEFAULT NULL,
  transactionCreatedTime datetime DEFAULT NULL,
  transactionDate date NOT NULL default '0000-00-00',
  invoiceID int(11) DEFAULT NULL,
  invoiceItemID int(11) default NULL,
  transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance','tax','product') NOT NULL,
  timeSheetID int(11) default NULL,
  productSaleItemID int(11) default NULL,
  transactionRepeatID int(11) default NULL,
  INDEX idx_timeSheetID (timeSheetID),
  INDEX idx_tfID (tfID),
  INDEX idx_invoiceItemID (invoiceItemID),
  PRIMARY KEY (transactionID)
) TYPE=MyISAM PACK_KEYS=0;


CREATE TABLE transactionRepeat (
  transactionRepeatID int(11) NOT NULL auto_increment,
  tfID int(11) NOT NULL default '0',
  payToName text NOT NULL,
  payToAccount text NOT NULL,
  companyDetails text NOT NULL,
  emailOne varchar(255) default '',
  emailTwo varchar(255) default '',
  transactionRepeatModifiedUser int(11) DEFAULT NULL,
  transactionRepeatModifiedTime datetime DEFAULT NULL,
  transactionRepeatCreatedUser int(11) DEFAULT NULL,
  transactionRepeatCreatedTime datetime DEFAULT NULL,
  transactionStartDate date NOT NULL default '0000-00-00',
  transactionFinishDate date NOT NULL default '0000-00-00',
  paymentBasis varchar(255) NOT NULL default '',
  amount DECIMAL(19,2) NOT NULL DEFAULT 0,
  product varchar(255) NOT NULL default '',
  status varchar(255) NOT NULL default 'pending',
  transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance') NOT NULL default 'invoice',
  reimbursementRequired tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (transactionRepeatID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE product (
  productID int(11) NOT NULL auto_increment,
  productName varchar(255) NOT NULL DEFAULT '',
  buyCost DECIMAL(19,2) NOT NULL DEFAULT 0,
  sellPrice DECIMAL(19,2) NOT NULL DEFAULT 0,
  description varchar(255),
  comment TEXT,
  PRIMARY KEY (productID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productCost (
  productCostID int(11) NOT NULL auto_increment,
  productID int(11) NOT NULL DEFAULT 0,
  tfID int(11) DEFAULT 0,
  amount DECIMAL(19,2) NOT NULL DEFAULT 0,
  isPercentage BOOL DEFAULT 0,
  description varchar(255)
  PRIMARY KEY (productCostID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSale (
  productSaleID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL,
  status enum('edit', 'admin', 'invoiced', 'finished') DEFAULT NULL,
  productSaleCreatedTime datetime default NULL,
  productSaleCreatedUser int(11) default NULL,
  productSaleModifiedTime datetime default NULL,
  productSaleModifiedUser int(11) default NULL,
  PRIMARY KEY (productSaleID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSaleItem (
  productSaleItemID int(11) NOT NULL auto_increment,
  productID int(11) NOT NULL,
  productSaleID int(11) NOT NULL,
  buyCost DECIMAL(19,2) NOT NULL DEFAULT 0,
  sellPrice DECIMAL(19,2) NOT NULL DEFAULT 0,
  quantity int(5) DEFAULT 1,
  description varchar(255),
  PRIMARY KEY (productSaleItemID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSaleTransaction (
  productSaleTransactionID int(11) NOT NULL auto_increment,
  productSaleItemID int(11) NOT NULL,
  tfID int(11) DEFAULT 0,
  amount DECIMAL (19,2) NOT NULL DEFAULT 0,
  isPercentage BOOL DEFAULT 0,
  description varchar(255),
  PRIMARY KEY (productSaleTransactionID)
) TYPE=MyISAM PACK_KEYS=0;

