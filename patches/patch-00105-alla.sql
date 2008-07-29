-- Add new commentTemplateType field to allow different commentTemplates lists for tasks and timesheets.
ALTER TABLE commentTemplate add commentTemplateType varchar(255) DEFAULT NULL AFTER commentTemplateText;
UPDATE commentTemplate set commentTemplateType = 'task';
