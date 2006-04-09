
-- *** This file has to be VALID SQL!
-- 1. COMMENT OUT THE SQL THAT HAS BEEN APPLIED TO PRODUCTION
-- 2. Type !!date in vim and list the db structure changes.  
-- 3. Make sure every statement ends in a semicolon

-- Wed Oct  2 00:11:00 EST 2002 for release 83
-- alter table person add dailyTaskEmail varchar(255) default "yes";

-- Tue Oct 15 14:21:05 EST 2002
-- alter table item add itemAuthor varchar(255) default "";

-- Thu Nov  7 21:01:11 EST 2002
-- ALTER TABLE transaction ADD projectID int(11) default '0' AFTER tfID;


-- Fri Dec 17 17:55:06 EST 2004

-- For active/inactive users
-- alter table person add active BOOL default 1; <- done to prod
-- update person set active = 1;

-- Dropping complex and never implemented permissions
-- alter table projectPerson drop canViewTasks;
-- alter table projectPerson drop canAddTasks;
-- alter table projectPerson drop canDeleteTasks;
-- alter table projectPerson drop canViewTaskAllocs;

-- Rename taskType to more useable form
-- update taskType set taskTypeName = "Parent/Phase" where taskTypeID = 2;

-- Add owner functionality to item table
-- ALTER TABLE item ADD personID int(11) default 0;

-- client created time field
-- ALTER TABLE client ADD clientCreatedTime varchar(11);

-- Don't FORGET TO UPDATE PERMISSIONS FOR permissionID=30 to allow Add Tasks for ALL as well


-- Mon May 23 14:58:19 EST 2005
-- Structure for taskCommentTemplate table
-- REMEBER TO ADD PERM_READ_WRITE for the taskCommentTemplate table in PRODUCTION!
-- create table taskCommentTemplate
-- (
--   taskCommentTemplateID int(11) PRIMARY KEY, 
--   taskCommentTemplateName varchar(255), 
--   taskCommentTemplateText text, 
--   taskCommentTemplateLastModified timestamp(14)
-- );   

-- add taskCommentTemplate column to task table
-- alter table task add taskCommentTemplateID int(11);

--- Mon Jun 27 00:46:38 EST 2005
--- create table projectPersonRole
--- (
--- projectPersonRoleID int(11) PRIMARY KEY AUTO_INCREMENT,
--- projectPersonRoleName varchar(255),
--- projectPersonRoleHandle varchar(255),
--- projectPersonRoleSortKey int
--- );
--- insert into projectPersonRole (projectPersonRoleName,projectPersonRoleHandle,projectPersonRoleSortKey) values ('Project Manager','isManager',10);
--- insert into projectPersonRole (projectPersonRoleName,projectPersonRoleHandle,projectPersonRoleSortKey) values ('Engineer (edit tasks)','canEditTasks',20);



--- Do these things when pushing alloc_stage to alloc!

---  THIS IS IMPORTANT SINCE _STAGE IS USING NEW ROLE METHOD AND _PRODUCTION IS USING canEditTasks/isMAnager method
--- Need to preserve data from prod and stage!!!!!!!!!!!!!!!!
-- update projectPerson set projectPersonRoleID = 2 where canEditTasks = 1;
-- update projectPerson set projectPersonRoleID = 1 where isManager = 1;

-- alter table projectPerson drop canEditTasks;
-- alter table projectPerson drop isManager;
-- update timeSheetItem set unit = "Hourly" where unit = "Hour";

-- update timeSheetItem set timeSheetItemDurationUnitID = 1 where unit = "Hourly";
-- update timeSheetItem set timeSheetItemDurationUnitID = 2 where unit = "Daily";
-- update timeSheetItem set timeSheetItemDurationUnitID = 3 where unit = "Weekly";
-- update timeSheetItem set timeSheetItemDurationUnitID = 4 where unit = "Monthly";
-- alter table timeSheetItem drop unit;

-- update projectPerson set rateUnitID = 1 where rateType = "Hourly";
-- update projectPerson set rateUnitID = 2 where rateType = "Daily";
-- update projectPerson set rateUnitID = 3 where rateType = "Weekly";
-- update projectPerson set rateUnitID = 4 where rateType = "Monthly";
-- alter table projectPerson drop rateType;

