
-- Rename taskCommentTemplate to commentTemplate to genericise the functionality.
ALTER TABLE taskCommentTemplate RENAME TO commentTemplate;
ALTER TABLE commentTemplate CHANGE taskCommentTemplateName commentTemplateName varchar(255);
ALTER TABLE commentTemplate CHANGE taskCommentTemplateText commentTemplateText text;
ALTER TABLE commentTemplate CHANGE taskCommentTemplateModifiedTime commentTemplateModifiedTime datetime;
ALTER TABLE commentTemplate CHANGE taskCommentTemplateID commentTemplateID int(11) NOT NULL auto_increment;


