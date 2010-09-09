-- Add the new rejected field to the timesheet table
ALTER TABLE timeSheet ADD dateRejected date default NULL AFTER dateSubmittedToAdmin;


