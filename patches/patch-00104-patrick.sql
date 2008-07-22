
-- alter permissions that refer to taskCommentTemplate to refer to commentTemplate (the new name of the table)
UPDATE permission SET tableName = "commentTemplate" WHERE tableName = "taskCommentTemplate";

