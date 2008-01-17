-- Make sent email header field big
ALTER TABLE sentEmailLog MODIFY sentEmailHeader TEXT;

-- Add orphan type to sentEmailTypes
ALTER TABLE sentEmailLog MODIFY sentEmailType  enum('reminder','reminder_advnotice','task_created','task_closed','task_comments','timesheet_submit','timesheet_reject','daily_digest','timesheet_finished','new_password','task_reassigned','orphan') DEFAULT NULL;

