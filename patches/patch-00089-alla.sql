-- add new email uid field to comment, so each comment knows which email it came from
ALTER TABLE comment ADD commentEmailUID VARCHAR(255) DEFAULT NULL AFTER commentEmailRecipients;
