alter table sentEmailLog change sentEmailType sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit') default NULL;

