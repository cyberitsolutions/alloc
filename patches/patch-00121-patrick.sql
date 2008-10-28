-- Add new configuration settings for email subject lines
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_taskComment", "Task Comment: %ti %tn [%tp]", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_dailyDigest", "Daily Digest", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetToManager", "Time sheet %mi submitted for your approval", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetFromManager", "Time sheet %mi rejected by manager", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetFromAdministrator", "Time sheet %mi rejected by administrator", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetToAdministrator", "Time sheet %mi submitted for your approval", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_timeSheetCompleted", "Time sheet %mi completed", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderClient", "Client Reminder: %li %cc", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderProject", "Project Reminder: %pi %pn", "text");
INSERT INTO config (`name`,`value`,`type`) VALUES ("emailSubject_reminderTask", "Task Reminder: %ti %tn [%tp]", "text");

