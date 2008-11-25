--
-- Dumping data for table `permission`
--

DELETE FROM permission;
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `actions`, `comment`)
VALUES

 ('absence'                  ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('absence'                  ,0  ,0 ,'manage'   ,'Y' ,NULL ,31    ,NULL)
,('absence'                  ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('announcement'             ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('announcement'             ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('client'                   ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('clientContact'            ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('comment'                  ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('commentTemplate'          ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('commentTemplate'          ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('config'                   ,0  ,0 ,''         ,'Y' ,NULL ,17    ,NULL)
,('config'                   ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('expenseForm'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('expenseForm'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('history'                  ,0  ,0 ,''         ,'Y' ,NULL ,8     ,NULL)

,('interestedParty'          ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('invoice'                  ,-1 ,0 ,''         ,'Y' ,NULL ,3     ,'Update invoiceItem, can change invoice.')
,('invoice'                  ,-1 ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoice'                  ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('invoiceItem'              ,-1 ,0 ,''         ,'Y' ,NULL ,11    ,'Update time sheet, can change invoice item.')
,('invoiceItem'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoiceItem'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('item'                     ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('item'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,11    ,NULL)
,('item'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('loan'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('loan'                     ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('loan'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('person'                   ,-1 ,0 ,''         ,'Y' ,NULL ,259   ,NULL)
,('person'                   ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('person'                   ,0  ,0 ,'admin'    ,'Y' ,NULL ,7951  ,NULL)

,('product'                  ,0  ,0 ,''         ,'Y' ,0    ,1     ,NULL)
,('product'                  ,0  ,0 ,'manage'   ,'Y' ,100  ,15    ,NULL)
,('product'                  ,0  ,0 ,'admin'    ,'Y' ,100  ,15    ,NULL)

,('productCost'              ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productCost'              ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productCost'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('productSale'              ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSale'              ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSale'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('productSaleItem'          ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSaleItem'          ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSaleItem'          ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('project'                  ,0  ,0 ,''         ,'Y' ,100  ,513   ,'Allow all to read projects for searches.')
,('project'                  ,-1 ,0 ,'employee' ,'Y' ,100  ,769   ,NULL)
,('project'                  ,-1 ,0 ,'employee' ,'Y' ,99   ,271   ,NULL)
,('project'                  ,-1 ,0 ,'manage'   ,'Y' ,100  ,783   ,NULL)
,('project'                  ,0  ,0 ,'admin'    ,'Y' ,100  ,783   ,NULL)

,('projectPerson'            ,-1 ,0 ,''         ,'Y' ,NULL ,17    ,NULL)
,('projectPerson'            ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,'Allow employee PMs to add other people.')
,('projectPerson'            ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectPerson'            ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('projectCommissionPerson'  ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('reminder'                 ,0  ,0 ,''         ,'Y' ,NULL ,15    ,'Will have to change this later?')

,('savedView'                ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('sentEmailLog'             ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('skillList'                ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('skillList'                ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('skillProficiencys'        ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('skillProficiencys'        ,-1 ,0 ,'employee' ,'Y' ,NULL ,14    ,NULL)
,('skillProficiencys'        ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('task'                     ,-1 ,0 ,'employee' ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,'Allow read all task records for searches.')
,('task'                     ,0  ,0 ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,257   ,NULL)

,('taskType'                 ,0  ,0 ,''         ,'Y' ,NULL ,17    ,NULL)

,('tf'                       ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('tf'                       ,0  ,0 ,'manage'   ,'Y' ,NULL ,1     ,NULL)
,('tf'                       ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('tfPerson'                 ,-1 ,0 ,'employee' ,'Y' ,NULL ,1     ,'Allow employee to read own tfPerson.')
,('tfPerson'                 ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('timeUnit'                 ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)

,('timeSheet'                ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('timeSheet'                ,0  ,0 ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('timeSheet'                ,0  ,0 ,'admin'    ,'Y' ,NULL ,783   ,NULL)

,('timeSheetItem'            ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('token'                    ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('transaction'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('transaction'              ,0  ,0 ,'manage'   ,'Y' ,NULL ,8192  ,"Manager create pending transaction.")
,('transaction'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,65295 ,NULL)

,('transactionRepeat'        ,-1 ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('transactionRepeat'        ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

;


--
-- Dumping data for table `config`
--


INSERT INTO config (name, value, type) VALUES ('AllocFromEmailAddress','','text');
INSERT INTO config (name, value, type) VALUES ('mainTfID','','text');
INSERT INTO config (name, value, type) VALUES ('timeSheetAdminEmail','0','text');
INSERT INTO config (name, value, type) VALUES ('companyName','Your Business Here','text');
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
INSERT INTO config (name, value, type) VALUES ('taxTfID', '0', 'text');
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
INSERT INTO config (name,value,type) VALUES ("taskBlockers", "a:4:{i:0;a:2:{s:5:\"label\";s:20:\"Available to work on\";s:4:\"icon\";s:18:\"icon_orb_green.png\";}i:1;a:2:{s:5:\"label\";s:20:\"Waiting for customer\";s:4:\"icon\";s:16:\"icon_orb_red.png\";}i:2;a:2:{s:5:\"label\";s:23:\"Waiting for information\";s:4:\"icon\";s:16:\"icon_orb_red.png\";}i:3;a:2:{s:5:\"label\";s:16:\"Awaiting manager\";s:4:\"icon\";s:19:\"icon_orb_yellow.png\";}}", "array");

INSERT INTO config (name,value,type) VALUES ("taskPriorities",'a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}',"array");

INSERT INTO config (name,value,type) VALUES ("projectPriorities",'a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}',"array");

INSERT INTO config (name,value,type) VALUES ("defaultInterestedParties",'a:0:{}',"array");
INSERT INTO config (name,value,type) VALUES ("timeSheetManagerEmail", "","text");
INSERT INTO config (name,value,type) VALUES ("task_email_header", "","text");
INSERT INTO config (name,value,type) VALUES ("task_email_footer", "","text");
INSERT INTO config (name,value,type) VALUES ("outTfID","","text");
INSERT INTO config (name,value,type) VALUES ("inTfID","","text");

INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_taskComment", "Task Comment: %ti %tn [%tp]", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_dailyDigest", "Daily Digest", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetToManager", "Time sheet %mi submitted for your approval", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetFromManager", "Time sheet %mi rejected by manager", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetFromAdministrator", "Time sheet %mi rejected by administrator", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetToAdministrator", "Time sheet %mi submitted for your approval", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetCompleted", "Time sheet %mi completed", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderClient", "Client Reminder: %li %cc", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderProject", "Project Reminder: %pi %pn", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderTask", "Task Reminder: %ti %tn [%tp]", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderOther", "Reminder: ", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("wikiMarkup", "Markdown","text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("wikiVCS", "git","text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("singleSession","1","text");








--
-- Dumping data for table `taskType`
--


INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (1,'Task',1,10);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (2,'Parent',1,20);
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
INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ("Add Comments to Comment","comment","add_comment_from_email");