-- alter table taskType drop sortKey;
-- update taskType set taskTypeActive = 1;
-- update taskType set taskTypeSequence = 10 WHERE taskTypeID = 1;
-- update taskType set taskTypeSequence = 20 WHERE taskTypeID = 2;
-- update taskType set taskTypeSequence = 30 WHERE taskTypeID = 3;
-- update taskType set taskTypeSequence = 40 WHERE taskTypeID = 4;

--- alter table task change timeEstimate timeEstimate  decimal(4,2) default 0;

--- New table to standardise time types across task, timeSheetItem and projectPerson
--- CREATE TABLE timeUnit (
--- timeUnitID int(11) PRIMARY KEY AUTO_INCREMENT,
--- timeUnitName varchar(30),
--- timeUnitLabelA varchar(30),
--- timeUnitLabelB varchar(30),
--- timeUnitSeconds int,
--- timeUnitActive int(1),
--- timeUnitSequence int
--- );

--- insert into timeUnit values (1, "hour","Hours","Hourly",3600,1,10);
--- insert into timeUnit values (2, "day","Days","Daily",28800,1,20);
--- insert into timeUnit values (3, "week","Weeks","Weekly",144000,1,30);
--- insert into timeUnit values (4, "month","Months","Monthly",576000,1,40);

--- alter table task add timeEstimateUnitID int(3) default NULL after timeEstimate;
--- alter table timeSheetItem add timeSheetItemDurationUnitID int(3) default NULL after timeSheetItemDuration;
--- alter table projectPerson add rateUnitID int(3) default NULL after rate;


--- alter table taskType add taskTypeActive int(1);
--- alter table taskType add taskTypeSequence int;


--- alter table person change active personActive tinyint(1) default 1;
--- insert into timeUnit values (5, "fixed", "Fixed Rate", "Fixed Rate",0,1,50);


-- alter table timeSheetItem add commentPrivate tinyint(1) default 0;

--- Mon Jan  2 16:32:23 EST 2006
-- alter table task add dateAssigned datetime DEFAULT NULL after dateCreated;


-- Can't do this!  
-- alter table timeSheetItem change location comment text default null;

-- Did this instead!
--alter table timeSheetItem add comment text default null;

-- Will have to do this again ... when pushing staging to prod!!!
update timeSheetItem set comment = location where comment is null and (location != "" or location IS NOT NULL);

drop table config;

CREATE TABLE config (
  configID int(11) PRIMARY KEY AUTO_INCREMENT,
  name text NOT NULL default '',
  value text NOT NULL default ''
);

insert into config (name,value) values ('AllocFromEmailAddress','alloc-admin@cyber.com.au');
insert into config (name,value) values ('cybersourceTfID','7');
insert into config (name,value) values ('timeSheetAdminEmail','69');
insert into config (name,value) values ('companyName','Cybersource Pty Ltd');
insert into config (name,value) values ('companyContactPhone','+61 3 9621 2377');
insert into config (name,value) values ('companyContactFax','+61 3 9621 2477');
insert into config (name,value) values ('companyContactEmail','info@cyber.com.au');
insert into config (name,value) values ('companyContactHomePage','http://www.cyber.com.au');
insert into config (name,value) values ('companyContactAddress','Level 4, 10-16 Queen St, Melbourne Vic. 3000 Australia');
insert into config (name,value) values ('companyACN','');
insert into config (name,value) values ('allocURL','http://alloc/');


insert into taskType (taskTypeID,taskTypeName,taskTypeActive,taskTypeSequence) values (5,"Milestone",1,50);
update task set taskTypeID = 5 where isMilestone !=0;
update task set taskTypeID = 5 where isMilestone !=0;
alter table task drop isMilestone;


CREATE TABLE taskCCList (
  taskCCListID int(11) PRIMARY KEY AUTO_INCREMENT,
  taskID int(11) NOT NULL,
  fullName text default "",
  emailAddress text NOT NULL
);

update projectPersonRole set projectPersonRoleSortKey = 30 where projectPersonRoleHandle = "isManager";

insert into projectPersonRole (projectPersonRoleID,projectPersonRoleName,projectPersonRoleHandle,projectPersonRoleSortKey) VALUES 
                              (3,"Project Manager + Time Sheet Recipient","timeSheetRecipient",40);


