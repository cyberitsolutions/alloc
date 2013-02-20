
-- Indexes to speed up querying
CREATE INDEX clientName ON client (clientName);
CREATE INDEX clientID ON clientContact (clientID);
CREATE INDEX commentMaster ON comment (commentMaster);
CREATE INDEX commentMasterID ON comment (commentMasterID);
CREATE INDEX commentLinkID ON comment (commentLinkID);
CREATE INDEX commentType ON comment (commentType);
CREATE INDEX commentCreatedTime ON comment (commentCreatedTime);
CREATE INDEX idx_personID ON history (personID);
CREATE INDEX idx_invoiceID ON invoiceItem (invoiceID);
CREATE INDEX idx_invoiceRepeatID ON invoice (invoiceRepeatID);
CREATE INDEX tableName ON permission (tableName);
CREATE INDEX projectName ON project (projectName);
CREATE INDEX idx_clientID ON project (clientID);
CREATE INDEX idx_person_project ON projectPerson (projectID,personID);
CREATE INDEX taskName ON task (taskName);
CREATE INDEX projectID ON task (projectID);
CREATE INDEX parentTaskID ON task (parentTaskID);
CREATE INDEX taskTypeID ON task (taskTypeID);
CREATE INDEX taskStatus ON task (taskStatus);
CREATE INDEX dateCreated ON task (dateCreated);
CREATE INDEX idx_entityName ON auditItem (entityName);
CREATE INDEX idx_entityID ON auditItem (entityID);
CREATE INDEX idx_tfPerson_tfID ON tfPerson (tfID);
CREATE INDEX idx_timeSheetItem_timeSheetID ON timeSheetItem (timeSheetID);
CREATE INDEX idx_taskID ON timeSheetItem (taskID);
CREATE INDEX dateTimeSheetItem ON timeSheetItem (dateTimeSheetItem);
CREATE INDEX idx_tsiHinttaskID ON tsiHint (taskID);
CREATE INDEX idx_tsiHintDate ON tsiHint (date);
CREATE INDEX idx_transaction_timeSheetID ON transaction (timeSheetID);
CREATE INDEX idx_transaction_tfID ON transaction (tfID);
CREATE INDEX idx_invoiceItemID ON transaction (invoiceItemID);
CREATE INDEX idx_fromTfID ON transaction (fromTfID);
CREATE INDEX idx_productSaleID ON transaction (productSaleID);
CREATE INDEX idx_productSaleItemID ON transaction (productSaleItemID);
CREATE INDEX idx_productCostID ON transaction (productCostID);
CREATE INDEX idx_transactionGroupID ON transaction (transactionGroupID);
CREATE INDEX idx_interestedParty_entityID ON interestedParty (entityID);

-- Unique key constraints
CREATE UNIQUE INDEX name ON config (name);
CREATE UNIQUE INDEX invoiceNum ON invoice (invoiceNum);
CREATE UNIQUE INDEX username ON person (username);
CREATE UNIQUE INDEX tokenHash ON token (tokenHash);
CREATE UNIQUE INDEX commentEmailUID ON comment (commentEmailUID);
CREATE UNIQUE INDEX date_currency ON exchangeRate (exchangeRateCreatedDate,fromCurrency,toCurrency);
CREATE UNIQUE INDEX entity_entityID ON indexQueue (entity,entityID);

