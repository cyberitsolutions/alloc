-- MySQL dump 8.23
--
-- Host: localhost    Database: alloc
---------------------------------------------------------
-- Server version	3.23.58

--
-- Dumping data for table `permission`
--


INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('config',0,0,'','Y',100,'Allow all users to read the configuration',17,1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('config',0,0,'admin','Y',100,'Allow admin users to update the configuration',15,2);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('person',0,0,'','Y',100,'Allow all users to read all person records.  Note that read permisssion for person entities only provides access to the username, first name and surname - all other fields require the \"read details\" permission',1,3);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('taskType',0,0,'','Y',100,'Allow all users to read the taskType table',17,4);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('client',0,0,'','Y',100,'Allow everyone to do anything with clients',15,5);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectPerson',-1,0,'','Y',100,'Allow users to read projectPerson records that they own.',17,12);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectPerson',0,0,'admin','Y',100,'Allow admin staff to read and write project person records',31,37);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('project',0,0,'','Y',100,'allow everyone to READ projects this way it doesn\\\'t bugger up the search..',513,30);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('absence',-1,0,'employee','Y',100,'Allow employees to read and write absence forms for their own user',15,15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('absence',0,0,'manage','Y',100,'Allow managers to read and write all absence forms',31,16);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('expenseForm',0,0,'admin','Y',100,'Allow admin staff to do anything with expense forms',31,18);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('expenseForm',-1,0,'employee','Y',100,'Allow employees to do anything to expense forms that they own (an expense forms owner is its creator or someone who has access to a TF that is listed on the expense form)',31,19);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('invoice',0,0,'admin','Y',100,'Allow admin staff to do anything with invoices',31,20);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('invoice',-1,0,'employee','Y',100,'Allow employees to read invoices that they own (a user owns an invoice if they have a related transaction in a TF they can access)',17,21);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('tf',0,0,'admin','Y',100,'Allow admin staff to do anything to TF records',31,22);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('tf',0,0,'employee','Y',100,'Allow employees to read all TF records (this does not mean they can read the transactions, just things like the TF name which they need to fill in expense forms for other people)',1,23);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('invoiceItem',0,0,'admin','Y',100,'Allow admin staff to do anything with all invoice items',271,24);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('invoiceItem',-1,0,'employee','Y',100,'Allow employees to read invoice items they own (a user owns an invoice item if they own any of the related TF transactions)',17,25);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('item',0,0,'employee','Y',100,'Allow employees to read, update and create all items',11,26);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('item',0,0,'admin','Y',100,'Allow admin staff to read and write all items',31,27);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('loan',0,0,'employee','Y',100,'Allow all employees to view loans',17,28);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('loan',-1,0,'employee','Y',100,'Allow employees to read and write loan records that they own',31,29);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('project',0,0,'admin','Y',100,'Allow admin staff to read and write project records',783,31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectCommissionPerson',-1,0,'','Y',100,'Allow people to read their own commission records',15,32);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectCommissionPerson',0,0,'admin','Y',100,'Allow admin staff to do anything with project commission records',31,33);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectCommissionPerson',-1,0,'manage','Y',100,'Allow managers to do anything with project commission records for which they are an owner of ie they are a project Person for.',15,34);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectModificationNote',0,0,'admin','Y',100,'Allow admin to mess with modification notes',15,35);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectModificationNote',0,0,'manage','Y',100,'Allow managers to read and create project modification notes',9,36);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectPerson',-1,0,'manage','Y',100,'Allow managers to read and write project people records for projects they are an owner of.  Ie they are a projectPerson for.',15,38);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('task',-1,0,'employee','Y',100,'Allow employees to read, update and create tasks that they own (a user owns a task if they are assigned to the task\\\'s project)... added delete too.',287,39);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('task',0,0,'manage','Y',100,'Allow managers to perform all actions on tasks',287,41);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('task',0,0,'admin','Y',100,'Allow admin staff to read all tasks',257,42);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('tfPerson',-1,0,'employee','Y',100,'Allow employees to read tfPerson records that they own (necessary for security checks)',1,44);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('tfPerson',0,0,'admin','Y',100,'Allow admin staff to read and write tfPerson records (i.e. determine who has access to which TFs)',15,45);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheet',-1,0,'employee','Y',100,'Allow employees to read, write and monitor their own time sheets',31,46);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('person',-1,0,'','Y',100,'Allow users to read and write their own details',259,47);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheet',0,0,'admin','Y',100,'Allow admin staff to read, write, invoice and monitor all time sheets',783,48);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheet',0,0,'manage','Y',100,'Allow managers to read, write, approve and monitor all time sheets',287,49);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheetItem',-1,0,'employee','Y',100,NULL,15,50);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheetItem',0,0,'admin','Y',100,'Allow admin staff to read and write all time sheet items',15,51);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('transaction',-1,0,'employee','Y',100,'Allow employees to read and write their own transaction records',15,54);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('transaction',0,0,'admin','Y',100,'Allow admin staff to read, write and monitor all transaction records',65295,55);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('eventFilter',-1,0,'','Y',100,'Allow users to do anything with their own event filters',15,70);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('eventFilter',0,0,'','Y',100,'Allow all users to read event filter records - this is necessary to allow notifications to be sent to the appropriate users when a user triggers an event',1,69);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('announcement',0,0,'admin','Y',100,'Allow admin to perform all operations on all announcements',15,71);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('announcement',0,0,'','Y',100,'Allow all users to read all announcements',1,72);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('transactionRepeat',-1,0,'employee','Y',100,'Allow employees to read their own repeating transactions',1,73);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('transactionRepeat',0,0,'admin','Y',100,'Allow admin staff to do anything with repeating transactions',15,74);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('project',-1,0,'manage','Y',100,'Allow managers to read and write all project records that they are a projectperson for.',783,75);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('skillProficiencys',0,0,'manage','Y',100,'Allow admin do do anything to skillProficiencys',15,78);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('skillList',0,0,'admin','Y',100,'Allow all admin to do anything with skillList',15,77);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('skillList',0,0,'employee','Y',100,'Allow employees to reed skillList',1,80);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('skillProficiencys',0,0,'employee','Y',100,'Allow employees to read all skillProficiencys',1,81);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('skillProficiencys',-1,0,'employee','Y',100,'Allow employees to change their own skillProficiencys',14,82);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('comment',0,0,'','Y',100,'Allow everyone to do anything to comments',15,83);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('clientContact',0,0,'','Y',100,'everyone can do everything with client contacts (!)',15,84);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('task',0,0,'employee','Y',100,'let people read ALL task records. - removed CREATE perm (2004-11-07 -alex)',1,89);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('project',-1,0,'employee','Y',100,'let employees who have permission create new tasks etc.',769,90);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('reminder',0,0,'','Y',NULL,'Q and Dirty reminders setup, will have to change later probably.',15,91);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('history',0,0,'','Y',NULL,'allow everyone to create history records.',8,95);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('loan',0,0,'admin','Y',100,'allow admin to do whatever they want to do with loans.',15,96);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('person',0,0,'admin','Y',NULL,'Allow admin to do everything with users. (Even change a users role to god!!)',7951,100);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('timeSheetItem',0,0,'manage','Y',10,'Allow managers to do everything to all timesheet items (Clancy 19/9/03)',15,98);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('taskCommentTemplate',0,0,'manage','Y',NULL,'To let project managers manipulate task comment templates.',15,99);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('projectPerson',-1,0,'employee','Y',NULL,'To allow employees who are \\\"Project Managers\\\" on a project to add other people etc.',15,104);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('project',-1,0,'employee','Y',99,'This will hopefully allow projects to administrated by employees with Project Manager perms for a project.',271,103);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions, permissionID) VALUES ('item',-1,0,'','Y',NULL,'Allow people to do what they like with items they own/have created.',15,105);

