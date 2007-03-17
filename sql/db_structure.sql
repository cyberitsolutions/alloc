
CREATE TABLE absence (
  absenceID int(11) NOT NULL auto_increment,
  dateFrom date default NULL,
  dateTo date default NULL,
  absenceType enum('Annual Leave','Holiday','Illness','Other') default NULL,
  contactDetails text,
  personID int(11) NOT NULL default '0',
  PRIMARY KEY  (absenceID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE announcement (
  announcementID int(11) NOT NULL auto_increment,
  heading varchar(255) default NULL,
  body text,
  personID int(11) NOT NULL default '0',
  displayFromDate date default NULL,
  displayToDate date default NULL,
  PRIMARY KEY  (announcementID)
) TYPE=MyISAM;


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
  clientModifiedTime timestamp(14) NOT NULL,
  clientModifiedUser int(11) NOT NULL default '0',
  clientStatus enum('current','potential','archived') NOT NULL default 'current',
  clientCreatedTime varchar(11) default NULL,
  PRIMARY KEY  (clientID),
  KEY clientName (clientName)
) TYPE=ISAM PACK_KEYS=1;


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
  PRIMARY KEY  (clientContactID),
  KEY clientID (clientID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE comment (
  commentID int(11) NOT NULL auto_increment,
  commentType varchar(255) NOT NULL default '',
  commentLinkID int(11) NOT NULL default '0',
  commentModifiedTime datetime NOT NULL default '0000-00-00 00:00:00',
  commentModifiedUser int(11) NOT NULL default '0',
  comment text,
  PRIMARY KEY  (commentID),
  KEY commentLinkID (commentLinkID),
  KEY commentType (commentType)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE config (
  configID int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL,
  value text NOT NULL,
  type enum("text","array") NOT NULL default "text",
  PRIMARY KEY (configID),
  UNIQUE KEY (name)
) TYPE=MyISAM;


CREATE TABLE expenseForm (
  expenseFormID int(11) NOT NULL auto_increment,
  expenseFormModifiedUser int(11) NOT NULL default '0',
  lastModified timestamp(14) NOT NULL,
  paymentMethod varchar(255) default NULL,
  reimbursementRequired tinyint(4) NOT NULL default '0',
  enteredBy int(11) NOT NULL default '0',
  transactionRepeatID int(11) NOT NULL default '0',
  expenseFormFinalised tinyint(4) NOT NULL default '0',
  seekClientReimbursement int(1) NOT NULL default 0,
  PRIMARY KEY  (expenseFormID)
) TYPE=MyISAM;


CREATE TABLE history (
  historyID int(11) NOT NULL auto_increment,
  the_time timestamp(14) NOT NULL,
  the_place varchar(255) NOT NULL default '',
  the_args varchar(255) default NULL,
  personID int(11) NOT NULL default '0',
  the_label varchar(255) default '',
  PRIMARY KEY  (historyID)
) TYPE=MyISAM;


CREATE TABLE htmlElement (
  htmlElementID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) NOT NULL,
  htmlElementParentID INT(11) DEFAULT 0,
  handle VARCHAR(255) NOT NULL,
  label VARCHAR(255) DEFAULT NULL,
  helpText TEXT DEFAULT NULL,
  defaultValue VARCHAR(255) DEFAULT NULL,
  sequence INT(11) DEFAULT 0,
  enabled  INT(1) DEFAULT 1,
  PRIMARY KEY (htmlElementID)
);

CREATE TABLE htmlAttribute (
  htmlAttributeID INT(11) NOT NULL auto_increment,
  htmlElementID INT(11) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  value VARCHAR(255) DEFAULT NULL,
  isDefault INT(1) DEFAULT 0,
  PRIMARY KEY (htmlAttributeID)
);

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
);

CREATE TABLE htmlAttributeType (
  htmlAttributeTypeID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) DEFAULT NULL,
  name VARCHAR(255) NOT NULL DEFAULT "",
  defaultValue VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (htmlAttributeTypeID)
);


CREATE TABLE invoice (
  invoiceID int(11) NOT NULL auto_increment,
  invoiceDate date NOT NULL default '0000-00-00',
  invoiceNum int(11) NOT NULL default '0',
  invoiceName varchar(255) NOT NULL default '',
  PRIMARY KEY  (invoiceID)
) TYPE=MyISAM;


