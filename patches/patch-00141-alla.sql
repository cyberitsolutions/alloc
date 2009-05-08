-- Change columns to varchars that will have referential integrity to the lookup/metadata tables.

ALTER TABLE absence CHANGE absenceType absenceType varchar(255) default NULL;
ALTER TABLE client CHANGE clientStatus clientStatus varchar(255) NOT NULL default 'current';
ALTER TABLE config CHANGE type type varchar(255) NOT NULL default "text";
ALTER TABLE invoice CHANGE invoiceStatus invoiceStatus varchar(255) NOT NULL DEFAULT 'edit';
ALTER TABLE item CHANGE itemType itemType varchar(255) NOT NULL default 'cd';
ALTER TABLE project CHANGE projectType projectType varchar(255) default NULL;
ALTER TABLE project CHANGE currencyType currencyType varchar(255) default NULL;
ALTER TABLE project CHANGE projectStatus projectStatus varchar(255) NOT NULL default 'current';
ALTER TABLE projectPerson CHANGE emailType emailType varchar(255) default NULL;
ALTER TABLE role CHANGE roleLevel roleLevel varchar(255) NOT NULL;
ALTER TABLE reminder CHANGE reminderRecuringInterval reminderRecuringInterval varchar(255) NOT NULL default 'No';
ALTER TABLE reminder CHANGE reminderAdvNoticeInterval reminderAdvNoticeInterval varchar(255) NOT NULL default 'No';
ALTER TABLE proficiency CHANGE skillProficiency skillProficiency varchar(255) NOT NULL default 'Novice';
ALTER TABLE auditItem CHANGE changeType changeType varchar(255) NOT NULL default 'FieldChange';
ALTER TABLE timeSheet CHANGE status status varchar(255) default NULL;
ALTER TABLE transaction CHANGE status status varchar(255) NOT NULL DEFAULT 'pending';
ALTER TABLE transaction CHANGE transactionType transactionType varchar(255) NOT NULL;
ALTER TABLE transactionRepeat CHANGE transactionType transactionType varchar(255) NOT NULL;
ALTER TABLE productSale CHANGE status status varchar(255) NOT NULL;

