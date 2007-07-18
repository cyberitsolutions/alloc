
-- Add a new timeSheet status type of paid.
ALTER TABLE timeSheet CHANGE status status enum('edit','manager','admin','invoiced','paid') default NULL;

-- Add a new type of sentEmail: timesheet_paid
ALTER TABLE sentEmailLog CHANGE sentEmailType sentEmailType enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','timesheet_paid','daily_digest');