CREATE TABLE invoiceItem (
  invoiceItemID int(11) NOT NULL auto_increment,
  invoiceID int(11) NOT NULL default '0',
  iiMemo text,
  iiQuantity float default NULL,
  iiUnitPrice float default NULL,
  iiAmount float default NULL,
  status varchar(255) NOT NULL default '',
  PRIMARY KEY  (invoiceItemID)
) TYPE=MyISAM;


CREATE TABLE item (
  itemID int(11) NOT NULL auto_increment,
  itemName varchar(255) default '',
  itemNotes text,
  lastModified timestamp(14) NOT NULL,
  itemModifiedUser int(11) NOT NULL default '0',
  itemType enum('cd','book','other') NOT NULL default 'cd',
  itemAuthor varchar(255) default '',
  personID int(11) default '0',
  PRIMARY KEY  (itemID)
) TYPE=MyISAM;


CREATE TABLE loan (
  loanID int(11) NOT NULL auto_increment,
  itemID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  loanModifiedUser int(11) NOT NULL default '0',
  lastModified timestamp(14) NOT NULL,
  dateBorrowed date NOT NULL default '0000-00-00',
  dateToBeReturned date default NULL,
  dateReturned date default NULL,
  PRIMARY KEY  (loanID)
) TYPE=MyISAM;


CREATE TABLE patchLog (
  patchLogID int(11) NOT NULL auto_increment,
  patchName varchar(255) NOT NULL,
  patchDesc text,
  patchDate timestamp(14) NOT NULL,
  PRIMARY KEY  (patchLogID)
) TYPE=ISAM PACK_KEYS=1;


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
) TYPE=MyISAM;


