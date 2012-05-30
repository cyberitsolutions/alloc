#!/bin/bash

# This script creates the views for a single user alloc database
# This is to be imported as the mysql root user eg:
# ./make_single_user_db.sh | mysql -u root -p alloc_someuser

# This script can be re-imported repeatedly and it should rebuild clean every time

username="boppity"
password="boppity"
personID="4"

cat <<EOD

DROP DATABASE IF EXISTS alloc_${username};
CREATE DATABASE alloc_${username};
USE alloc_${username};

-- These views are read-only, but all records in the related tables can be read by every person

CREATE OR REPLACE VIEW alloc_${username}.absenceType      AS SELECT * FROM alloc.absenceType;
CREATE OR REPLACE VIEW alloc_${username}.announcement     AS SELECT * FROM alloc.announcement;
CREATE OR REPLACE VIEW alloc_${username}.auditItem        AS SELECT * FROM alloc.auditItem;
CREATE OR REPLACE VIEW alloc_${username}.changeType       AS SELECT * FROM alloc.changeType;
CREATE OR REPLACE VIEW alloc_${username}.client           AS SELECT * FROM alloc.client;
CREATE OR REPLACE VIEW alloc_${username}.clientContact    AS SELECT * FROM alloc.clientContact;
CREATE OR REPLACE VIEW alloc_${username}.clientStatus     AS SELECT * FROM alloc.clientStatus;
CREATE OR REPLACE VIEW alloc_${username}.comment          AS SELECT * FROM alloc.comment;
CREATE OR REPLACE VIEW alloc_${username}.commentTemplate  AS SELECT * FROM alloc.commentTemplate;
CREATE OR REPLACE VIEW alloc_${username}.currencyType     AS SELECT * FROM alloc.currencyType;
CREATE OR REPLACE VIEW alloc_${username}.error            AS SELECT * FROM alloc.error;
CREATE OR REPLACE VIEW alloc_${username}.exchangeRate     AS SELECT * FROM alloc.exchangeRate;
CREATE OR REPLACE VIEW alloc_${username}.indexQueue       AS SELECT * FROM alloc.indexQueue;
CREATE OR REPLACE VIEW alloc_${username}.interestedParty  AS SELECT * FROM alloc.interestedParty;
CREATE OR REPLACE VIEW alloc_${username}.invoiceStatus    AS SELECT * FROM alloc.invoiceStatus;
CREATE OR REPLACE VIEW alloc_${username}.item             AS SELECT * FROM alloc.item;
CREATE OR REPLACE VIEW alloc_${username}.itemType         AS SELECT * FROM alloc.itemType;
CREATE OR REPLACE VIEW alloc_${username}.loan             AS SELECT * FROM alloc.loan;
CREATE OR REPLACE VIEW alloc_${username}.patchLog         AS SELECT * FROM alloc.patchLog;
CREATE OR REPLACE VIEW alloc_${username}.permission       AS SELECT * FROM alloc.permission;
CREATE OR REPLACE VIEW alloc_${username}.product          AS SELECT * FROM alloc.product;
CREATE OR REPLACE VIEW alloc_${username}.productCost      AS SELECT * FROM alloc.productCost;
CREATE OR REPLACE VIEW alloc_${username}.productSaleStatus AS SELECT * FROM alloc.productSaleStatus;
CREATE OR REPLACE VIEW alloc_${username}.proficiency      AS SELECT * FROM alloc.proficiency;
CREATE OR REPLACE VIEW alloc_${username}.projectStatus    AS SELECT * FROM alloc.projectStatus;
CREATE OR REPLACE VIEW alloc_${username}.projectType      AS SELECT * FROM alloc.projectType;
CREATE OR REPLACE VIEW alloc_${username}.reminderAdvNoticeInterval AS SELECT * FROM alloc.reminderAdvNoticeInterval;
CREATE OR REPLACE VIEW alloc_${username}.reminderRecuringInterval AS SELECT * FROM alloc.reminderRecuringInterval;
CREATE OR REPLACE VIEW alloc_${username}.role             AS SELECT * FROM alloc.role;
CREATE OR REPLACE VIEW alloc_${username}.roleLevel        AS SELECT * FROM alloc.roleLevel;
CREATE OR REPLACE VIEW alloc_${username}.sentEmailType    AS SELECT * FROM alloc.sentEmailType;
CREATE OR REPLACE VIEW alloc_${username}.skill            AS SELECT * FROM alloc.skill;
CREATE OR REPLACE VIEW alloc_${username}.skillProficiency AS SELECT * FROM alloc.skillProficiency;
CREATE OR REPLACE VIEW alloc_${username}.task             AS SELECT * FROM alloc.task;
CREATE OR REPLACE VIEW alloc_${username}.taskStatus       AS SELECT * FROM alloc.taskStatus;
CREATE OR REPLACE VIEW alloc_${username}.taskType         AS SELECT * FROM alloc.taskType;
CREATE OR REPLACE VIEW alloc_${username}.pendingTask      AS SELECT * FROM alloc.pendingTask;
CREATE OR REPLACE VIEW alloc_${username}.timeSheetItemMultiplier AS SELECT * FROM alloc.timeSheetItemMultiplier;
CREATE OR REPLACE VIEW alloc_${username}.timeSheetStatus  AS SELECT * FROM alloc.timeSheetStatus;
CREATE OR REPLACE VIEW alloc_${username}.timeUnit         AS SELECT * FROM alloc.timeUnit;
CREATE OR REPLACE VIEW alloc_${username}.token            AS SELECT * FROM alloc.token;
CREATE OR REPLACE VIEW alloc_${username}.tokenAction      AS SELECT * FROM alloc.tokenAction;
CREATE OR REPLACE VIEW alloc_${username}.transactionStatus AS SELECT * FROM alloc.transactionStatus;
CREATE OR REPLACE VIEW alloc_${username}.transactionType  AS SELECT * FROM alloc.transactionType;


