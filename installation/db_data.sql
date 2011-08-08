
-- Info for the new metadata tables

INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Annual Leave',1,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Holiday',2,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Illness',3,true);
INSERT INTO absenceType (absenceTypeID, absenceTypeSeq, absenceTypeActive) VALUES ('Other',4,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('Current',1,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('Potential',2,true);
INSERT INTO clientStatus (clientStatusID, clientStatusSeq, clientStatusActive) VALUES ('Archived',3,true);
INSERT INTO configType (configTypeID, configTypeSeq, configTypeActive) VALUES ('text',1,true);
INSERT INTO configType (configTypeID, configTypeSeq, configTypeActive) VALUES ('array',2,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('edit',1,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('reconcile',2,true);
INSERT INTO invoiceStatus (invoiceStatusID, invoiceStatusSeq, invoiceStatusActive) VALUES ('finished',3,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('cd',1,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('book',2,true);
INSERT INTO itemType (itemTypeID, itemTypeSeq, itemTypeActive) VALUES ('other',3,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('Project',1,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('Contract',2,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('Job',3,true);
INSERT INTO projectType (projectTypeID, projectTypeSeq, projectTypeActive) VALUES ('Prepaid',4,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('Current',1,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('Potential',2,true);
INSERT INTO projectStatus (projectStatusID, projectStatusSeq, projectStatusActive) VALUES ('Archived',3,true);
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
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('project_comments',14,1);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('client_comments',15,1);
INSERT INTO sentEmailType (sentEmailTypeID, sentEmailTypeSeq, sentEmailTypeActive) VALUES ('invoice_comments',16,1);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Novice',1,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Junior',2,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Intermediate',3,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Advanced',4,true);
INSERT INTO skillProficiency (skillProficiencyID, skillProficiencySeq, skillProficiencyActive) VALUES ('Senior',5,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("open_notstarted"  ,"Open: Not Started" ,"#b0d9b0", 10,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("open_inprogress"  ,"Open: In Progress" ,"#66f066", 20,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_info"     ,"Pending: Info"     ,"#f9ca7f", 30,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_manager"  ,"Pending: Manager"  ,"#f9ca7f", 40,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("pending_client"   ,"Pending: Client"   ,"#f9ca7f", 50,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_invalid"   ,"Closed: Invalid"   ,"#e0e0e0", 60,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_duplicate" ,"Closed: Duplicate" ,"#e0e0e0", 70,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_incomplete","Closed: Incomplete","#e0e0e0", 80,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_archived"  ,"Closed: Archived"  ,"#e0e0e0", 85,true);
INSERT INTO taskStatus (taskStatusID, taskStatusLabel, taskStatusColour, taskStatusSeq, taskStatusActive) VALUES ("closed_complete"  ,"Closed: Completed" ,"#e0e0e0", 90,true);
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


-- The default active currencies
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('USD','$','United States dollar','5',true,2);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('AUD','$','Australian dollar','10',true,2);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('GBP','£','British pound','15',true,2);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('CAD','$','Canadian dollar','20',true,2);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('NZD','$','New Zealand dollar','25',true,2);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive, numberToBasic) VALUES ('EUR','€','Euro','30',true,2);

-- The not-active currencies
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AED','د.إ','United Arab Emirates dirham','11200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AFN','؋','Afghan afghani','100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ALL','L','Albanian lek','200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AMD','դր.','Armenian dram','7350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ANG','ƒ','Netherlands Antillean guilder','9600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AOA','Kz','Angolan kwanza','400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ARS','$','Argentine peso','550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AWG','ƒ','Aruban florin','650',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('AZN','m','Azerbaijani manat','850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BAM','KM','Bosnia and Herzegovina convertible mark','1500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BBD','$','Barbadian dollar','1050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BDT','৳','Bangladeshi taka','1000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BGN','лв','Bulgarian lev','1800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BHD','.د.ب','Bahraini dinar','950',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BIF','Fr','Burundian franc','1950',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BMD','$','Bermudian dollar','1300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BND','$','Brunei dollar','9500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BOB','Bs.','Bolivian boliviano','1400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BRL','R$','Brazilian real','1600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BSD','$','Bahamian dollar','900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BTN','Nu.','Bhutanese ngultrum','1350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BWP','P','Botswana pula','1550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BYR','Br','Belarusian ruble','1100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('BZD','$','Belize dollar','1200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CDF','Fr','Congolese franc','2600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CHF','Fr','Swiss franc','6200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CLP','$','Chilean peso','2350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CNY','¥','Chinese yuan','2400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('COP','$','Colombian peso','2500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CRC','₡','Costa Rican colón','2750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CUC','$','Cuban convertible peso','2900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CVE','?','Cape Verdean escudo','2150',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('CZK','Kč','Czech koruna','3050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('DJF','Fr','Djiboutian franc','3150',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('DKK','kr','Danish krone','3750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('DOP','$','Dominican peso','3250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('DZD','د.ج','Algerian dinar','300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('EEK','kr','Estonian kroon','3600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('EGP','ج.م','Egyptian pound','3400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ERN','Nfk','Eritrean nakfa','3550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ETB','Br','Ethiopian birr','3650',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('FJD','$','Fijian dollar','3800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('FKP','£','Falkland Islands pound','3700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GEL','ლ','Georgian lari','4100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GHS','₵','Ghanaian cedi','4200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GIP','£','Gibraltar pound','4250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GMD','D','Gambian dalasi','4050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GNF','Fr','Guinean franc','4500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GTQ','Q','Guatemalan quetzal','4400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('GYD','$','Guyanese dollar','4600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('HKD','$','Hong Kong dollar','4750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('HNL','L','Honduran lempira','4700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('HRK','kn','Croatian kuna','2850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('HTG','G','Haitian gourde','4650',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('HUF','Ft','Hungarian forint','4800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('IDR','Rp','Indonesian rupiah','4950',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ILS','₪','Israeli new sheqel','8200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('INR','?','Indian rupee','4900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('IQD','ع.د','Iraqi dinar','5050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('IRR','﷼','Iranian rial','5000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ISK','kr','Icelandic króna','4850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('JMD','$','Jamaican dollar','5300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('JOD','د.ا','Jordanian dinar','5450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('JPY','¥','Japanese yen','5350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KES','Sh','Kenyan shilling','5550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KGS','лв','Kyrgyzstani som','5850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KHR','៛','Cambodian riel','2000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KMF','Fr','Comorian franc','2550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KPW','₩','North Korean won','5650',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KRW','₩','South Korean won','5700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KWD','د.ك','Kuwaiti dinar','5800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KYD','$','Cayman Islands dollar','2200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('KZT','₸','Kazakhstani tenge','5500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LAK','₭','Lao kip','5900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LBP','ل.ل','Lebanese pound','6000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LKR','Rs','Sri Lankan rupee','10100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LRD','$','Liberian dollar','6100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LSL','L','Lesotho loti','6050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LTL','Lt','Lithuanian litas','6250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LVL','Ls','Latvian lats','5950',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('LYD','ل.د','Libyan dinar','6150',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MAD','د.م.','Moroccan dirham','7250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MDL','L','Moldovan leu','7000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MGA','Ar','Malagasy ariary','6450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MKD','ден','Macedonian denar','6400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MMK','K','Myanma kyat','1900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MNT','₮','Mongolian tögrög','7100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MOP','P','Macanese pataca','6350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MRO','UM','Mauritanian ouguiya','6800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MUR','₨','Mauritian rupee','6850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MVR','ރ.','Maldivian rufiyaa','6600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MWK','MK','Malawian kwacha','6500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MXN','$','Mexican peso','6900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MYR','RM','Malaysian ringgit','6550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('MZN','MTn','Mozambican metical','7300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('NAD','$','Namibian dollar','7400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('NGN','₦','Nigerian naira','7800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('NIO','C$','Nicaraguan córdoba','7700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('NOK','kr','Norwegian krone','8000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('NPR','₨','Nepalese rupee','7500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('OMR','ر.ع.','Omani rial','8050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PAB','B/.','Panamanian balboa','8250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PEN','S/.','Peruvian nuevo sol','8400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PGK','K','Papua New Guinean kina','8300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PHP','₱','Philippine peso','8450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PKR','₨','Pakistani rupee','8100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PLN','zł','Polish złoty','8550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('PYG','₲','Paraguayan guaraní','8350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('QAR','ر.ق','Qatari riyal','8650',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('RON','L','Romanian leu','8700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('RSD','дин.','Serbian dinar','9350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('RUB','р.','Russian ruble','10000',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('RWF','Fr','Rwandan franc','8800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SAR','ر.س','Saudi riyal','9250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SBD','$','Solomon Islands dollar','9750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SCR','₨','Seychellois rupee','9400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SDG','£','Sudanese pound','10150',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SEK','kr','Swedish krona','10300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SHP','£','Saint Helena pound','8900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SLL','Le','Sierra Leonean leone','9450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SOS','Sh','Somali shilling','9800',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SRD','$','Surinamese dollar','10200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('STD','Db','São Tomé and Príncipe dobra','9200',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SVC','₡','Salvadoran colón','3450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SYP','ل.س','Syrian pound','10400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('SZL','L','Swazi lilangeni','10250',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('THB','฿','Thai baht','10600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TJS','ЅМ','Tajikistani somoni','10500',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TMM','m','Turkmenistani manat','10950',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TND','د.ت','Tunisian dinar','10850',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TOP','T$','Tongan paʻanga','10700',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TRY','₤','Turkish lira','7900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TTD','$','Trinidad and Tobago dollar','10750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TWD','$','New Taiwan dollar','10450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('TZS','Sh','Tanzanian shilling','10550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('UAH','₴','Ukrainian hryvnia','11150',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('UGX','Sh','Ugandan shilling','11100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('UYU','$','Uruguayan peso','11350',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('UZS','лв','Uzbekistani som','11400',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('VEF','Bs F','Venezuelan bolívar','11550',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('VND','₫','Vietnamese đồng','11600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('VUV','Vt','Vanuatu vatu','11450',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('WST','T','Samoan tala','9100',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('XAF','Fr','Central African CFA franc','2050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('XCD','$','East Caribbean dollar','9050',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('XOF','Fr','West African CFA franc','9300',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('XPF','Fr','CFP franc','7600',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('YER','﷼','Yemeni rial','11750',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ZAR','R','South African rand','9900',false);
INSERT INTO currencyType (currencyTypeID,currencyTypeLabel,currencyTypeName,currencyTypeSeq,currencyTypeActive) VALUES ('ZMK','ZK','Zambian kwacha','11800',false);


--
-- Dumping data for table permission
--

DELETE FROM permission;
INSERT INTO permission (tableName, entityID, personID, roleName, allow, sortKey, actions, comment)
VALUES

 ('absence'                  ,-1 ,NULL ,'employee' ,true ,NULL ,15    ,NULL)
,('absence'                  ,0  ,NULL ,'manage'   ,true ,NULL ,31    ,NULL)
,('absence'                  ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('announcement'             ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('announcement'             ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('client'                   ,0  ,NULL ,''         ,true ,NULL ,15    ,NULL)
,('clientContact'            ,0  ,NULL ,''         ,true ,NULL ,15    ,NULL)

,('comment'                  ,0  ,NULL ,'employee' ,true ,NULL ,15    ,NULL)
,('comment'                  ,0  ,NULL ,'client'   ,true ,NULL ,15    ,NULL)

,('commentTemplate'          ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('commentTemplate'          ,0  ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)

,('config'                   ,0  ,NULL ,''         ,true ,NULL ,17    ,NULL)
,('config'                   ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('expenseForm'              ,-1 ,NULL ,'employee' ,true ,NULL ,31    ,NULL)
,('expenseForm'              ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('history'                  ,0  ,NULL ,''         ,true ,NULL ,8     ,NULL)

,('indexQueue'               ,0  ,NULL ,'employee' ,true ,NULL ,15    ,NULL)

,('interestedParty'          ,0  ,NULL ,''         ,true ,NULL ,9     ,NULL)
,('interestedParty'          ,0  ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('interestedParty'          ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)
,('interestedParty'          ,-1 ,NULL ,''         ,true ,NULL ,15    ,NULL)

,('invoice'                  ,-1 ,NULL ,''         ,true ,NULL ,3     ,'Update invoiceItem, can change invoice.')
,('invoice'                  ,-1 ,NULL ,'employee' ,true ,NULL ,17    ,NULL)
,('invoice'                  ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('invoiceItem'              ,-1 ,NULL ,''         ,true ,NULL ,11    ,'Update time sheet, can change invoice item.')
,('invoiceItem'              ,-1 ,NULL ,'employee' ,true ,NULL ,17    ,NULL)
,('invoiceItem'              ,0  ,NULL ,'admin'    ,true ,NULL ,271   ,NULL)

,('item'                     ,-1 ,NULL ,''         ,true ,NULL ,15    ,NULL)
,('item'                     ,0  ,NULL ,'employee' ,true ,NULL ,11    ,NULL)
,('item'                     ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('loan'                     ,0  ,NULL ,'employee' ,true ,NULL ,17    ,NULL)
,('loan'                     ,-1 ,NULL ,'employee' ,true ,NULL ,31    ,NULL)
,('loan'                     ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('person'                   ,-1 ,NULL ,''         ,true ,NULL ,259   ,NULL)
,('person'                   ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('person'                   ,0  ,NULL ,'admin'    ,true ,NULL ,7951  ,NULL)

,('product'                  ,0  ,NULL ,''         ,true ,0    ,1     ,NULL)
,('product'                  ,0  ,NULL ,'manage'   ,true ,100  ,15    ,NULL)
,('product'                  ,0  ,NULL ,'admin'    ,true ,100  ,15    ,NULL)

,('productCost'              ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('productCost'              ,0  ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('productCost'              ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('productSale'              ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('productSale'              ,-1 ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('productSale'              ,0  ,NULL ,'admin'    ,true ,NULL ,271   ,NULL)

,('productSaleItem'          ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)
,('productSaleItem'          ,-1 ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('productSaleItem'          ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('project'                  ,0  ,NULL ,''         ,true ,100  ,513   ,'Allow all to read projects for searches.')
,('project'                  ,-1 ,NULL ,'employee' ,true ,100  ,769   ,NULL)
,('project'                  ,-1 ,NULL ,'employee' ,true ,99   ,271   ,NULL)
,('project'                  ,-1 ,NULL ,'manage'   ,true ,100  ,783   ,NULL)
,('project'                  ,0  ,NULL ,'admin'    ,true ,100  ,783   ,NULL)

,('projectPerson'            ,-1 ,NULL ,''         ,true ,NULL ,17    ,NULL)
,('projectPerson'            ,-1 ,NULL ,'employee' ,true ,NULL ,15    ,'Allow employee PMs to add other people.')
,('projectPerson'            ,-1 ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('projectPerson'            ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('projectCommissionPerson'  ,-1 ,NULL ,''         ,true ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,-1 ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('reminder'                 ,0  ,NULL ,''         ,true ,NULL ,15    ,'Will have to change this later?')

,('sentEmailLog'             ,0  ,NULL ,''         ,true ,NULL ,15    ,NULL)

,('skill'                    ,0  ,NULL ,'employee' ,true ,NULL ,1     ,NULL)
,('skill'                    ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('proficiency'              ,0  ,NULL ,'employee' ,true ,NULL ,1     ,NULL)
,('proficiency'              ,-1 ,NULL ,'employee' ,true ,NULL ,14    ,NULL)
,('proficiency'              ,0  ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)

,('task'                     ,-1 ,NULL ,'employee' ,true ,NULL ,287   ,NULL)
,('task'                     ,0  ,NULL ,'employee' ,true ,NULL ,1     ,'Allow read all task records for searches.')
,('task'                     ,0  ,NULL ,'manage'   ,true ,NULL ,287   ,NULL)
,('task'                     ,0  ,NULL ,'admin'    ,true ,NULL ,257   ,NULL)

,('tf'                       ,0  ,NULL ,'employee' ,true ,NULL ,1     ,NULL)
,('tf'                       ,0  ,NULL ,'manage'   ,true ,NULL ,1     ,NULL)
,('tf'                       ,0  ,NULL ,'admin'    ,true ,NULL ,31    ,NULL)

,('tfPerson'                 ,-1 ,NULL ,'employee' ,true ,NULL ,1     ,'Allow employee to read own tfPerson.')
,('tfPerson'                 ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('timeUnit'                 ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)

,('timeSheet'                ,-1 ,NULL ,'employee' ,true ,NULL ,31    ,NULL)
,('timeSheet'                ,0  ,NULL ,'manage'   ,true ,NULL ,287   ,NULL)
,('timeSheet'                ,0  ,NULL ,'admin'    ,true ,NULL ,783   ,NULL)

,('timeSheetItem'            ,-1 ,NULL ,''         ,true ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,NULL ,'manage'   ,true ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('token'                    ,0  ,NULL ,''         ,true ,NULL ,15    ,NULL)
,('tokenAction'              ,0  ,NULL ,''         ,true ,NULL ,1     ,NULL)

,('transaction'              ,-1 ,NULL ,''         ,true ,NULL ,15    ,'Allow everyone to modify PENDING transactions that they own.')
,('transaction'              ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,'Allow admin to do everything with transactions.')

,('transactionRepeat'        ,-1 ,NULL ,'employee' ,true ,NULL ,1     ,NULL)
,('transactionRepeat'        ,0  ,NULL ,'admin'    ,true ,NULL ,15    ,NULL)

,('auditItem'                ,0  ,NULL ,'employee' ,true ,NULL ,8 + 1 ,'Allow employees to create and read audit items.')

;


--
-- Dumping data for table config
--

INSERT INTO tf (tfID,tfName,tfActive) VALUES (1,"Main Funds",1);
INSERT INTO tf (tfID,tfName,tfActive) VALUES (2,"Incoming Funds",1);
INSERT INTO tf (tfID,tfName,tfActive) VALUES (3,"Outgoing Funds",1);
INSERT INTO tf (tfID,tfName,tfActive) VALUES (4,"Expense Funds",1);
INSERT INTO tf (tfID,tfName,tfActive) VALUES (5,"Tax Funds",1);

INSERT INTO config (name, value, type) VALUES ('currency', '', 'text');
INSERT INTO config (name, value, type) VALUES ('AllocFromEmailAddress','','text');
INSERT INTO config (name, value, type) VALUES ('mainTfID','1','text');
INSERT INTO config (name, value, type) VALUES ('companyName','Your Business Here','text');
INSERT INTO config (name, value, type) VALUES ('companyContactPhone','+61 3 9621 2377','text');
INSERT INTO config (name, value, type) VALUES ('companyContactFax','+61 3 9621 2477','text');
INSERT INTO config (name, value, type) VALUES ('companyContactEmail','info@cyber.com.au','text');
INSERT INTO config (name, value, type) VALUES ('companyContactHomePage','http://www.cyber.com.au','text');
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
INSERT INTO config (name, value, type) VALUES ('taxTfID', '5', 'text');
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
INSERT INTO config (name,value,type) VALUES ('allocEmailExtra','/notls/norsh','text');
INSERT INTO config (name,value,type) VALUES ('taskPriorities','a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}','array');

INSERT INTO config (name,value,type) VALUES ('projectPriorities','a:5:{i:1;a:2:{s:5:"label";s:8:"Critical";s:6:"colour";s:7:"#ff0000";}i:2;a:2:{s:5:"label";s:9:"Important";s:6:"colour";s:7:"#ff7200";}i:3;a:2:{s:5:"label";s:6:"Normal";s:6:"colour";s:7:"#333333";}i:4;a:2:{s:5:"label";s:5:"Minor";s:6:"colour";s:7:"#666666";}i:5;a:2:{s:5:"label";s:8:"Wishlist";s:6:"colour";s:7:"#999999";}}','array');

INSERT INTO config (name,value,type) VALUES ('defaultInterestedParties','a:0:{}','array');
INSERT INTO config (name,value,type) VALUES ('inTfID','2','text');
INSERT INTO config (name,value,type) VALUES ('outTfID','3','text');
INSERT INTO config (name,value,type) VALUES ('expenseFormTfID','4','text');

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
INSERT INTO config (name,value,type) VALUES ('allocSessionMinutes', '540', 'text');
INSERT INTO config (name,value,type) VALUES ('timeSheetEditors', 'none', 'text');
INSERT INTO config (name,value,type) VALUES ('mapURL', 'http://maps.google.com.au/?q=%ad', 'text');





--
-- Dumping data for table taskType
--


INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Task'     ,10,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Parent'   ,20,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Message'  ,30,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Fault'    ,40,true);
INSERT INTO taskType (taskTypeID, taskTypeSeq, taskTypeActive) VALUES ('Milestone',50,true);

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

-- Has to be tokenActionID of 2 for reverse compatibility 
INSERT INTO tokenAction (tokenActionID,tokenAction,tokenActionType,tokenActionMethod) VALUES (2,'Add Comments to Comment','comment','add_comment_from_email');





