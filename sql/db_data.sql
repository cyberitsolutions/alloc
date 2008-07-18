-- MySQL dump 8.23
--
-- Host: localhost    Database: alloc
---------------------------------------------------------
-- Server version	3.23.58

--
-- Dumping data for table `permission`
--


INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('config',0,0,'','Y',100,'Allow all users to read the configuration',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('config',0,0,'admin','Y',100,'Allow admin users to update the configuration',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('person',0,0,'','Y',100,'Allow all users to read all person records.  Note that read permisssion for person entities only provides access to the username, first name and surname - all other fields require the \"read details\" permission',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('taskType',0,0,'','Y',100,'Allow all users to read the taskType table',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('client',0,0,'','Y',100,'Allow everyone to do anything with clients',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectPerson',-1,0,'','Y',100,'Allow users to read projectPerson records that they own.',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectPerson',0,0,'admin','Y',100,'Allow admin staff to read and write project person records',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('project',0,0,'','Y',100,'allow everyone to READ projects this way it doesn\\\'t bugger up the search..',513);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('absence',-1,0,'employee','Y',100,'Allow employees to read and write absence forms for their own user',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('absence',0,0,'manage','Y',100,'Allow managers to read and write all absence forms',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('expenseForm',0,0,'admin','Y',100,'Allow admin staff to do anything with expense forms',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('expenseForm',-1,0,'employee','Y',100,'Allow employees to do anything to expense forms that they own (an expense forms owner is its creator or someone who has access to a TF that is listed on the expense form)',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoice',0,0,'admin','Y',100,'Allow admin staff to do anything with invoices',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoice',-1,0,'employee','Y',100,'Allow employees to read invoices that they own (a user owns an invoice if they have a related transaction in a TF they can access)',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('tf',0,0,'admin','Y',100,'Allow admin staff to do anything to TF records',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('tf',0,0,'employee','Y',100,'Allow employees to read all TF records (this does not mean they can read the transactions, just things like the TF name which they need to fill in expense forms for other people)',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoiceItem',0,0,'admin','Y',100,'Allow admin staff to do anything with all invoice items',271);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoiceItem',-1,0,'employee','Y',100,'Allow employees to read invoice items they own (a user owns an invoice item if they own any of the related TF transactions)',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('item',0,0,'employee','Y',100,'Allow employees to read, update and create all items',11);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('item',0,0,'admin','Y',100,'Allow admin staff to read and write all items',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('loan',0,0,'employee','Y',100,'Allow all employees to view loans',17);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('loan',-1,0,'employee','Y',100,'Allow employees to read and write loan records that they own',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('project',0,0,'admin','Y',100,'Allow admin staff to read and write project records',783);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectCommissionPerson',-1,0,'','Y',100,'Allow people to read their own commission records',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectCommissionPerson',0,0,'admin','Y',100,'Allow admin staff to do anything with project commission records',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectCommissionPerson',-1,0,'manage','Y',100,'Allow managers to do anything with project commission records for which they are an owner of ie they are a project Person for.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectModificationNote',0,0,'admin','Y',100,'Allow admin to mess with modification notes',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectModificationNote',0,0,'manage','Y',100,'Allow managers to read and create project modification notes',9);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectPerson',-1,0,'manage','Y',100,'Allow managers to read and write project people records for projects they are an owner of.  Ie they are a projectPerson for.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('task',-1,0,'employee','Y',100,'Allow employees to read, update and create tasks that they own (a user owns a task if they are assigned to the task\\\'s project)... added delete too.',287);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('task',0,0,'manage','Y',100,'Allow managers to perform all actions on tasks',287);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('task',0,0,'admin','Y',100,'Allow admin staff to read all tasks',257);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('tfPerson',-1,0,'employee','Y',100,'Allow employees to read tfPerson records that they own (necessary for security checks)',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('tfPerson',0,0,'admin','Y',100,'Allow admin staff to read and write tfPerson records (i.e. determine who has access to which TFs)',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheet',-1,0,'employee','Y',100,'Allow employees to read, write and monitor their own time sheets',31);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('person',-1,0,'','Y',100,'Allow users to read and write their own details',259);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheet',0,0,'admin','Y',100,'Allow admin staff to read, write, invoice and monitor all time sheets',783);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheet',0,0,'manage','Y',100,'Allow managers to read, write, approve and monitor all time sheets',287);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheetItem',-1,0,'','Y',100,NULL,15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheetItem',0,0,'admin','Y',100,'Allow admin staff to read and write all time sheet items',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('transaction',-1,0,'employee','Y',100,'Allow employees to read and write their own transaction records',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('transaction',0,0,'admin','Y',100,'Allow admin staff to read, write and monitor all transaction records',65295);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('announcement',0,0,'admin','Y',100,'Allow admin to perform all operations on all announcements',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('announcement',0,0,'','Y',100,'Allow all users to read all announcements',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('transactionRepeat',-1,0,'employee','Y',100,'Allow employees to read their own repeating transactions',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('transactionRepeat',0,0,'admin','Y',100,'Allow admin staff to do anything with repeating transactions',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('project',-1,0,'manage','Y',100,'Allow managers to read and write all project records that they are a projectperson for.',783);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('skillProficiencys',0,0,'manage','Y',100,'Allow admin do do anything to skillProficiencys',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('skillList',0,0,'admin','Y',100,'Allow all admin to do anything with skillList',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('skillList',0,0,'employee','Y',100,'Allow employees to reed skillList',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('skillProficiencys',0,0,'employee','Y',100,'Allow employees to read all skillProficiencys',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('skillProficiencys',-1,0,'employee','Y',100,'Allow employees to change their own skillProficiencys',14);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('comment',0,0,'','Y',100,'Allow everyone to do anything to comments',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('clientContact',0,0,'','Y',100,'everyone can do everything with client contacts (!)',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('task',0,0,'employee','Y',100,'let people read ALL task records. - removed CREATE perm (2004-11-07 -alex)',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('project',-1,0,'employee','Y',100,'let employees who have permission create new tasks etc.',769);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('reminder',0,0,'','Y',NULL,'Q and Dirty reminders setup, will have to change later probably.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('history',0,0,'','Y',NULL,'allow everyone to create history records.',8);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('loan',0,0,'admin','Y',100,'allow admin to do whatever they want to do with loans.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('person',0,0,'admin','Y',NULL,'Allow admin to do everything with users. (Even change a users role to god!!)',7951);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeSheetItem',0,0,'manage','Y',10,'Allow managers to do everything to all timesheet items (Clancy 19/9/03)',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('commentTemplate',0,0,'manage','Y',NULL,'To let project managers manipulate task comment templates.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('projectPerson',-1,0,'employee','Y',NULL,'To allow employees who are \\\"Project Managers\\\" on a project to add other people etc.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('project',-1,0,'employee','Y',99,'This will hopefully allow projects to administrated by employees with Project Manager perms for a project.',271);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('item',-1,0,'','Y',NULL,'Allow people to do what they like with items they own/have created.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('sentEmailLog',0,0,'','Y',NULL,NULL,15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('absence',0,0,'admin','Y',NULL,'Allow all admin to manipulate all absence records.', 15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('commentTemplate',0,0,'','Y',NULL,'Allow everyone to read commentTemplates',1);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('token',0,0,'','Y',NULL,'Allow everyone to do anything with tokens.',15);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoiceItem',-1,0,'','Y',NULL,'This allows time sheet users to update the related invoice item record.',11);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('invoice',-1,0,'','Y',NULL,'User needs to be able to update invoice because updating an invoiceItem, changes the dates on the invoice itself.',3);
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('timeUnit',0,0,'','Y',NULL,'Allow people to read timeUnit records.',1);

INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, comment, actions) VALUES ('interestedParty',0,0,'','Y',NULL,'Allow people to do anything to interestedParty records.',15);

INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `comment`, `actions`)
VALUES
('product', 0, 0, '', 'Y', 0, 'Users can view product templates', 1),
('product', 0, 0, 'manage', 'Y', 100, 'Manager can manipulate all product templates.', 15),
('productCost', 0, 0, 'manage', 'Y', 100, 'Manager can manipulate all product templates.', 15),
('productCost', 0, 0, '', 'Y', 100, 'Users can view product templates', 1),
('productSale', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales.', 15),
('productSale', 0, 0, '', 'Y', 100, 'Users can view product sales', 1),
('productSaleItem', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales.', 15),
('productSaleItem', 0, 0, '', 'Y', 100, 'Users can view product sales', 1),
('productSaleTransaction', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales', 15),
('productSaleTransaction', 0, 0, '', 'Y', 100, 'Users can view product sales', 1);

--
-- Dumping data for table `config`
--


INSERT INTO config (name, value, type) VALUES ('AllocFromEmailAddress','','text');
INSERT INTO config (name, value, type) VALUES ('cybersourceTfID','0','text');
INSERT INTO config (name, value, type) VALUES ('timeSheetAdminEmail','0','text');
INSERT INTO config (name, value, type) VALUES ('companyName','Cybersource','text');
INSERT INTO config (name, value, type) VALUES ('companyContactPhone','+61 3 9621 2377','text');
INSERT INTO config (name, value, type) VALUES ('companyContactFax','+61 3 9621 2477','text');
INSERT INTO config (name, value, type) VALUES ('companyContactEmail','info@cyber.com.au','text');
INSERT INTO config (name, value, type) VALUES ('companyContactHomePage','http://www.cybersource.com.au','text');
INSERT INTO config (name, value, type) VALUES ('companyContactAddress','Level 4, 10-16 Queen St','text');
INSERT INTO config (name, value, type) VALUES ('companyACN','ACN 053 904 082','text');
INSERT INTO config (name, value, type) VALUES ('hoursInDay','7.5','text');
-- This line has been moved into the install program. 
-- INSERT INTO config (name, value) VALUES ('allocURL','http://change_me_to_your_URL_for_allocPSA/')
INSERT INTO config (name, value, type) VALUES ('companyABN','ABN 13 053 904 082','text');
INSERT INTO config (name, value, type) VALUES ('companyContactAddress2','Melbourne Vic. 3000','text');
INSERT INTO config (name, value, type) VALUES ('companyContactAddress3','Australia','text');
INSERT INTO config (name, value, type) VALUES ('timeSheetPrintFooter','Authorisation (please print):<br><br>Authorisation (signature):<br><br>Date:','text');
INSERT INTO config (name, value, type) VALUES ('taxName','GST','text');
INSERT INTO config (name, value, type) VALUES ('taxPercent','10','text');
INSERT INTO config (name, value, type) VALUES ('companyPercent','28.5','text');
INSERT INTO config (name, value, type) VALUES ('paymentInsurancePercent','10','text');
INSERT INTO config (name, value, type) VALUES ('payrollTaxPercent','5','text');
INSERT INTO config (name, value, type) VALUES ('calendarFirstDay','Sun','text');
INSERT INTO config (name,value,type) VALUES ("timeSheetPrint",'a:3:{i:0;s:24:"timeSheetPrintMode=items";i:1;s:24:"timeSheetPrintMode=units";i:2;s:24:"timeSheetPrintMode=money";}',"array");
INSERT INTO config (name,value,type) VALUES ("timeSheetPrintOptions",'a:10:{s:24:"timeSheetPrintMode=items";s:7:"Default";s:36:"timeSheetPrintMode=items&printDesc=1";s:8:"Default+";s:24:"timeSheetPrintMode=units";s:5:"Units";s:36:"timeSheetPrintMode=units&printDesc=1";s:6:"Units+";s:24:"timeSheetPrintMode=money";s:7:"Invoice";s:36:"timeSheetPrintMode=money&printDesc=1";s:8:"Invoice+";s:36:"timeSheetPrintMode=items&format=html";s:12:"Default Html";s:48:"timeSheetPrintMode=items&format=html&printDesc=1";s:13:"Default Html+";s:27:"timeSheetPrintMode=estimate";s:8:"Estimate";s:39:"timeSheetPrintMode=estimate&printDesc=1";s:9:"Estimate+";}',"array"); 
INSERT INTO config (name,value,type) VALUES ("allocEmailAdmin","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailHost","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailPort","143","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailUsername","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailPassword","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailProtocol","imap","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailFolder","INBOX","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailKeyMethod","headers","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailAddressMethod","to","text");
INSERT INTO config (name,value,type) VALUES ("timeSheetMultipliers", "a:5:{i:1;a:2:{s:5:\"label\";s:13:\"Standard rate\";s:10:\"multiplier\";s:1:\"1\";}i:2;a:2:{s:5:\"label\";s:15:\"Time and a half\";s:10:\"multiplier\";s:3:\"1.5\";}i:3;a:2:{s:5:\"label\";s:11:\"Double time\";s:10:\"multiplier\";s:1:\"2\";}i:4;a:2:{s:5:\"label\";s:11:\"Triple time\";s:10:\"multiplier\";s:1:\"3\";}i:5;a:2:{s:5:\"label\";s:9:\"No charge\";s:10:\"multiplier\";s:1:\"0\";}}", "array");



-- INSERT select, table, tr, td, label, content (ie plaintext), 
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (1,'select','select',1,1,0,0,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (2,'option','option',1,0,1,0,1,"selected",1);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (3,'textarea','textarea',1,0,1,1,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (4,'input_checkbox','input',0,0,0,0,1,"checked",NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (5,'input_text','input',0,0,0,0,1,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (6,'input_hidden','input',0,0,0,0,1,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID,hasLabelValue) VALUES (7,'input_submit','input',0,0,0,0,1,NULL,NULL,1);

-- Insert default attributes for a Select
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"size","1");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"id",NULL);

-- Insert default attributes for an Option
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (2,"value",NULL);

-- Insert default attributes for an Textarea
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"rows","4");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"cols","60");

-- Insert default attributes for an Checkbox
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"value",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"type","checkbox");

-- Insert default attributes for an Textbox
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"size",60);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"type","text");

-- Insert default attributes for an Hidden
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"type","hidden");

-- Insert default attributes for an Submit
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"type","submit");


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
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (2,'day','Days','Daily',27000,1,20);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (3,'week','Weeks','Weekly',135000,1,30);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (4,'month','Months','Monthly',540000,1,40);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (5,'fixed','Fixed Rate','Fixed Rate',0,1,50);

--
-- Dumping data for table `role`
--


INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (1,'Project Manager','isManager', 'project', 30);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (2,'Engineer (edit tasks)','canEditTasks', 'project', 20);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (3,'Project Manager + Time Sheet Recipient','timeSheetRecipient', 'project', 40);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (4,'Super User','god', 'person', 10);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (5,'Finance Admin','admin', 'person', 20);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (6,'Project Manager','manage', 'person', 30);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (7,'Employee','employee','person', 40);


INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ("Add Comments to Task","task","add_comment_from_email");
INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ("Add Comments to Task (via a comment)","comment","add_comment_from_email");



INSERT INTO config (name,value,type) VALUES ("taskPriorities",'a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}',"array");

INSERT INTO config (name,value,type) VALUES ("projectPriorities",'a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}',"array");

INSERT INTO config (name,value,type) VALUES ("defaultInterestedParties",'a:0:{}',"array");
INSERT INTO config (name,value,type) VALUES ("timeSheetManagerEmail", "","text");