-- Restricted row sets

CREATE OR REPLACE VIEW alloc_${username}.timeSheet     AS SELECT * FROM alloc.timeSheet     WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.timeSheetItem AS SELECT * FROM alloc.timeSheetItem WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.absence       AS SELECT * FROM alloc.absence       WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.config        AS SELECT * FROM alloc.config        WHERE name != 'allocEmailPassword';

CREATE OR REPLACE VIEW alloc_${username}.project AS SELECT project.*, projectPerson.rate as rate, projectPerson.rateUnitID as rateUnitID
                                                      FROM alloc.project
                                                 LEFT JOIN alloc.projectPerson ON projectPerson.projectID = project.projectID 
                                                     WHERE projectPerson.personID = ${personID};

CREATE OR REPLACE VIEW alloc_${username}.person        AS SELECT personID, username, emailAddress, availability, areasOfInterest, comments, managementComments, lastLoginDate, personModifiedUser, firstName, surname, preferred_tfID, personActive, sessData, phoneNo1, phoneNo2, emergencyContact FROM alloc.person;

CREATE OR REPLACE VIEW alloc_${username}.expenseForm AS SELECT * FROM alloc.expenseForm WHERE expenseFormCreatedUser = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.reminder AS SELECT * FROM alloc.reminder WHERE personID = ${personID};

-- ** invoice
-- ** invoiceItem
CREATE OR REPLACE VIEW alloc_${username}.history AS SELECT * FROM alloc.history WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.projectCommissionPerson AS SELECT * FROM alloc.projectCommissionPerson WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.projectPerson AS SELECT * FROM alloc.projectPerson WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.sentEmailLog AS SELECT * FROM alloc.sentEmailLog WHERE sentEmailLogCreatedUser = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.sess AS SELECT * FROM alloc.sess WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.tf AS SELECT tf.* FROM alloc.tfPerson LEFT JOIN alloc.tf ON tf.tfID = tfPerson.tfID WHERE alloc.tfPerson.personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.transaction AS SELECT transaction.* FROM alloc.transaction LEFT JOIN alloc.tfPerson ON (tfPerson.tfID = transaction.tfID OR tfPerson.tfID = transaction.fromTfID) WHERE tfPerson.personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.transactionRepeat AS SELECT transactionRepeat.* FROM alloc.transactionRepeat LEFT JOIN alloc.tfPerson ON (tfPerson.tfID = transactionRepeat.tfID OR tfPerson.tfID = transactionRepeat.fromTfID) WHERE tfPerson.personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.productSale AS SELECT * FROM alloc.productSale WHERE personID = ${personID} OR productSaleCreatedUser = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.productSaleItem AS SELECT productSaleItem.* FROM alloc.productSaleItem LEFT JOIN productSale ON productSaleItem.productSaleID = productSale.productSaleID WHERE productSale.personID = ${personID} OR productSale.productSaleCreatedUser = ${personID};


-- Everyone can read everything in their database
GRANT SELECT ON alloc_${username}.* TO ${username} IDENTIFIED BY '${password}';
GRANT SHOW VIEW ON alloc_${username}.* TO ${username};
GRANT CREATE VIEW ON alloc_${username}.* TO ${username};
-- GRANT CREATE ROUTINE ON alloc_${username}.* TO ${username};
-- GRANT EXECUTE ON alloc_${username}.* TO ${username};
-- nope don't exist:
-- GRANT DROP VIEW ON alloc_${username}.* TO ${username};
-- GRANT DELETE ROUTINE ON alloc_${username}.* TO ${username};

-- can insert, update and delete these tables:
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.timeSheet TO ${username};
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.timeSheetItem TO ${username};
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.task TO ${username};
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.pendingTask TO ${username};
-- expenseForm should be writable ?
-- reminder    should be writable ?


EOD
