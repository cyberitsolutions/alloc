-- more changes to reconcile differences between sql/db_structure.sql
alter table invoiceItem change status status varchar(255) default NULL;
