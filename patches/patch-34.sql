

-- Add finished to time sheet status's
ALTER TABLE timeSheet CHANGE status status enum('edit','manager','admin','invoiced','paid','finished') default NULL;

-- Change all 'paid' into 'finished'
UPDATE timeSheet SET status = "finished" WHERE status = "paid";

-- Remove 'paid' from time sheet status
ALTER TABLE timeSheet CHANGE status status enum('edit','manager','admin','invoiced','finished') default NULL;

-- Add timesheet_finished to sentEmailLog.sentEmailType
ALTER TABLE sentEmailLog CHANGE sentEmailType sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','timesheet_paid','daily_digest','timesheet_finished');

-- Update sentEmailLog entries
UPDATE sentEmailLog SET sentEmailType = 'timesheet_finished' WHERE sentEmailType = 'timesheet_paid';

-- Remove unused 'timesheet_paid'
ALTER TABLE sentEmailLog CHANGE sentEmailType sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','daily_digest','timesheet_finished');

