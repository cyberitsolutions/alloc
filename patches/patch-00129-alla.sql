-- nuke blocker, add taskStatus and taskSubStatus
ALTER TABLE task DROP blocker;
ALTER TABLE task ADD taskStatus varchar(255) NOT NULL;
ALTER TABLE task ADD taskSubStatus varchar(255) DEFAULT NULL AFTER taskStatus;

-- add indexes
ALTER TABLE task ADD KEY taskStatus (taskStatus);
ALTER TABLE task ADD KEY taskSubStatus (taskSubStatus);

DELETE FROM config WHERE name = 'taskBlockers';

-- $ops["open"]["notstarted"]   = array("label"=>"Not Started","colour"=>"background-color:#8fe78f;");
-- $ops["open"]["inprogress"]   = array("label"=>"In Progress","colour"=>"background-color:#8fe78f;");
-- $ops["pending"]["info"]      = array("label"=>"Info"       ,"colour"=>"background-color:#f9ca7f;");
-- $ops["pending"]["manager"]   = array("label"=>"Manager"    ,"colour"=>"background-color:#f9ca7f;");
-- $ops["pending"]["client"]    = array("label"=>"Client"     ,"colour"=>"background-color:#f9ca7f;");
-- $ops["closed"]["invalid"]    = array("label"=>"Invalid"    ,"colour"=>"background-color:#e0e0e0;");
-- $ops["closed"]["duplicate"]  = array("label"=>"Duplicate"  ,"colour"=>"background-color:#e0e0e0;");
-- $ops["closed"]["incomplete"] = array("label"=>"Incomplete" ,"colour"=>"background-color:#e0e0e0;");
-- $ops["closed"]["complete"]   = array("label"=>"Completed"  ,"colour"=>"background-color:#e0e0e0;");
-- echo serialize($ops);

INSERT INTO config (name,value,type) VALUES ("taskStatusOptions",'a:3:{s:4:"open";a:2:{s:10:"notstarted";a:2:{s:5:"label";s:11:"Not Started";s:6:"colour";s:25:"background-color:#8fe78f;";}s:10:"inprogress";a:2:{s:5:"label";s:11:"In Progress";s:6:"colour";s:25:"background-color:#8fe78f;";}}s:7:"pending";a:3:{s:4:"info";a:2:{s:5:"label";s:4:"Info";s:6:"colour";s:25:"background-color:#f9ca7f;";}s:7:"manager";a:2:{s:5:"label";s:7:"Manager";s:6:"colour";s:25:"background-color:#f9ca7f;";}s:6:"client";a:2:{s:5:"label";s:6:"Client";s:6:"colour";s:25:"background-color:#f9ca7f;";}}s:6:"closed";a:4:{s:7:"invalid";a:2:{s:5:"label";s:7:"Invalid";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:9:"duplicate";a:2:{s:5:"label";s:9:"Duplicate";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:10:"incomplete";a:2:{s:5:"label";s:10:"Incomplete";s:6:"colour";s:25:"background-color:#e0e0e0;";}s:8:"complete";a:2:{s:5:"label";s:9:"Completed";s:6:"colour";s:25:"background-color:#e0e0e0;";}}}', 'array');


-- udpate existing tasks statuses
UPDATE task SET taskStatus = 'closed', taskSubStatus = 'complete' WHERE dateActualCompletion IS NOT NULL;
UPDATE task SET taskStatus = 'closed', taskSubStatus = 'duplicate' WHERE duplicateTaskID IS NOT NULL;

UPDATE task SET taskStatus = 'open', taskSubStatus = 'notstarted' WHERE dateActualCompletion IS NULL AND dateActualStart IS NULL;
UPDATE task SET taskStatus = 'open', taskSubStatus = 'inprogress' WHERE dateActualCompletion IS NULL AND dateActualStart IS NOT NULL;