-- Add the referential integrity to the lookup/metadata tables. These are
-- all ON UPDATE CASCADE, so that changes in the lookup tables are reflected with
-- changes in the transaction data
ALTER TABLE absence ADD CONSTRAINT absence_absenceType FOREIGN KEY (absenceType) REFERENCES absenceType (absenceTypeID) ON UPDATE CASCADE;
ALTER TABLE absence ADD CONSTRAINT absence_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE client ADD CONSTRAINT client_clientStatus FOREIGN KEY (clientStatus) REFERENCES clientStatus (clientStatusID) ON UPDATE CASCADE;
ALTER TABLE config ADD CONSTRAINT config_configType FOREIGN KEY (type) REFERENCES configType (configTypeID) ON UPDATE CASCADE;
ALTER TABLE invoice ADD CONSTRAINT invoice_invoiceStatus FOREIGN KEY (invoiceStatus) REFERENCES invoiceStatus (invoiceStatusID) ON UPDATE CASCADE;
ALTER TABLE item ADD CONSTRAINT item_itemType FOREIGN KEY (itemType) REFERENCES itemType (itemTypeID) ON UPDATE CASCADE;
ALTER TABLE project ADD CONSTRAINT project_projectType FOREIGN KEY (projectType) REFERENCES projectType (projectTypeID) ON UPDATE CASCADE;
ALTER TABLE project ADD CONSTRAINT project_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE project ADD CONSTRAINT project_projectStatus FOREIGN KEY (projectStatus) REFERENCES projectStatus (projectStatusID) ON UPDATE CASCADE;
ALTER TABLE role ADD CONSTRAINT role_roleLevel FOREIGN KEY (roleLevel) REFERENCES roleLevel (roleLevelID) ON UPDATE CASCADE;
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderRecuringInterval FOREIGN KEY (reminderRecuringInterval) REFERENCES reminderRecuringInterval (reminderRecuringIntervalID) ON UPDATE CASCADE;
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderAdvNoticeInterval FOREIGN KEY (reminderAdvNoticeInterval) REFERENCES reminderAdvNoticeInterval (reminderAdvNoticeIntervalID) ON UPDATE CASCADE;
ALTER TABLE sentEmailLog ADD CONSTRAINT sentEmailLog_sentEmailType FOREIGN KEY (sentEmailType) REFERENCES sentEmailType (sentEmailTypeID) ON UPDATE CASCADE;
ALTER TABLE proficiency ADD CONSTRAINT proficiency_skillProficiency FOREIGN KEY (skillProficiency) REFERENCES skillProficiency (skillProficiencyID) ON UPDATE CASCADE;
ALTER TABLE auditItem ADD CONSTRAINT auditItem_changeType FOREIGN KEY (changeType) REFERENCES changeType (changeTypeID) ON UPDATE CASCADE;
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_status FOREIGN KEY (status) REFERENCES timeSheetStatus (timeSheetStatusID) ON UPDATE CASCADE;
ALTER TABLE transaction ADD CONSTRAINT transaction_status FOREIGN KEY (status) REFERENCES transactionStatus (transactionStatusID) ON UPDATE CASCADE;
ALTER TABLE transaction ADD CONSTRAINT transaction_transactionType FOREIGN KEY (transactionType) REFERENCES transactionType (transactionTypeID) ON UPDATE CASCADE;
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_transactionType FOREIGN KEY (transactionType) REFERENCES transactionType (transactionTypeID) ON UPDATE CASCADE;
ALTER TABLE productSale ADD CONSTRAINT productSale_status FOREIGN KEY (status) REFERENCES productSaleStatus (productSaleStatusID) ON UPDATE CASCADE;

