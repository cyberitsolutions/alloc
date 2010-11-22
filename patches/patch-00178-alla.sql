
-- create a back up field
ALTER TABLE comment ADD commentEmailUIDORIG VARCHAR(255) DEFAULT NULL;

-- back up data
UPDATE comment SET commentEmailUIDORIG = commentEmailUID;

-- wipe field clean, so the unique constraint can be added
UPDATE comment SET commentEmailUID = null;

-- need to ensure commentEmailUID is unique
CREATE UNIQUE INDEX commentEmailUID ON comment (commentEmailUID);

-- create a new field to track the email id
ALTER TABLE comment ADD commentEmailMessageID TEXT DEFAULT NULL AFTER commentEmailUID;

-- backup the comment table
CREATE TABLE commentbackup SELECT * FROM comment;
