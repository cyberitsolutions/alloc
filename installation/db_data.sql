
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
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('insurance',7,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('tax',8,true);
INSERT INTO transactionType (transactionTypeID, transactionTypeSeq, transactionTypeActive) VALUES ('sale',9,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('edit',1,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('allocate',2,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('admin',3,true);
INSERT INTO productSaleStatus (productSaleStatusID, productSaleStatusSeq, productSaleStatusActive) VALUES ('finished',4,true);
INSERT INTO timeSheetItemMultiplier (timeSheetItemMultiplierID, timeSheetItemMultiplierName, timeSheetItemMultiplierSeq, timeSheetItemMultiplierActive) VALUES (1.00,'Standard rate',1,true);
INSERT INTO timeSheetItemMultiplier (timeSheetItemMultiplierID, timeSheetItemMultiplierName, timeSheetItemMultiplierSeq, timeSheetItemMultiplierActive) VALUES (1.50,'Time and a half',2,true);
INSERT INTO timeSheetItemMultiplier (timeSheetItemMultiplierID, timeSheetItemMultiplierName, timeSheetItemMultiplierSeq, timeSheetItemMultiplierActive) VALUES (2.00,'Double time',3,true);
INSERT INTO timeSheetItemMultiplier (timeSheetItemMultiplierID, timeSheetItemMultiplierName, timeSheetItemMultiplierSeq, timeSheetItemMultiplierActive) VALUES (3.00,'Triple time',4,true);
INSERT INTO timeSheetItemMultiplier (timeSheetItemMultiplierID, timeSheetItemMultiplierName, timeSheetItemMultiplierSeq, timeSheetItemMultiplierActive) VALUES (0,'No charge',5,true);






--
-- Dumping data for table permission
--

DELETE FROM permission;
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, actions, comment)
VALUES

 ('absence'                  ,-1 ,NULL ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('absence'                  ,0  ,NULL ,'manage'   ,'Y' ,NULL ,31    ,NULL)
,('absence'                  ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('announcement'             ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('announcement'             ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('client'                   ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)
,('clientContact'            ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)

,('comment'                  ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)

,('commentTemplate'          ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('commentTemplate'          ,0  ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('config'                   ,0  ,NULL ,''         ,'Y' ,NULL ,17    ,NULL)
,('config'                   ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('expenseForm'              ,-1 ,NULL ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('expenseForm'              ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('history'                  ,0  ,NULL ,''         ,'Y' ,NULL ,8     ,NULL)

,('interestedParty'          ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)

,('invoice'                  ,-1 ,NULL ,''         ,'Y' ,NULL ,3     ,'Update invoiceItem, can change invoice.')
,('invoice'                  ,-1 ,NULL ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoice'                  ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('invoiceItem'              ,-1 ,NULL ,''         ,'Y' ,NULL ,11    ,'Update time sheet, can change invoice item.')
,('invoiceItem'              ,-1 ,NULL ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoiceItem'              ,0  ,NULL ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('item'                     ,-1 ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)
,('item'                     ,0  ,NULL ,'employee' ,'Y' ,NULL ,11    ,NULL)
,('item'                     ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('loan'                     ,0  ,NULL ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('loan'                     ,-1 ,NULL ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('loan'                     ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('person'                   ,-1 ,NULL ,''         ,'Y' ,NULL ,259   ,NULL)
,('person'                   ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('person'                   ,0  ,NULL ,'admin'    ,'Y' ,NULL ,7951  ,NULL)

,('product'                  ,0  ,NULL ,''         ,'Y' ,0    ,1     ,NULL)
,('product'                  ,0  ,NULL ,'manage'   ,'Y' ,100  ,15    ,NULL)
,('product'                  ,0  ,NULL ,'admin'    ,'Y' ,100  ,15    ,NULL)

,('productCost'              ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('productCost'              ,0  ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productCost'              ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('productSale'              ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSale'              ,-1 ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSale'              ,0  ,NULL ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('productSaleItem'          ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)
,('productSaleItem'          ,-1 ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('productSaleItem'          ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('project'                  ,0  ,NULL ,''         ,'Y' ,100  ,513   ,'Allow all to read projects for searches.')
,('project'                  ,-1 ,NULL ,'employee' ,'Y' ,100  ,769   ,NULL)
,('project'                  ,-1 ,NULL ,'employee' ,'Y' ,99   ,271   ,NULL)
,('project'                  ,-1 ,NULL ,'manage'   ,'Y' ,100  ,783   ,NULL)
,('project'                  ,0  ,NULL ,'admin'    ,'Y' ,100  ,783   ,NULL)

,('projectPerson'            ,-1 ,NULL ,''         ,'Y' ,NULL ,17    ,NULL)
,('projectPerson'            ,-1 ,NULL ,'employee' ,'Y' ,NULL ,15    ,'Allow employee PMs to add other people.')
,('projectPerson'            ,-1 ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectPerson'            ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('projectCommissionPerson'  ,-1 ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,-1 ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('reminder'                 ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,'Will have to change this later?')

,('sentEmailLog'             ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)

,('skill'                    ,0  ,NULL ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('skill'                    ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('proficiency'              ,0  ,NULL ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('proficiency'              ,-1 ,NULL ,'employee' ,'Y' ,NULL ,14    ,NULL)
,('proficiency'              ,0  ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('task'                     ,-1 ,NULL ,'employee' ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,NULL ,'employee' ,'Y' ,NULL ,1     ,'Allow read all task records for searches.')
,('task'                     ,0  ,NULL ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,NULL ,'admin'    ,'Y' ,NULL ,257   ,NULL)

,('taskType'                 ,0  ,NULL ,''         ,'Y' ,NULL ,17    ,NULL)

,('tf'                       ,0  ,NULL ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('tf'                       ,0  ,NULL ,'manage'   ,'Y' ,NULL ,1     ,NULL)
,('tf'                       ,0  ,NULL ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('tfPerson'                 ,-1 ,NULL ,'employee' ,'Y' ,NULL ,1     ,'Allow employee to read own tfPerson.')
,('tfPerson'                 ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('timeUnit'                 ,0  ,NULL ,''         ,'Y' ,NULL ,1     ,NULL)

,('timeSheet'                ,-1 ,NULL ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('timeSheet'                ,0  ,NULL ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('timeSheet'                ,0  ,NULL ,'admin'    ,'Y' ,NULL ,783   ,NULL)

,('timeSheetItem'            ,-1 ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,NULL ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('token'                    ,0  ,NULL ,''         ,'Y' ,NULL ,15    ,NULL)

,('transaction'              ,-1 ,NULL ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('transaction'              ,0  ,NULL ,'manage'   ,'Y' ,NULL ,8192  ,'Manager create pending transaction.')
,('transaction'              ,0  ,NULL ,'admin'    ,'Y' ,NULL ,65295 ,NULL)

,('transactionRepeat'        ,-1 ,NULL ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('transactionRepeat'        ,0  ,NULL ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('auditItem'                ,0  ,NULL ,'employee' ,'Y', NULL, 8 + 1 ,'Allow employees to create and read audit items.')

;


--
-- Dumping data for table config
--


INSERT INTO config (name, value, type) VALUES ('AllocFromEmailAddress','','text');
INSERT INTO config (name, value, type) VALUES ('mainTfID','','text');
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
INSERT INTO config (name,value,type) VALUES ('timeSheetPrint','a:3:{i:0;s:24:"timeSheetPrintMode=items";i:1;s:24:"timeSheetPrintMode=units";i:2;s:24:"timeSheetPrintMode=money";}','array');
INSERT INTO config (name,value,type) VALUES ('timeSheetPrintOptions','a:10:{s:24:"timeSheetPrintMode=items";s:7:"Default";s:36:"timeSheetPrintMode=items&printDesc=1";s:8:"Default+";s:24:"timeSheetPrintMode=units";s:5:"Units";s:36:"timeSheetPrintMode=units&printDesc=1";s:6:"Units+";s:24:"timeSheetPrintMode=money";s:7:"Invoice";s:36:"timeSheetPrintMode=money&printDesc=1";s:8:"Invoice+";s:36:"timeSheetPrintMode=items&format=html";s:12:"Default Html";s:48:"timeSheetPrintMode=items&format=html&printDesc=1";s:13:"Default Html+";s:27:"timeSheetPrintMode=estimate";s:8:"Estimate";s:39:"timeSheetPrintMode=estimate&printDesc=1";s:9:"Estimate+";}','array'); 
INSERT INTO config (name,value,type) VALUES ('allocEmailAdmin','','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailHost','','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailPort','143','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailUsername','','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailPassword','','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailProtocol','imap','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailFolder','INBOX','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailKeyMethod','headers','text');
INSERT INTO config (name,value,type) VALUES ('allocEmailAddressMethod','to','text');
INSERT INTO config (name,value,type) VALUES ('taskPriorities','a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}','array');

INSERT INTO config (name,value,type) VALUES ('projectPriorities','a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}','array');

INSERT INTO config (name,value,type) VALUES ('taskStatusOptions','a:3:{s:4:"open";a:2:{s:10:"notstarted";a:2:{s:5:"label";s:11:"Not Started";s:6:"colour";s:25:"background-color:#8fe78f;";}s:10:"inprogress";a:2:{s:5:"label";s:11:"In Progress";s:6:"colour";s:25:"background-color:#8fe78f;";}}s:7:"pending";a:3:{s:4:"info";a:2:{s:5:"label";s:4:"Info";s:6:"colour";s:25:"background-color:#f9ca7f;";}s:7:"manager";a:2:{s:5:"label";s:7:"Manager";s:6:"colour";s:25:"background-color:#f9ca7f;";}s:6:"client";a:2:{s:5:"label";s:6:"Client";s:6:"colour";s:25:"background-color:#f9ca7f;";}}s:6:"closed";a:4:{s:7:"invalid";a:2:{s:5:"label";s:7:"Invalid";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:9:"duplicate";a:2:{s:5:"label";s:9:"Duplicate";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:10:"incomplete";a:2:{s:5:"label";s:10:"Incomplete";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:8:"complete";a:2:{s:5:"label";s:9:"Completed";s:6:"colour";s:25:"background-color:#e0e0e0;";}}}', 'array');

INSERT INTO config (name,value,type) VALUES ('defaultInterestedParties','a:0:{}','array');
INSERT INTO config (name,value,type) VALUES ('task_email_header', '','text');
INSERT INTO config (name,value,type) VALUES ('task_email_footer', '','text');
INSERT INTO config (name,value,type) VALUES ('outTfID','','text');
INSERT INTO config (name,value,type) VALUES ('inTfID','','text');

INSERT INTO config (name,value,type) VALUES ('emailSubject_taskComment', '[allocPSA] Task Comment: %ti %tn [%tp]', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_dailyDigest', '[allocPSA] Daily Digest', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_timeSheetToManager', '[allocPSA] Time sheet %ti submitted for your approval', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_timeSheetFromManager', '[allocPSA] Time sheet %ti rejected by manager', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_timeSheetFromAdministrator', '[allocPSA] Time sheet %ti rejected by administrator', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_timeSheetToAdministrator', '[allocPSA] Time sheet %ti submitted for your approval', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_timeSheetCompleted', '[allocPSA] Time sheet %ti completed', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_reminderClient', '[allocPSA] Client Reminder: %li %cc', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_reminderProject', '[allocPSA] Project Reminder: %pi %pn', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_reminderTask', '[allocPSA] Task Reminder: %ti %tn [%tp]', 'text');
INSERT INTO config (name,value,type) VALUES ('emailSubject_reminderOther', '[allocPSA] Reminder: ', 'text');
INSERT INTO config (name,value,type) VALUES ('wikiMarkup', 'Markdown','text');
INSERT INTO config (name,value,type) VALUES ('wikiVCS', 'git','text');
INSERT INTO config (name,value,type) VALUES ('singleSession','1','text');
INSERT INTO config (name, value, type) VALUES ('clientCategories','a:7:{i:0;a:2:{s:5:"label";s:6:"Client";s:5:"value";i:1;}i:1;a:2:{s:5:"label";s:6:"Vendor";s:5:"value";i:2;}i:2;a:2:{s:5:"label";s:8:"Supplier";s:5:"value";i:3;}i:3;a:2:{s:5:"label";s:10:"Consultant";s:5:"value";i:4;}i:4;a:2:{s:5:"label";s:10:"Government";s:5:"value";i:5;}i:5;a:2:{s:5:"label";s:10:"Non-profit";s:5:"value";i:6;}i:6;a:2:{s:5:"label";s:8:"Internal";s:5:"value";i:7;}}','array');
INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetManagerList', 'a:0:{}', 'array');
INSERT INTO config (name,value,type) VALUES ('defaultTimeSheetAdminList', 'a:0:{}', 'array');





--
-- Dumping data for table taskType
--


INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (1,'Task',true,10);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (2,'Parent',true,20);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (3,'Message',true,30);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (4,'Fault',true,40);
INSERT INTO taskType (taskTypeID, taskTypeName, taskTypeActive, taskTypeSequence) VALUES (5,'Milestone',true,50);

--
-- Dumping data for table timeUnit
--


INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (1,'hour','Hours','Hourly',3600,true,10);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (2,'day','Days','Daily',27000,true,20);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (3,'week','Weeks','Weekly',135000,true,30);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (4,'month','Months','Monthly',540000,true,40);
INSERT INTO timeUnit (timeUnitID, timeUnitName, timeUnitLabelA, timeUnitLabelB, timeUnitSeconds, timeUnitActive, timeUnitSequence) VALUES (5,'fixed','Fixed Rate','Fixed Rate',0,true,50);

--
-- Dumping data for table role
--


INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (1,'Project Manager','isManager', 'project', 30);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (2,'Engineer (edit tasks)','canEditTasks', 'project', 20);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (3,'Project Manager + Time Sheet Recipient','timeSheetRecipient', 'project', 40);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (4,'Super User','god', 'person', 10);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (5,'Finance Admin','admin', 'person', 20);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (6,'Project Manager','manage', 'person', 30);
INSERT INTO role (roleID, roleName, roleHandle, roleLevel, roleSequence) VALUES (7,'Employee','employee','person', 40);


INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ('Add Comments to Task','task','add_comment_from_email');
INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ('Add Comments to Comment','comment','add_comment_from_email');





