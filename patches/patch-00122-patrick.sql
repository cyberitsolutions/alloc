
-- add config item for task blockers
INSERT INTO config (name,value,type) VALUES ("taskBlockers", "a:4:{i:0;a:2:{s:5:\"label\";s:20:\"Available to work on\";s:4:\"icon\";s:18:\"icon_orb_green.png\";}i:1;a:2:{s:5:\"label\";s:20:\"Waiting for customer\";s:4:\"icon\";s:16:\"icon_orb_red.png\";}i:2;a:2:{s:5:\"label\";s:23:\"Waiting for information\";s:4:\"icon\";s:16:\"icon_orb_red.png\";}i:3;a:2:{s:5:\"label\";s:16:\"Awaiting manager\";s:4:\"icon\";s:19:\"icon_orb_yellow.png\";}}","array");

-- add a new task field for the current task blockage status
ALTER TABLE task ADD blocker TINYINT NOT NULL DEFAULT 0;
