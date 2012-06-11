-- permit anyone to create comments.
UPDATE permission SET roleName = '' where tableName = 'comment';
