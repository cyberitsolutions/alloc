-- Add new_password to sentEmailLog.sentEmailType
ALTER TABLE sentEmailLog CHANGE sentEmailType sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','daily_digest','timesheet_finished','new_password');