--
-- Dumping data for table `config`
--


INSERT INTO config (configID, name, value) VALUES (1,'AllocFromEmailAddress','alloc-admin@cyber.com.au');
INSERT INTO config (configID, name, value) VALUES (2,'cybersourceTfID','7');
INSERT INTO config (configID, name, value) VALUES (3,'timeSheetAdminEmail','69');
INSERT INTO config (configID, name, value) VALUES (4,'companyName','Cybersource Pty Ltd');
INSERT INTO config (configID, name, value) VALUES (5,'companyContactPhone','+61 3 9621 2377');
INSERT INTO config (configID, name, value) VALUES (6,'companyContactFax','+61 3 9621 2477');
INSERT INTO config (configID, name, value) VALUES (7,'companyContactEmail','info@cyber.com.au');
INSERT INTO config (configID, name, value) VALUES (8,'companyContactHomePage','http://www.cyber.com.au');
INSERT INTO config (configID, name, value) VALUES (9,'companyContactAddress','Level 4, 10-16 Queen St, Melbourne Vic. 3000 Australia');
INSERT INTO config (configID, name, value) VALUES (10,'companyACN','053 904 082');
INSERT INTO config (configID, name, value) VALUES (11,'allocURL','http://alloc/');

--
-- Dumping data for table `taskType`
--


INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (1,'Task',1,10);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (2,'Parent/Phase',1,20);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (3,'Message',1,30);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (4,'Fault',1,40);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (5,'Milestone',1,50);

--
-- Dumping data for table `timeUnit`
--


INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (1,'hour','Hours','Hourly',3600,1,10);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (2,'day','Days','Daily',28800,1,20);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (3,'week','Weeks','Weekly',144000,1,30);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (4,'month','Months','Monthly',0,1,40);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (5,'fixed','Fixed Rate','Fixed Rate',0,1,50);

--
-- Dumping data for table `projectPersonRole`
--


INSERT INTO projectPersonRole (projectPersonRoleID, projectPersonRoleName, projectPersonRoleHandle, projectPersonRoleSortKey) VALUES (1,'Project Manager','isManager',30);
INSERT INTO projectPersonRole (projectPersonRoleID, projectPersonRoleName, projectPersonRoleHandle, projectPersonRoleSortKey) VALUES (2,'Engineer (edit tasks)','canEditTasks',20);
INSERT INTO projectPersonRole (projectPersonRoleID, projectPersonRoleName, projectPersonRoleHandle, projectPersonRoleSortKey) VALUES (3,'Project Manager + Time Sheet Recipient','timeSheetRecipient',40);

insert into person (username,password,personActive) values ('alloc','/.N0BifPoPoZg',1); 
