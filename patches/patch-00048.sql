-- Insert new config vars for mail server 
INSERT INTO config (name,value,type) VALUES ("allocEmailAdmin","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailHost","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailPort","143","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailUsername","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailPassword","","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailProtocol","imap","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailFolder","INBOX","text");
INSERT INTO config (name,value,type) VALUES ("allocEmailKeyMethod","headers","text");


-- Create new table token
CREATE TABLE token (
tokenID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
tokenHash VARCHAR(255) NOT NULL DEFAULT '',
tokenEntity VARCHAR(32) DEFAULT '',
tokenEntityID INT(11),
tokenActionID INT(11) NOT NULL,
tokenExpirationDate DATETIME DEFAULT NULL,
tokenUsed INT(11) DEFAULT 0,
tokenMaxUsed INT(11) DEFAULT 0,
tokenActive INT(1) DEFAULT 0,
tokenCreatedBy INT(11) NOT NULL,
tokenCreatedDate DATETIME,
UNIQUE KEY (tokenHash)
) TYPE=MyISAM;


-- Create new table tokenAction
CREATE TABLE tokenAction (
tokenActionID INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
tokenAction VARCHAR(32) NOT NULL,
tokenActionType VARCHAR(32),
tokenActionMethod VARCHAR(32)
) TYPE=MyISAM;


-- Insert new tokenAction for adding comments to a task
INSERT INTO tokenAction (tokenAction,tokenActionType,tokenActionMethod) VALUES ("Add Comments to Task","task","add_comment_from_email");

-- Add new modified user field to the comment table to represent the client contacts
ALTER TABLE comment ADD commentCreatedUserClientContactID INT(11) DEFAULT NULL AFTER commentModifiedUser;

-- Change type of commentModifiedUser so that it can be NULL
ALTER TABLE comment CHANGE commentModifiedUser commentModifiedUser INT(11) DEFAULT NULL;

-- Add email recipients field to comment table
ALTER TABLE comment ADD commentEmailRecipients VARCHAR(255) DEFAULT "" AFTER commentCreatedUserClientContactID;
