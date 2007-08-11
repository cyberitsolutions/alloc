-- Add new created when/who fields to comment
ALTER TABLE comment ADD commentCreatedTime datetime default NULL AFTER commentLinkID;
ALTER TABLE comment ADD commentCreatedUser int(11) default NULL AFTER commentCreatedTime;

-- alloc comment.commentModifiedTime to be null
ALTER TABLE comment CHANGE commentModifiedTime commentModifiedTime datetime DEFAULT NULL;

-- update new columns from old data
UPDATE comment SET commentCreatedUser = commentModifiedUser;
UPDATE comment SET commentCreatedTime = commentModifiedTime;

-- add plaintext field to capture person who created comment, for incoming email comments
ALTER TABLE comment ADD commentCreatedUserText varchar(255) DEFAULT NULL AFTER commentCreatedUserClientContactID;

-- alloc various modified columns to be null, rename modified columns to be consistant
ALTER TABLE client CHANGE clientModifiedTime clientModifiedTime datetime DEFAULT NULL;
ALTER TABLE client CHANGE clientModifiedUser clientModifiedUser int(11) DEFAULT NULL;

ALTER TABLE expenseForm CHANGE expenseFormModifiedUser expenseFormModifiedUser int(11) DEFAULT NULL;
ALTER TABLE expenseForm CHANGE lastModified            expenseFormModifiedTime datetime DEFAULT NULL;

ALTER TABLE item CHANGE itemModifiedUser itemModifiedUser int(11) DEFAULT NULL;
ALTER TABLE item CHANGE lastModified     itemModifiedTime datetime DEFAULT NULL;

ALTER TABLE loan CHANGE loanModifiedUser loanModifiedUser int(11) DEFAULT NULL;
ALTER TABLE loan CHANGE lastModified     loanModifiedTime datetime DEFAULT NULL;

ALTER TABLE person CHANGE personModifiedUser personModifiedUser int(11) DEFAULT NULL;

ALTER TABLE project CHANGE projectModifiedUser projectModifiedUser int(11) DEFAULT NULL;

ALTER TABLE projectPerson CHANGE projectPersonModifiedUser projectPersonModifiedUser int(11) DEFAULT NULL;

ALTER TABLE reminder CHANGE reminderModifiedTime reminderModifiedTime datetime DEFAULT NULL;
ALTER TABLE reminder CHANGE reminderModifiedUser reminderModifiedUser int(11) DEFAULT NULL;

ALTER TABLE sentEmailLog CHANGE sentEmailLogModifiedTime sentEmailLogModifiedTime datetime DEFAULT NULL;
ALTER TABLE sentEmailLog CHANGE sentEmailLogModifiedUser sentEmailLogModifiedUser int(11) DEFAULT NULL;

ALTER TABLE task CHANGE taskModifiedUser taskModifiedUser int(11) DEFAULT NULL;

ALTER TABLE taskCommentTemplate CHANGE taskCommentTemplateLastModified taskCommentTemplateModifiedTime datetime DEFAULT NULL;

ALTER TABLE tf CHANGE tfModifiedTime tfModifiedTime datetime DEFAULT NULL;
ALTER TABLE tf CHANGE tfModifiedUser tfModifiedUser int(11) DEFAULT NULL;

ALTER TABLE transaction CHANGE transactionModifiedUser transactionModifiedUser int(11) DEFAULT NULL;
ALTER TABLE transaction CHANGE lastModified            transactionModifiedTime datetime DEFAULT NULL;

ALTER TABLE transactionRepeat CHANGE transactionRepeatModifiedUser transactionRepeatModifiedUser int(11) DEFAULT NULL;
ALTER TABLE transactionRepeat CHANGE lastModified            transactionRepeatModifiedTime datetime DEFAULT NULL;
