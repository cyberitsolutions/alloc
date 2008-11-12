-- Add a FULLTEXT index to task.taskName for easy duplicate checking
ALTER TABLE task ADD FULLTEXT(taskName);