CREATE TABLE person (
  username varchar(32) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  perms varchar(255) default NULL,
  personID int(11) NOT NULL auto_increment,
  emailAddress varchar(255) default NULL,
  availability text,
  areasOfInterest text,
  comments text,
  managementComments text,
  emailFormat varchar(255) default NULL,
  lastLoginDate datetime default NULL,
  personModifiedUser int(11) NOT NULL default '0',
  firstName varchar(255) default NULL,
  surname varchar(255) default NULL,
  preferred_tfID int(11) default NULL,
  dailyTaskEmail varchar(255) default 'yes',
  personActive tinyint(1) default '1',
  phoneNo1 varchar(255) default "",
  phoneNo2 varchar(255) default "",
  sessData text,
  PRIMARY KEY (personID),
  UNIQUE KEY (username)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE project (
  projectID int(11) NOT NULL auto_increment,
  projectName varchar(255) NOT NULL default '',
  projectComments text,
  clientID int(11) NOT NULL default '0',
  clientContactID int(11) default '0',
  projectModifiedUser int(11) NOT NULL default '0',
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
  projectBudget decimal(10,0) default NULL,
  currencyType enum('AUD','USD','NZD','CAD') default NULL,
  projectShortName varchar(255) default NULL,
  projectStatus enum('current','potential','archived') NOT NULL default 'current',
  projectPriority int(11) default NULL,
  is_agency tinyint(4) default NULL,
  cost_centre_tfID int(11) default NULL,
  customerBilledDollars decimal(19,2) default '0.00',
  PRIMARY KEY  (projectID),
  KEY projectName (projectName),
  KEY clientID (clientID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE projectCommissionPerson (
  projectCommissionPersonID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  commissionPercent decimal(5,3) default '0.000',
  tfID int(11) NOT NULL default '0',
  PRIMARY KEY  (projectCommissionPersonID)
) TYPE=MyISAM;


CREATE TABLE projectModificationNote (
  projectModNoteID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  dateMod date default NULL,
  modDescription text,
  personID int(11) NOT NULL default '0',
  PRIMARY KEY  (projectModNoteID)
) TYPE=MyISAM;


CREATE TABLE projectPerson (
  projectPersonID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  projectPersonRoleID int(11) NOT NULL default '0',
  emailType enum('None','Assigned Tasks','All Tasks') default NULL,
  rate decimal(5,2) default '0.00',
  rateUnitID int(3) default NULL,
  projectPersonModifiedUser int(11) NOT NULL default '0',
  emailDateRegex varchar(255) default NULL,
  PRIMARY KEY  (projectPersonID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE projectPersonRole (
  projectPersonRoleID int(11) NOT NULL auto_increment,
  projectPersonRoleName varchar(255) default NULL,
  projectPersonRoleHandle varchar(255) default NULL,
  projectPersonRoleSortKey int(11) default NULL,
  PRIMARY KEY  (projectPersonRoleID)
) TYPE=MyISAM;


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
  reminderModifiedTime datetime NOT NULL default '0000-00-00 00:00:00',
  reminderModifiedUser int(11) NOT NULL default '0',
  PRIMARY KEY  (reminderID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE sentEmailLog (
  sentEmailLogID int(11) NOT NULL auto_increment,
  sentEmailTo text NOT NULL,
  sentEmailSubject varchar(255),
  sentEmailBody text,
  sentEmailHeader varchar(255),
  sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','daily_digest','timesheet_finished','new_password'),
  sentEmailLogModifiedTime timestamp(14) NOT NULL,
  sentEmailLogModifiedUser int(11) NOT NULL default '0',
  PRIMARY KEY  (sentEmailLogID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE sess (
  sessID varchar(32) NOT NULL default '',
  personID int(11) NOT NULL default '0',
  sessData text,
  PRIMARY KEY  (sessID)
) TYPE=MyISAM;


CREATE TABLE skillList (
  skillID int(11) NOT NULL auto_increment,
  skillName varchar(40) NOT NULL default '',
  skillDescription text,
  skillClass varchar(40) NOT NULL default '',
  PRIMARY KEY  (skillID)
) TYPE=MyISAM;


CREATE TABLE skillProficiencys (
  proficiencyID int(11) NOT NULL auto_increment,
  personID int(11) NOT NULL default '0',
  skillID int(11) NOT NULL default '0',
  skillProficiency enum('Novice','Junior','Intermediate','Advanced','Senior') NOT NULL default 'Novice',
  PRIMARY KEY  (proficiencyID)
) TYPE=MyISAM;


CREATE TABLE task (
  taskID int(11) NOT NULL auto_increment,
  taskName varchar(255) NOT NULL default '',
  taskDescription text,
  creatorID int(11) NOT NULL default '0',
  closerID int(11) default NULL,
  priority tinyint(4) NOT NULL default '0',
  timeEstimate decimal(4,2) default '0.00',
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
  parentTaskID int(11) NOT NULL default '0',
  taskTypeID int(11) NOT NULL default '1',
  taskModifiedUser int(11) NOT NULL default '0',
  taskCommentTemplateID int(11) default NULL,
  PRIMARY KEY  (taskID),
  KEY taskName (taskName),
  KEY dateAdded (dateCreated),
  KEY projectID (projectID),
  KEY parentTaskID (parentTaskID),
  KEY taskTypeID (taskTypeID),
  KEY parentTaskID_2 (parentTaskID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE taskCCList (
  taskCCListID int(11) NOT NULL auto_increment,
  taskID int(11) NOT NULL default '0',
  fullName text,
  emailAddress text NOT NULL,
  PRIMARY KEY  (taskCCListID)
) TYPE=MyISAM;


CREATE TABLE taskCommentTemplate (
  taskCommentTemplateID int(11) NOT NULL auto_increment,
  taskCommentTemplateName varchar(255) default NULL,
  taskCommentTemplateText text,
  taskCommentTemplateLastModified timestamp(14) NOT NULL,
  PRIMARY KEY  (taskCommentTemplateID)
) TYPE=MyISAM;


CREATE TABLE taskType (
  taskTypeID int(11) NOT NULL auto_increment,
  taskTypeName varchar(255) default NULL,
  taskTypeActive int(1) default NULL,
  taskTypeSequence int(11) default NULL,
  PRIMARY KEY  (taskTypeID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE tf (
  tfID int(11) NOT NULL auto_increment,
  tfName varchar(255) NOT NULL default '',
  tfComments text,
  tfModifiedTime timestamp(14) NOT NULL,
  tfModifiedUser int(11) NOT NULL default '0',
  qpEmployeeNum int(11) default NULL,
  quickenAccount varchar(255) default NULL,
  PRIMARY KEY  (tfID)
) TYPE=MyISAM;


CREATE TABLE tfPerson (
  tfPersonID int(11) NOT NULL auto_increment,
  tfID int(11) NOT NULL default '0',
  personID int(11) NOT NULL default '0',
  PRIMARY KEY  (tfPersonID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE timeSheet (
  timeSheetID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL default '0',
  dateFrom date default NULL,
  dateTo date default NULL,
  status enum('edit','manager','admin','invoiced','finished') default NULL,
  personID int(11) NOT NULL default '0',
  approvedByManagerPersonID int(11) default NULL,
  approvedByAdminPersonID int(11) default NULL,
  invoiceNum int(11) default NULL,
  invoiceItemID int(11) default '0',
  dateSubmittedToManager date default NULL,
  dateSubmittedToAdmin date default NULL,
  invoiceDate date default NULL,
  billingNote text,
  payment_insurance tinyint(4) default '0',
  recipient_tfID int(11) default NULL,
  PRIMARY KEY  (timeSheetID)
) TYPE=MyISAM;


CREATE TABLE timeSheetItem (
  timeSheetItemID int(11) NOT NULL auto_increment,
  timeSheetID int(11) NOT NULL default '0',
  dateTimeSheetItem date default NULL,
  timeSheetItemDuration decimal(4,2) default '0.00',
  timeSheetItemDurationUnitID int(3) default NULL,
  description text,
  location text,
  personID int(11) NOT NULL default '0',
  taskID int(11) default '0',
  rate decimal(5,2) default '0.00',
  commentPrivate tinyint(1) default '0',
  comment text,
  PRIMARY KEY  (timeSheetItemID)
) TYPE=ISAM PACK_KEYS=1;


CREATE TABLE timeUnit (
  timeUnitID int(11) NOT NULL auto_increment,
  timeUnitName varchar(30) default NULL,
  timeUnitLabelA varchar(30) default NULL,
  timeUnitLabelB varchar(30) default NULL,
  timeUnitSeconds int(11) default NULL,
  timeUnitActive int(1) default NULL,
  timeUnitSequence int(11) default NULL,
  PRIMARY KEY  (timeUnitID)
) TYPE=MyISAM;


CREATE TABLE transaction (
  transactionID int(11) NOT NULL auto_increment,
  companyDetails text NOT NULL,
  product varchar(255) NOT NULL default '',
  amount float NOT NULL default '0',
  status varchar(255) NOT NULL default 'pending',
  expenseFormID int(11) NOT NULL default '0',
  tfID int(11) NOT NULL default '0',
  projectID int(11) default '0',
  transactionModifiedUser int(11) NOT NULL default '0',
  lastModified timestamp(14) NOT NULL,
  quantity int(11) NOT NULL default '1',
  dateEntered date NOT NULL default '0000-00-00',
  transactionDate date NOT NULL default '0000-00-00',
  invoiceItemID int(11) default NULL,
  transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance') NOT NULL default 'invoice',
  timeSheetID int(11) default NULL,
  transactionRepeatID int(11) default NULL,
  PRIMARY KEY  (transactionID)
) TYPE=MyISAM;


CREATE TABLE transactionRepeat (
  transactionRepeatID int(11) NOT NULL auto_increment,
  tfID int(11) NOT NULL default '0',
  payToName text NOT NULL,
  payToAccount text NOT NULL,
  companyDetails text NOT NULL,
  emailOne varchar(255) default '',
  emailTwo varchar(255) default '',
  transactionRepeatModifiedUser int(11) NOT NULL default '0',
  lastModified timestamp(14) NOT NULL,
  dateEntered date NOT NULL default '0000-00-00',
  transactionStartDate date NOT NULL default '0000-00-00',
  transactionFinishDate date NOT NULL default '0000-00-00',
  paymentBasis varchar(255) NOT NULL default '',
  amount float NOT NULL default '0',
  product varchar(255) NOT NULL default '',
  status varchar(255) NOT NULL default 'pending',
  transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','insurance') NOT NULL default 'invoice',
  reimbursementRequired tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (transactionRepeatID)
) TYPE=MyISAM;