-- Add the regular foreign key constraints
ALTER TABLE announcement ADD CONSTRAINT announcement_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE client ADD CONSTRAINT client_clientModifiedUser FOREIGN KEY (clientModifiedUser) REFERENCES person (personID);
ALTER TABLE clientContact ADD CONSTRAINT clientContact_clientID FOREIGN KEY (clientID) REFERENCES client (clientID);
ALTER TABLE comment ADD CONSTRAINT comment_commentCreatedUser FOREIGN KEY (commentCreatedUser) REFERENCES person (personID);
ALTER TABLE comment ADD CONSTRAINT comment_commentModifiedUser FOREIGN KEY (commentModifiedUser) REFERENCES person (personID);
ALTER TABLE comment ADD CONSTRAINT comment_commentCreatedUserClientContactID FOREIGN KEY (commentCreatedUserClientContactID) REFERENCES clientContact (clientContactID);
ALTER TABLE expenseForm ADD CONSTRAINT expenseForm_clientID FOREIGN KEY (clientID) REFERENCES client (clientID);
ALTER TABLE expenseForm ADD CONSTRAINT expenseForm_expenseFormModifiedUser FOREIGN KEY (expenseFormModifiedUser) REFERENCES person (personID);
ALTER TABLE expenseForm ADD CONSTRAINT expenseForm_expenseFormCreatedUser FOREIGN KEY (expenseFormCreatedUser) REFERENCES person (personID);
ALTER TABLE expenseForm ADD CONSTRAINT expenseForm_transactionRepeatID FOREIGN KEY (transactionRepeatID) REFERENCES transactionRepeat (transactionRepeatID);
ALTER TABLE history ADD CONSTRAINT history_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE invoice ADD CONSTRAINT invoice_clientID FOREIGN KEY (clientID) REFERENCES client (clientID);
ALTER TABLE invoice ADD CONSTRAINT invoice_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE invoice ADD CONSTRAINT invoice_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE invoice ADD CONSTRAINT invoice_invoiceRepeatID FOREIGN KEY (invoiceRepeatID) REFERENCES invoiceRepeat (invoiceRepeatID);
ALTER TABLE invoice ADD CONSTRAINT invoice_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_invoiceID FOREIGN KEY (invoiceID) REFERENCES invoice (invoiceID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_timeSheetID FOREIGN KEY (timeSheetID) REFERENCES timeSheet (timeSheetID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_timeSheetItemID FOREIGN KEY (timeSheetItemID) REFERENCES timeSheetItem (timeSheetItemID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_expenseFormID FOREIGN KEY (expenseFormID) REFERENCES expenseForm (expenseFormID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_transactionID FOREIGN KEY (transactionID) REFERENCES transaction (transactionID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_productSaleID FOREIGN KEY (productSaleID) REFERENCES productSale (productSaleID);
ALTER TABLE invoiceItem ADD CONSTRAINT invoiceItem_productSaleItemID FOREIGN KEY (productSaleItemID) REFERENCES productSaleItem (productSaleItemID);
ALTER TABLE invoiceRepeat ADD CONSTRAINT invoiceRepeat_invoiceID FOREIGN KEY (invoiceID) REFERENCES invoice (invoiceID);
ALTER TABLE invoiceRepeat ADD CONSTRAINT invoiceRepeat_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE invoiceRepeatDate ADD CONSTRAINT invoiceRepeat_invoiceRepeatID FOREIGN KEY (invoiceRepeatID) REFERENCES invoiceRepeat (invoiceRepeatID);
ALTER TABLE item ADD CONSTRAINT item_itemModifiedUser FOREIGN KEY (itemModifiedUser) REFERENCES person (personID);
ALTER TABLE item ADD CONSTRAINT item_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE loan ADD CONSTRAINT loan_itemID FOREIGN KEY (itemID) REFERENCES item (itemID);
ALTER TABLE loan ADD CONSTRAINT loan_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE loan ADD CONSTRAINT loan_loanModifiedUser FOREIGN KEY (loanModifiedUser) REFERENCES person (personID);
ALTER TABLE person ADD CONSTRAINT person_personModifiedUser FOREIGN KEY (personModifiedUser) REFERENCES person (personID);
ALTER TABLE person ADD CONSTRAINT person_preferred_tfID FOREIGN KEY (preferred_tfID) REFERENCES tf (tfID);
ALTER TABLE person ADD CONSTRAINT person_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);
ALTER TABLE project ADD CONSTRAINT project_clientID FOREIGN KEY (clientID) REFERENCES client (clientID);
ALTER TABLE project ADD CONSTRAINT project_clientContactID FOREIGN KEY (clientContactID) REFERENCES clientContact (clientContactID);
ALTER TABLE project ADD CONSTRAINT project_projectModifiedUser FOREIGN KEY (projectModifiedUser) REFERENCES person (personID);
ALTER TABLE project ADD CONSTRAINT project_defaultTimeSheetUnit FOREIGN KEY (defaultTimeSheetRateUnitID) REFERENCES timeUnit (timeUnitID);
ALTER TABLE projectCommissionPerson ADD CONSTRAINT projectCommissionPerson_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE projectCommissionPerson ADD CONSTRAINT projectCommissionPerson_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE projectCommissionPerson ADD CONSTRAINT projectCommissionPerson_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE projectPerson ADD CONSTRAINT projectPerson_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE projectPerson ADD CONSTRAINT projectPerson_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE projectPerson ADD CONSTRAINT projectPerson_roleID FOREIGN KEY (roleID) REFERENCES role (roleID);
ALTER TABLE projectPerson ADD CONSTRAINT projectPerson_projectPersonModifiedUser FOREIGN KEY (projectPersonModifiedUser) REFERENCES person (personID);
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderCreatedUser FOREIGN KEY (reminderCreatedUser) REFERENCES person (personID);
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderModifiedUser FOREIGN KEY (reminderModifiedUser) REFERENCES person (personID);
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderHash FOREIGN KEY (reminderHash) REFERENCES token (tokenHash);
ALTER TABLE reminderRecipient ADD CONSTRAINT reminderRecipient_reminderID FOREIGN KEY (reminderID) REFERENCES reminder (reminderID);
ALTER TABLE reminderRecipient ADD CONSTRAINT reminderRecipient_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE sentEmailLog ADD CONSTRAINT sentEmailLog_sentEmailLogCreatedUser FOREIGN KEY (sentEmailLogCreatedUser) REFERENCES person (personID);
ALTER TABLE sess ADD CONSTRAINT sess_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE proficiency ADD CONSTRAINT proficiency_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE proficiency ADD CONSTRAINT proficiency_skillID FOREIGN KEY (skillID) REFERENCES skill (skillID);
ALTER TABLE task ADD CONSTRAINT task_creatorID FOREIGN KEY (creatorID) REFERENCES person (personID);
ALTER TABLE task ADD CONSTRAINT task_closerID FOREIGN KEY (closerID) REFERENCES person (personID);
ALTER TABLE task ADD CONSTRAINT task_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE task ADD CONSTRAINT task_managerID FOREIGN KEY (managerID) REFERENCES person (personID);
ALTER TABLE task ADD CONSTRAINT task_taskModifiedUser FOREIGN KEY (taskModifiedUser) REFERENCES person (personID);
ALTER TABLE task ADD CONSTRAINT task_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE task ADD CONSTRAINT task_parentTaskID FOREIGN KEY (parentTaskID) REFERENCES task (taskID);
ALTER TABLE task ADD CONSTRAINT task_taskTypeID FOREIGN KEY (taskTypeID) REFERENCES taskType (taskTypeID);
ALTER TABLE task ADD CONSTRAINT task_duplicateTaskID FOREIGN KEY (duplicateTaskID) REFERENCES task (taskID);
ALTER TABLE task ADD CONSTRAINT task_taskStatus FOREIGN KEY (taskStatus) REFERENCES taskStatus (taskStatusID);
ALTER TABLE task ADD CONSTRAINT task_estimatorID FOREIGN KEY (estimatorID) REFERENCES person (personID);
ALTER TABLE pendingTask ADD CONSTRAINT pendingTask_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);
ALTER TABLE pendingTask ADD CONSTRAINT pendingTask_pendingTaskID FOREIGN KEY (pendingTaskID) REFERENCES task (taskID);
ALTER TABLE auditItem ADD CONSTRAINT auditItem_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE interestedParty ADD CONSTRAINT interestedParty_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE interestedParty ADD CONSTRAINT interestedParty_clientContactID FOREIGN KEY (clientContactID) REFERENCES clientContact (clientContactID);
ALTER TABLE tf ADD CONSTRAINT tf_tfModifiedUser FOREIGN KEY (tfModifiedUser) REFERENCES person (personID);
ALTER TABLE tfPerson ADD CONSTRAINT tfPerson_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE tfPerson ADD CONSTRAINT tfPerson_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_approvedByManagerPersonID FOREIGN KEY (approvedByManagerPersonID) REFERENCES person (personID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_approvedByAdminPersonID FOREIGN KEY (approvedByAdminPersonID) REFERENCES person (personID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_recipient_tfID FOREIGN KEY (recipient_tfID) REFERENCES tf (tfID);
ALTER TABLE timeSheet ADD CONSTRAINT timeSheet_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_timeSheetID FOREIGN KEY (timeSheetID) REFERENCES timeSheet (timeSheetID);
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_timeSheetItemDurationUnitID FOREIGN KEY (timeSheetItemDurationUnitID) REFERENCES timeUnit (timeUnitID);
ALTER TABLE timeSheetItem ADD CONSTRAINT timeSheetItem_multiplier FOREIGN KEY (multiplier) REFERENCES timeSheetItemMultiplier (timeSheetItemMultiplierID) ON UPDATE CASCADE;
ALTER TABLE tsiHint ADD CONSTRAINT tsiHint_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE tsiHint ADD CONSTRAINT tsiHint_taskID FOREIGN KEY (taskID) REFERENCES task (taskID);
ALTER TABLE token ADD CONSTRAINT token_tokenActionID FOREIGN KEY (tokenActionID) REFERENCES tokenAction (tokenActionID);
ALTER TABLE token ADD CONSTRAINT token_tokenCreatedBy FOREIGN KEY (tokenCreatedBy) REFERENCES person (personID);
ALTER TABLE transaction ADD CONSTRAINT transaction_expenseFormID FOREIGN KEY (expenseFormID) REFERENCES expenseForm (expenseFormID);
ALTER TABLE transaction ADD CONSTRAINT transaction_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE transaction ADD CONSTRAINT transaction_fromTfID FOREIGN KEY (fromTfID) REFERENCES tf (tfID);
ALTER TABLE transaction ADD CONSTRAINT transaction_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE transaction ADD CONSTRAINT transaction_transactionModifiedUser FOREIGN KEY (transactionModifiedUser) REFERENCES person (personID);
ALTER TABLE transaction ADD CONSTRAINT transaction_transactionCreatedUser FOREIGN KEY (transactionCreatedUser) REFERENCES person (personID);
ALTER TABLE transaction ADD CONSTRAINT transaction_invoiceID FOREIGN KEY (invoiceID) REFERENCES invoice (invoiceID);
ALTER TABLE transaction ADD CONSTRAINT transaction_invoiceItemID FOREIGN KEY (invoiceItemID) REFERENCES invoiceItem (invoiceItemID);
ALTER TABLE transaction ADD CONSTRAINT transaction_timeSheetID FOREIGN KEY (timeSheetID) REFERENCES timeSheet (timeSheetID);
ALTER TABLE transaction ADD CONSTRAINT transaction_productSaleID FOREIGN KEY (productSaleID) REFERENCES productSale (productSaleID);
ALTER TABLE transaction ADD CONSTRAINT transaction_productSaleItemID FOREIGN KEY (productSaleItemID) REFERENCES productSaleItem (productSaleItemID);
ALTER TABLE transaction ADD CONSTRAINT transaction_productCostID FOREIGN KEY (productCostID) REFERENCES productCost (productCostID);
ALTER TABLE transaction ADD CONSTRAINT transaction_transactionRepeatID FOREIGN KEY (transactionRepeatID) REFERENCES transactionRepeat (transactionRepeatID);
ALTER TABLE transaction ADD CONSTRAINT transaction_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_fromTfID FOREIGN KEY (fromTfID) REFERENCES tf (tfID);
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_transactionRepeatModifiedUser FOREIGN KEY (transactionRepeatModifiedUser) REFERENCES person (personID);
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_transactionRepeatCreatedUser FOREIGN KEY (transactionRepeatCreatedUser) REFERENCES person (personID);
ALTER TABLE transactionRepeat ADD CONSTRAINT transactionRepeat_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE productCost ADD CONSTRAINT productCost_productID FOREIGN KEY (productID) REFERENCES product (productID);
ALTER TABLE productCost ADD CONSTRAINT productCost_currencyTypeID FOREIGN KEY (currencyTypeID) REFERENCES currencyType (currencyTypeID);
ALTER TABLE productSale ADD CONSTRAINT productSale_clientID FOREIGN KEY (clientID) REFERENCES client (clientID);
ALTER TABLE productSale ADD CONSTRAINT productSale_projectID FOREIGN KEY (projectID) REFERENCES project (projectID);
ALTER TABLE productSale ADD CONSTRAINT productSale_productSaleCreatedUser FOREIGN KEY (productSaleCreatedUser) REFERENCES person (personID);
ALTER TABLE productSale ADD CONSTRAINT productSale_productSaleModifiedUser FOREIGN KEY (productSaleModifiedUser) REFERENCES person (personID);
ALTER TABLE productSale ADD CONSTRAINT productSale_personID FOREIGN KEY (personID) REFERENCES person (personID);
ALTER TABLE productSale ADD CONSTRAINT productSale_tfID FOREIGN KEY (tfID) REFERENCES tf (tfID);
ALTER TABLE productSaleItem ADD CONSTRAINT productSaleItem_productID FOREIGN KEY (productID) REFERENCES product (productID);
ALTER TABLE productSaleItem ADD CONSTRAINT productSaleItem_productSaleID FOREIGN KEY (productSaleID) REFERENCES productSale (productSaleID);










