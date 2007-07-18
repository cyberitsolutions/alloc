CREATE TABLE sentEmailLog (
  sentEmailLogID int(11) NOT NULL auto_increment,
  sentEmailTo text NOT NULL,
  sentEmailSubject varchar(255),
  sentEmailBody text,
  sentEmailHeader varchar(255),
  sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments'),
  sentEmailLogModifiedTime timestamp(14) NOT NULL,
  sentEmailLogModifiedUser int(11) NOT NULL default '0',
  PRIMARY KEY  (sentEmailLogID)
) TYPE=ISAM PACK_KEYS=1;


