-- Rename tf.status to tf.tfActive (have to update old entries)
ALTER TABLE tf CHANGE status tfActive varchar(255);
UPDATE tf SET tfActive = 1 WHERE tfActive = 'active';
UPDATE tf SET tfActive = 0 WHERE tfActive = 'disabled' OR tfActive IS NULL;
ALTER TABLE tf CHANGE tfActive tfActive tinyint(1) NOT NULL;