-- Run a script which updates all the projectPerson entries from the project table
-- project/fix_projectperson_timesheet_manager_thing.php
-- This will only work as of mysql 4.04!!!: update projectPerson,project set projectPersonRoleID = 3 where project.projectID = projectPerson.projectID and project.timesheets_to_manager = projectPerson.personID;



alter table project drop managerUserID;
-- alter table project drop timesheets_to_manager;

alter table projectCommissionPerson change commissionPrecent commissionPercent decimal(5,3) default 0;




alter table absence change absenceID absenceID int(11) NOT NULL;
alter table absence change absenceID absenceID int(11) NOT NULL AUTO_INCREMENT default 0;
alter table announcement change announcementID announcementID int(11) AUTO_INCREMENT;
alter table client change clientID clientID int(11) AUTO_INCREMENT;
alter table employee change employeeID employeeID int(11) AUTO_INCREMENT;
alter table eventFilter change eventFilterID eventFilterID int(11) AUTO_INCREMENT;
alter table expenseForm change expenseFormID expenseFormID int(11) AUTO_INCREMENT;
alter table history change historyID historyID int(11) AUTO_INCREMENT;
alter table invoice change invoiceID invoiceID int(11) AUTO_INCREMENT;
alter table invoiceItem change invoiceItemID invoiceItemID int(11) AUTO_INCREMENT;
alter table item change itemID itemID int(11) AUTO_INCREMENT;
alter table loan change loanID loanID int(11) AUTO_INCREMENT;
alter table permission change permissionID permissionID int(11) AUTO_INCREMENT;


select * into outfile '/tmp/person.sql' from person;

drop table person;

CREATE TABLE person (
  username varchar(32) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  perms varchar(255) default NULL,
  personID int(11) NOT NULL AUTO_INCREMENT default '0',
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
  PRIMARY KEY  (personID),
  KEY username (username)
) TYPE=ISAM PACK_KEYS=1;

LOAD DATA INFILE '/tmp/person.sql' INTO TABLE person;

alter table position change positionID positionID int(11) AUTO_INCREMENT;
alter table project change projectID projectID int(11) AUTO_INCREMENT;
alter table projectCommissionPerson change projectCommissionPersonID projectCommissionPersonID int(11) AUTO_INCREMENT;
alter table projectModificationNote change projectModNoteID projectModNoteID int(11) AUTO_INCREMENT;
alter table projectPerson change projectPersonID projectPersonID int(11) AUTO_INCREMENT;
alter table task change taskID taskID int(11) AUTO_INCREMENT;
alter table taskCommentTemplate change taskCommentTemplateID taskCommentTemplateID int(11) AUTO_INCREMENT;
alter table taskType change taskTypeID taskTypeID int(11) AUTO_INCREMENT;
alter table tf change tfID tfID int(11) AUTO_INCREMENT;
alter table tfPerson change tfPersonID tfPersonID int(11) AUTO_INCREMENT;
alter table timeSheet change timeSheetID timeSheetID int(11) AUTO_INCREMENT;
alter table timeSheetItem change timeSheetItemID timeSheetItemID int(11) AUTO_INCREMENT;
alter table transaction change transactionID transactionID int(11) AUTO_INCREMENT;
alter table transactionRepeat change transactionRepeatID transactionRepeatID int(11) AUTO_INCREMENT;


drop table db_sequence;
drop table employee;
drop table position;


update task set timeEstimateUnitID = null where timeEstimate = 0;
update task set timeEstimate = null where timeEstimate = 0;


-- Run script to update the time sheet commission thing where a zero commission thing gets the remainder of time sheet funds.
-- In other words the script will add a new projectPersonCommission entry for each project


-- WHen Deploying live, don't forget that you moved the cron scripts, so you'll need to change the cron jobs!!


drop table active_sessions;


CREATE TABLE sess (
  sessID varchar(32) NOT NULL default '',
  personID int(11) NOT NULL default '0',
  sessData text,
  PRIMARY KEY  (sessID)
) TYPE=MyISAM;


alter table person add sessData text default "";

alter table expenseForm drop chequeDate;
alter table expenseForm drop chequeNumber;


