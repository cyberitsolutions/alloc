-- add new manager field to task
alter table task add managerID int(11) default NULL after personID;
