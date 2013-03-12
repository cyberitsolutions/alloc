-- This script should be imported into the main production alloc database
-- It can be re-imported repeatedly and it should rebuild clean every time

-- Permit limited recursion in the change_task_status procedure
set max_sp_recursion_depth = 10; 

DELIMITER $$

-- Error messages for mysql
DELETE FROM error$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to change time sheet status.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to delete time sheet unless status is edit.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Time sheet is not editable.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Time sheet is not editable(2).\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Time sheet's rate is not editable.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not editable.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not editable: user not a project member.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Invalid date.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not deletable.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task has pending tasks.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Must use: call change_task_status(taskID,status)\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task cannot be pending itself.\n\n")$$
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task belongs to wrong project.\n\n")$$


-- if (NOT something) doesn't work for NULLs
DROP FUNCTION IF EXISTS empty $$
CREATE FUNCTION empty(str text) RETURNS BOOLEAN DETERMINISTIC
BEGIN RETURN str = '' OR str IS NULL; END $$

-- if (null != 'something') evaluates falsely
DROP FUNCTION IF EXISTS neq $$
CREATE FUNCTION neq(str1 text, str2 text) RETURNS BOOLEAN DETERMINISTIC
BEGIN RETURN IFNULL(str1,'') != IFNULL(str2,''); END $$

-- returns true if we're accessing alloc from a single-user database of views
DROP FUNCTION IF EXISTS using_views $$
CREATE FUNCTION using_views() RETURNS BOOLEAN DETERMINISTIC
  RETURN (USER() != 'alloc@localhost' AND USER() != 'root@localhost');
$$

-- Return the personID for the current logged in user. This is determined
-- either by the mysql user, or if the mysql user is root or alloc, then
-- return the @personID session variable.
DROP FUNCTION IF EXISTS personID $$
CREATE FUNCTION personID() RETURNS varchar(255) READS SQL DATA
BEGIN
  IF (NOT using_views() AND @personID) THEN
    RETURN @personID;
  ELSE
    SELECT personID INTO @personID FROM person WHERE username = SUBSTRING(USER(),1,LOCATE("@",USER())-1)
  ORDER BY personActive DESC LIMIT 1;
    RETURN @personID;
  END IF;
END
$$

-- has_perm

DROP FUNCTION IF EXISTS has_perm $$
CREATE FUNCTION has_perm(pID INTEGER, action INTEGER, tableN VARCHAR(255)) RETURNS BOOLEAN READS SQL DATA
BEGIN
  DECLARE p varchar(255);
  DECLARE rtn BOOLEAN;
  SELECT perms INTO p FROM person WHERE personID = pID;

  SELECT count(*) INTO rtn
    FROM permission
   WHERE tableName = tableN
     AND (actions & action = action)
     AND (roleName IS NULL OR roleName = '' OR find_in_set(roleName,p)>0);
  return rtn;
END
$$

DROP FUNCTION IF EXISTS have_role $$
CREATE FUNCTION have_role(pID INTEGER, role VARCHAR(255)) RETURNS BOOLEAN READS SQL DATA
BEGIN
  DECLARE num INTEGER;
  SELECT count(personID) INTO num FROM person WHERE personID = pID AND find_in_set(role,perms);
  RETURN num;
END
$$

DROP FUNCTION IF EXISTS have_project_role $$
CREATE FUNCTION have_project_role(pID INTEGER, projID INTEGER, roles VARCHAR(255)) RETURNS BOOLEAN READS SQL DATA
BEGIN
  DECLARE num INTEGER;
  SELECT count(projectPersonID) INTO num
    FROM projectPerson pp 
    LEFT JOIN role ppr ON ppr.roleID = pp.roleID 
   WHERE projectID = projID and personID = pID
     AND ppr.roleHandle in (roles);
  RETURN num;
END
$$

DROP FUNCTION IF EXISTS can_edit_rate $$
CREATE FUNCTION can_edit_rate(pID INTEGER, projID INTEGER) RETURNS BOOLEAN READS SQL DATA
BEGIN
  DECLARE r BIGINT(20);
  SELECT rate INTO r FROM projectPerson WHERE projectID = projID AND personID = pID AND rate IS NULL LIMIT 1;
  IF (r IS NULL) THEN
    RETURN 1;
  END IF;
  IF (have_role(personID(), 'manage')) THEN
    RETURN 1;
  END IF;
  IF (have_role(personID(), 'admin')) THEN
    RETURN 1;
  END IF;
  IF (have_project_role(personID(), projID, 'isManager')) THEN
    RETURN 1;
  END IF;
  IF (have_project_role(personID(), projID, 'timeSheetRecipient')) THEN
    RETURN 1;
  END IF;
  RETURN 0;
END
$$


-- this fucker exists because mysql doesn't yet provide a way to kill the
-- operation when a trigger's initiating event should fail, eg BEFORE DELETE might
-- need to kill the delete because it's not permitted. Apparently proper signals
-- are coming in mysql 5.5(!)
DROP PROCEDURE IF EXISTS alloc_error $$
CREATE PROCEDURE alloc_error(msg varchar(255))
  -- perform an illegal command, should bomb out with msg as the error text
  -- this relies on the error messages being inserted previously - to cause
  -- the unique key check to fail.
  INSERT INTO error (errorID) VALUES (CONCAT('\n\nALLOC ERROR: ',msg,'\n\n'));
  -- If >= MySQL 5.5
  -- signal sqlstate '45000' set message_text = msg;
$$


-- audit 

DROP PROCEDURE IF EXISTS alloc_log $$
CREATE PROCEDURE alloc_log(entityName VARCHAR(255), entityID INTEGER, fieldName VARCHAR(255), oldValue TEXT, newValue TEXT)
BEGIN
  IF (neq(oldValue,newValue)) THEN
    INSERT INTO auditItem (entityName,entityID,personID,dateChanged,changeType,fieldName,oldValue) VALUES
    (entityName,entityID,personID(),NOW(),"FieldChange",fieldName,oldValue);
  END IF;
END
$$

-- search index

DROP PROCEDURE IF EXISTS update_search_index $$
CREATE PROCEDURE update_search_index(eName VARCHAR(255), eID INTEGER)
BEGIN
  DECLARE num INTEGER;
  IF (using_views()) THEN
    SELECT count(indexQueueID) INTO num FROM indexQueue WHERE entity = eName AND entityID = eID;
    IF (num = 0) THEN
      INSERT INTO indexQueue (entity,entityID) VALUES (eName, eID);
    END IF;
  END IF;
END
$$


-- triggers for timeSheet

DROP TRIGGER IF EXISTS before_insert_timeSheet $$
CREATE TRIGGER before_insert_timeSheet BEFORE INSERT ON timeSheet
FOR EACH ROW
BEGIN
  DECLARE pref_tfID INTEGER;
  DECLARE cbd BIGINT(20);
  DECLARE cur VARCHAR(3);
  SET NEW.personID = personID();
  SET NEW.status = 'edit';
  SELECT preferred_tfID INTO pref_tfID FROM person WHERE personID = personID();
  SET NEW.recipient_tfID = pref_tfID;
  SELECT customerBilledDollars,currencyTypeID INTO cbd,cur FROM project WHERE projectID = NEW.projectID;
  SET NEW.customerBilledDollars = cbd;
  SET NEW.currencyTypeID = cur;
  SET NEW.dateFrom = null;
  SET NEW.dateTo = null;
  SET NEW.approvedByManagerPersonID = null;
  SET NEW.approvedByAdminPersonID = null;
  SET NEW.dateSubmittedToManager = null;
  SET NEW.dateSubmittedToAdmin = null;
  SET NEW.dateRejected = null;
  SET NEW.invoiceDate = null;
END
$$


-- define("PERM_TIME_INVOICE_TIMESHEETS", 512); = admin
-- define("PERM_TIME_APPROVE_TIMESHEETS", 256); = manager and admin

DROP TRIGGER IF EXISTS before_update_timeSheet $$
CREATE TRIGGER before_update_timeSheet BEFORE UPDATE ON timeSheet
FOR EACH ROW
BEGIN
  DECLARE has_bastard_tasks INTEGER;
  DECLARE cbd BIGINT(20);
  DECLARE cur VARCHAR(3);

  IF (has_perm(personID(),512,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (OLD.status = 'manager' AND NEW.status = 'edit' AND has_perm(personID(),256,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (OLD.status = 'manager' AND NEW.status = 'admin' AND has_perm(personID(),256,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (neq(OLD.status, 'edit')) THEN
    call alloc_error('Time sheet is not editable(2).');
  ELSEIF (neq(NEW.status, 'edit') AND using_views()) THEN
    call alloc_error('Not permitted to change time sheet status.');
  ELSEIF (using_views()) THEN
    -- People using views can't modify the following fields
    SET NEW.timeSheetID = OLD.timeSheetID;
    SET NEW.personID = OLD.personID;
    SET NEW.status = 'edit';
    SET NEW.recipient_tfID = OLD.recipient_tfID;
    SET NEW.customerBilledDollars = OLD.customerBilledDollars;
    SET NEW.currencyTypeID = OLD.currencyTypeID;
    SET NEW.dateFrom = OLD.dateFrom;
    SET NEW.dateTo = OLD.dateTo;
    SET NEW.approvedByManagerPersonID = OLD.approvedByManagerPersonID;
    SET NEW.approvedByAdminPersonID = OLD.approvedByAdminPersonID;
    SET NEW.dateSubmittedToManager = OLD.dateSubmittedToManager;
    SET NEW.dateSubmittedToAdmin = OLD.dateSubmittedToAdmin;
    SET NEW.dateRejected = OLD.dateRejected;
    SET NEW.invoiceDate = OLD.invoiceDate;
  END IF;

  IF OLD.status = 'edit' AND NEW.status = 'edit' AND neq(OLD.projectID,NEW.projectID) THEN
    SELECT count(*) INTO has_bastard_tasks FROM timeSheetItem
 LEFT JOIN timeSheet ON timeSheet.timeSheetID = timeSheetItem.timeSheetID
 LEFT JOIN task ON timeSheetItem.taskID = task.taskID
     WHERE task.projectID != NEW.projectID
       AND timeSheetItem.timeSheetID = OLD.timeSheetID;
    IF has_bastard_tasks THEN
      call alloc_error("Task belongs to wrong project.");
    END IF;

    SELECT customerBilledDollars,currencyTypeID INTO cbd,cur FROM project WHERE projectID = NEW.projectID;
    SET NEW.customerBilledDollars = cbd;
    SET NEW.currencyTypeID = cur;
    UPDATE timeSheetItem
       SET rate = (SELECT rate FROM projectPerson WHERE projectID = NEW.projectID AND personID = personID() LIMIT 1)
     WHERE timeSheetID = OLD.timeSheetID;

  END IF;
END
$$

DROP TRIGGER IF EXISTS before_delete_timeSheet $$
CREATE TRIGGER before_delete_timeSheet BEFORE DELETE ON timeSheet
FOR EACH ROW
BEGIN
  IF (neq(OLD.status, 'edit')) THEN
    call alloc_error('Not permitted to delete time sheet unless status is edit.');
  END IF;
END
$$

-- timeSheetItem

DROP PROCEDURE IF EXISTS updateTimeSheetDates $$
CREATE PROCEDURE updateTimeSheetDates(IN id INTEGER)
BEGIN
  UPDATE timeSheet SET dateFrom = (SELECT min(dateTimeSheetItem) FROM timeSheetItem WHERE timeSheetID = id) WHERE timeSheetID = id;
  UPDATE timeSheet SET dateTo = (SELECT max(dateTimeSheetItem) FROM timeSheetItem WHERE timeSheetID = id) WHERE timeSheetID = id;
END
$$

DROP PROCEDURE IF EXISTS check_edit_timeSheet $$
CREATE PROCEDURE check_edit_timeSheet(IN id INTEGER)
BEGIN
  DECLARE timeSheetStatus VARCHAR(255);
  SELECT status INTO timeSheetStatus FROM timeSheet WHERE timeSheetID = id;
  if (neq(timeSheetStatus, "edit") AND NOT has_perm(personID(),512,"timeSheet")) THEN
    call alloc_error('Time sheet is not editable.');
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_insert_timeSheetItem $$
CREATE TRIGGER before_insert_timeSheetItem BEFORE INSERT ON timeSheetItem
FOR EACH ROW
BEGIN
  DECLARE validDate DATE;
  DECLARE pID INTEGER;
  DECLARE r BIGINT(20);
  DECLARE rUnitID INTEGER;
  DECLARE description VARCHAR(255);
  call check_edit_timeSheet(NEW.timeSheetID);

  SET NEW.timeSheetItemCreatedUser = personID();
  SET NEW.timeSheetItemCreatedTime = current_timestamp();

  SELECT DATE(NEW.dateTimeSheetItem) INTO validDate;
  IF (validDate = '0000-00-00') THEN
    call alloc_error("Invalid date.");
  END IF;

  SET NEW.personID = personID();
  SELECT projectID INTO pID FROM timeSheet WHERE timeSheet.timeSheetID = NEW.timeSheetID;
  SELECT rate,rateUnitID INTO r,rUnitID FROM projectPerson WHERE projectID = pID AND personID = personID() LIMIT 1;

  -- if rate is neq project-person rate AND they're don't have perm halt with error about rate
  IF (neq(NEW.rate,r) AND NOT can_edit_rate(personID(),pID)) THEN
    call alloc_error("Time sheet's rate is not editable.");
  END IF;

  IF (NEW.rate IS NULL AND r) THEN
    SET NEW.rate = r;
    SET NEW.timeSheetItemDurationUnitID = rUnitID;
  END IF;

  IF (NEW.taskID) THEN
    SELECT taskName INTO description FROM task WHERE taskID = NEW.taskID;
    SET NEW.description = description;
  END IF;

END
$$

DROP TRIGGER IF EXISTS after_insert_timeSheetItem $$
CREATE TRIGGER after_insert_timeSheetItem AFTER INSERT ON timeSheetItem
FOR EACH ROW
BEGIN
  DECLARE isTask BOOLEAN;
  call updateTimeSheetDates(NEW.timeSheetID);

  SELECT count(*) INTO isTask FROM task WHERE taskID = NEW.taskID AND taskStatus = 'open_notstarted';
  IF (isTask) THEN
    call change_task_status(NEW.taskID,'open_inprogress');
  END IF;
  UPDATE task SET dateActualStart = (SELECT min(dateTimeSheetItem) FROM timeSheetItem WHERE taskID = NEW.taskID)
   WHERE taskID = NEW.taskID;
END
$$

DROP TRIGGER IF EXISTS before_update_timeSheetItem $$
CREATE TRIGGER before_update_timeSheetItem BEFORE UPDATE ON timeSheetItem
FOR EACH ROW
BEGIN
  DECLARE validDate DATE;
  DECLARE pID INTEGER;
  DECLARE r BIGINT(20);
  DECLARE rUnitID INTEGER;
  DECLARE taskTitle varchar(255);
  call check_edit_timeSheet(OLD.timeSheetID);

  SET NEW.timeSheetItemModifiedUser = personID();
  SET NEW.timeSheetItemModifiedTime = current_timestamp();

  SELECT DATE(NEW.dateTimeSheetItem) INTO validDate;
  IF (validDate = '0000-00-00') THEN
    call alloc_error("Invalid date.");
  END IF;

  SET NEW.timeSheetItemID = OLD.timeSheetItemID;
  SET NEW.personID = OLD.personID;
  SELECT projectID INTO pID FROM timeSheet WHERE timeSheet.timeSheetID = NEW.timeSheetID;
  SELECT rate,rateUnitID INTO r,rUnitID FROM projectPerson WHERE projectID = pID AND personID = OLD.personID LIMIT 1;

  -- if rate is neq project-person rate AND they're don't have perm halt with error about rate
  IF (neq(NEW.rate,r) AND NOT can_edit_rate(personID(),pID)) THEN
    call alloc_error("Time sheet's rate is not editable.");
  END IF;

  IF (NEW.rate IS NULL AND r) THEN
    SET NEW.rate = r;
    SET NEW.timeSheetItemDurationUnitID = rUnitID;
  END IF;
  SELECT taskName INTO taskTitle FROM task WHERE taskID = NEW.taskID;
  SET NEW.description = taskTitle;
END
$$

DROP TRIGGER IF EXISTS after_update_timeSheetItem $$
CREATE TRIGGER after_update_timeSheetItem AFTER UPDATE ON timeSheetItem
FOR EACH ROW
BEGIN
  IF (neq(OLD.dateTimeSheetItem, NEW.dateTimeSheetItem)) THEN
    call updateTimeSheetDates(NEW.timeSheetID);
  END IF;
  UPDATE task SET dateActualStart = (SELECT min(dateTimeSheetItem) FROM timeSheetItem WHERE taskID = NEW.taskID)
   WHERE taskID = NEW.taskID;
END
$$

DROP TRIGGER IF EXISTS before_delete_timeSheetItem $$
CREATE TRIGGER before_delete_timeSheetItem BEFORE DELETE ON timeSheetItem
FOR EACH ROW
BEGIN
  call check_edit_timeSheet(OLD.timeSheetID);
END
$$

DROP TRIGGER IF EXISTS after_delete_timeSheetItem $$
CREATE TRIGGER after_delete_timeSheetItem AFTER DELETE ON timeSheetItem
FOR EACH ROW
BEGIN
  call updateTimeSheetDates(OLD.timeSheetID);
  UPDATE task SET dateActualStart = (SELECT min(dateTimeSheetItem) FROM timeSheetItem WHERE taskID = OLD.taskID)
   WHERE taskID = OLD.taskID;
END
$$

-- task perm

DROP PROCEDURE IF EXISTS check_edit_task $$
CREATE PROCEDURE check_edit_task(IN id INTEGER)
BEGIN
  DECLARE count_project INTEGER;
  SELECT count(project.projectID) INTO count_project
    FROM project LEFT JOIN projectPerson ON projectPerson.projectID = project.projectID 
   WHERE project.projectID = id AND projectPerson.personID = personID();

  IF (id AND count_project = 0 AND NOT has_perm(personID(),15,"task")) THEN
    call alloc_error('Task is not editable: user not a project member.');
  END IF;

  IF (id AND NOT has_perm(personID(),15,"task")) THEN
    call alloc_error('Task is not editable.');
  END IF;
END
$$

DROP PROCEDURE IF EXISTS check_delete_task $$
CREATE PROCEDURE check_delete_task(IN id INTEGER)
BEGIN
  IF (!can_delete_task(id)) THEN
    call alloc_error('Task is not deletable.');
  END IF;
END
$$

-- This function is used in PHP land as well
DROP FUNCTION IF EXISTS can_delete_task $$
CREATE FUNCTION can_delete_task(id INTEGER) RETURNS BOOLEAN READS SQL DATA
BEGIN
  DECLARE num_audits INTEGER;
  SELECT COUNT(*) INTO num_audits FROM auditItem WHERE entityName = 'task' AND entityID = id;
  -- perm delete = 4
  IF (NOT num_audits AND has_perm(personID(),4,"task")) THEN
    RETURN TRUE;
  END IF;
  RETURN FALSE;
END
$$

-- task

DROP TRIGGER IF EXISTS before_insert_task $$
CREATE TRIGGER before_insert_task BEFORE INSERT ON task
FOR EACH ROW
BEGIN
  DECLARE defTaskLimit DECIMAL(7,2);
  call check_edit_task(NEW.projectID);

  SET NEW.creatorID = personID();
  SET NEW.dateCreated = current_timestamp();

  -- inserted closed edge-case
  IF (substring(NEW.taskStatus,1,6) = 'closed') THEN
    SET NEW.dateActualCompletion = current_date();
  END IF;

  IF (empty(NEW.taskStatus)) THEN SET NEW.taskStatus = 'open_notstarted'; END IF;
  IF (empty(NEW.priority)) THEN SET NEW.priority = 3; END IF;
  IF (empty(NEW.taskTypeID)) THEN SET NEW.taskTypeID = 'Task'; END IF;
  IF (NEW.personID) THEN SET NEW.dateAssigned = current_timestamp(); END IF;
  IF (NEW.closerID) THEN SET NEW.dateClosed = current_timestamp(); END IF;
  IF (empty(NEW.timeLimit)) THEN SET NEW.timeLimit = NEW.timeExpected; END IF;
  
  IF (empty(NEW.timeLimit) AND NEW.projectID) THEN
    SELECT defaultTaskLimit INTO defTaskLimit FROM project WHERE projectID = NEW.projectID;
    SET NEW.timeLimit = defTaskLimit;
  END IF;
 
  IF (empty(NEW.estimatorID) AND (NEW.timeWorst OR NEW.timeBest OR NEW.timeExpected)) THEN
    SET NEW.estimatorID = personID();
  END IF;

  IF (empty(NEW.timeWorst) AND empty(NEW.timeBest) AND empty(NEW.timeExpected)) THEN
    SET NEW.estimatorID = NULL;
  END IF;

  IF (NEW.taskStatus = 'open_inprogress' AND empty(NEW.dateActualStart)) THEN
    SET NEW.dateActualStart = current_date();
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_update_task $$
CREATE TRIGGER before_update_task BEFORE UPDATE ON task
FOR EACH ROW
BEGIN
  call check_edit_task(OLD.projectID);

  IF (neq(@in_change_task_status,1) AND neq(OLD.taskStatus,NEW.taskStatus)) THEN
    call alloc_error('Must use: call change_task_status(taskID,status)');
  END IF;

  SET NEW.taskID = OLD.taskID;
  SET NEW.creatorID = OLD.creatorID;
  SET NEW.dateCreated = OLD.dateCreated;
  SET NEW.taskModifiedUser = personID();

  IF (empty(NEW.taskStatus)) THEN
    SET NEW.taskStatus = OLD.taskStatus;
  END IF;

  IF (empty(NEW.taskStatus)) THEN
    SET NEW.taskStatus = 'open_notstarted';
  END IF;

  IF (NEW.taskStatus = 'open_inprogress' AND neq(NEW.taskStatus, OLD.taskStatus) AND empty(NEW.dateActualStart)) THEN
    SET NEW.dateActualStart = current_date();
  END IF;

  IF ((SUBSTRING(NEW.taskStatus,1,4) = 'open' OR SUBSTRING(NEW.taskStatus,1,4) = 'pend')) THEN
    SET NEW.closerID = NULL;  
    SET NEW.dateClosed = NULL;  
    SET NEW.dateActualCompletion = NULL;  
    SET NEW.duplicateTaskID = NULL;  
  ELSEIF (SUBSTRING(NEW.taskStatus,1,6) = 'closed' AND neq(NEW.taskStatus, OLD.taskStatus)) THEN
    IF (empty(NEW.dateActualStart)) THEN SET NEW.dateActualStart = current_date(); END IF;
    IF (empty(NEW.dateClosed)) THEN SET NEW.dateClosed = current_timestamp(); END IF;
    IF (empty(NEW.closerID)) THEN SET NEW.closerID = personID(); END IF;
    SET NEW.dateActualCompletion = current_date();
  END IF;

  IF (NEW.personID AND neq(NEW.personID, OLD.personID)) THEN
    SET NEW.dateAssigned = current_timestamp();
  ELSEIF (empty(NEW.personID)) THEN
    SET NEW.dateAssigned = NULL;
  END IF;

  IF (NEW.closerID AND neq(NEW.closerID, OLD.closerID)) THEN
    SET NEW.dateClosed = current_timestamp();
  ELSEIF (empty(NEW.closerID)) THEN
    SET NEW.dateClosed = NULL;
  END IF;

  IF ((neq(NEW.timeWorst, OLD.timeWorst) OR neq(NEW.timeBest, OLD.timeBest) OR neq(NEW.timeExpected, OLD.timeExpected))
  AND empty(NEW.estimatorID)) THEN
    SET NEW.estimatorID = personID();
  END IF;

  IF (empty(NEW.timeWorst) AND empty(NEW.timeBest) AND empty(NEW.timeExpected)) THEN
    SET NEW.estimatorID = NULL;
  END IF;

END
$$

DROP TRIGGER IF EXISTS before_delete_task $$
CREATE TRIGGER before_delete_task BEFORE DELETE ON task
FOR EACH ROW
BEGIN
  call check_edit_task(OLD.projectID);
  call check_delete_task(OLD.taskID);
  DELETE FROM pendingTask WHERE taskID = OLD.taskID OR pendingTaskID = OLD.taskID;
END
$$

DROP TRIGGER IF EXISTS after_insert_task $$
CREATE TRIGGER after_insert_task AFTER INSERT ON task
FOR EACH ROW
BEGIN
  call update_search_index("task",NEW.taskID);
END
$$

DROP TRIGGER IF EXISTS after_update_task $$
CREATE TRIGGER after_update_task AFTER UPDATE ON task
FOR EACH ROW
BEGIN
  call alloc_log("task", OLD.taskID, "taskName",             OLD.taskName,             NEW.taskName);
  call alloc_log("task", OLD.taskID, "taskDescription",      OLD.taskDescription,      NEW.taskDescription);
  call alloc_log("task", OLD.taskID, "priority",             OLD.priority,             NEW.priority);
  call alloc_log("task", OLD.taskID, "timeLimit",            OLD.timeLimit,            NEW.timeLimit);
  call alloc_log("task", OLD.taskID, "timeBest",             OLD.timeBest,             NEW.timeBest);
  call alloc_log("task", OLD.taskID, "timeWorst",            OLD.timeWorst,            NEW.timeWorst);
  call alloc_log("task", OLD.taskID, "timeExpected",         OLD.timeExpected,         NEW.timeExpected);
  call alloc_log("task", OLD.taskID, "dateTargetStart",      OLD.dateTargetStart,      NEW.dateTargetStart);
  call alloc_log("task", OLD.taskID, "dateActualStart",      OLD.dateActualStart,      NEW.dateActualStart);
  call alloc_log("task", OLD.taskID, "projectID",            OLD.projectID,            NEW.projectID);
  call alloc_log("task", OLD.taskID, "parentTaskID",         OLD.parentTaskID,         NEW.parentTaskID);
  call alloc_log("task", OLD.taskID, "taskTypeID",           OLD.taskTypeID,           NEW.taskTypeID);
  call alloc_log("task", OLD.taskID, "personID",             OLD.personID,             NEW.personID);
  call alloc_log("task", OLD.taskID, "managerID",            OLD.managerID,            NEW.managerID);
  call alloc_log("task", OLD.taskID, "estimatorID",          OLD.estimatorID,          NEW.estimatorID);
  call alloc_log("task", OLD.taskID, "duplicateTaskID",      OLD.duplicateTaskID,      NEW.duplicateTaskID);
  call alloc_log("task", OLD.taskID, "dateTargetCompletion", OLD.dateTargetCompletion, NEW.dateTargetCompletion);
  call alloc_log("task", OLD.taskID, "dateActualCompletion", OLD.dateActualCompletion, NEW.dateActualCompletion);
  call alloc_log("task", OLD.taskID, "taskStatus",           OLD.taskStatus,           NEW.taskStatus);
  call update_search_index("task",NEW.taskID);
END
$$

-- pendingTask

DROP PROCEDURE IF EXISTS change_task_status $$
CREATE PROCEDURE change_task_status(tID INTEGER, new_status varchar(255))
BEGIN
  -- declare statements must be at the top
  DECLARE no_more_rows BOOLEAN;
  DECLARE num_records INTEGER;
  DECLARE task_that_is_pending INTEGER;
  DECLARE old_status VARCHAR(255);
  DECLARE num_pending_tasks INTEGER;
  DECLARE pending_tasks_cursor CURSOR FOR SELECT taskID FROM pendingTask WHERE pendingTaskID = tID;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_rows = TRUE;
  SET max_sp_recursion_depth = 10; 
  SET @in_change_task_status = 1;

  SELECT taskStatus INTO old_status FROM task WHERE taskID = tID;

  -- If just moved from anything to closed
  IF (neq(SUBSTRING(old_status,1,6),'closed') AND SUBSTRING(new_status,1,6) = 'closed') THEN

    -- Walk through a set of rows using a mysql cursor
    OPEN pending_tasks_cursor;
    the_loop: LOOP
      
      -- This loads the taskID results of the select query, defined above, into the loop variable
      FETCH pending_tasks_cursor INTO task_that_is_pending;

      -- We're looking for records that might prevent the cascading opening of pending tasks
      SELECT count(pendingTask.taskID) INTO num_records
        FROM pendingTask
   LEFT JOIN task ON task.taskID = pendingTask.pendingTaskID
       WHERE pendingTask.taskID = task_that_is_pending
         AND pendingTask.pendingTaskID != tID
         AND SUBSTRING(task.taskStatus,1,6) != 'closed';

      -- If there aren't any other open tasks, then try and change the status
      IF (num_records = 0) THEN
        -- first close off the dependence
        UPDATE task SET taskStatus = new_status WHERE taskID = tID AND taskStatus != new_status;
        call change_task_status(task_that_is_pending,"open_notstarted");
        -- this needs to be set again
        SET @in_change_task_status = 1; 
      END IF;

      IF no_more_rows THEN
        CLOSE pending_tasks_cursor;
        LEAVE the_loop;
      END IF;
    END LOOP the_loop;

    SET @in_change_task_status = 1; 

  -- Else if just moved from closed to open or pending
  ELSEIF (SUBSTRING(old_status,1,6) = 'closed' AND neq(SUBSTRING(new_status,1,6),'closed')) THEN
    -- Move all the tasks that were depending on the just opened task, to pending_tasks
    UPDATE task SET taskStatus = 'pending_tasks'
     WHERE taskStatus = 'open_notstarted'
       AND taskID IN (SELECT taskID FROM pendingTask WHERE pendingTaskID = tID);
  END IF;

  
  -- If just moved from pending_tasks to anything else ...
  -- If we're still waiting on other tasks to complete, then bomb out with an error
  IF (old_status = 'pending_tasks' AND neq(old_status,new_status)) THEN
     SELECT count(pendingTask.taskID) INTO num_pending_tasks FROM pendingTask
  LEFT JOIN task ON task.taskID = pendingTask.pendingTaskID
      WHERE pendingTask.taskID = tID
        AND SUBSTRING(task.taskStatus,1,6) != 'closed';
 
    IF (num_pending_tasks > 0) THEN
      call alloc_error('Task has pending tasks.');
    END IF;
  END IF;

    -- And finally, update the task
  IF (neq(old_status,new_status)) THEN
    UPDATE task SET taskStatus = new_status WHERE taskID = tID;
  END IF;

  SET @in_change_task_status = 0;
END
$$

DROP TRIGGER IF EXISTS before_insert_pendingTask $$
CREATE TRIGGER before_insert_pendingTask BEFORE INSERT ON pendingTask
FOR EACH ROW
BEGIN
  DECLARE pID INTEGER;
  SELECT projectID INTO pID FROM task WHERE taskID = NEW.taskID;
  call check_edit_task(pID);
  IF (NEW.taskID = NEW.pendingTaskID) THEN
    call alloc_error('Task cannot be pending itself.');
  END IF;
END
$$

DROP TRIGGER IF EXISTS after_insert_pendingTask $$
CREATE TRIGGER after_insert_pendingTask AFTER INSERT ON pendingTask
FOR EACH ROW
BEGIN
  DECLARE num_rows INTEGER;
  DECLARE t1status varchar(255);
  DECLARE t2status varchar(255);

  -- inserted a new dependency relationship, might need to make the task pending
  SELECT taskStatus INTO t1status FROM task WHERE taskID = NEW.taskID;
  SELECT taskStatus INTO t2status FROM task WHERE taskID = NEW.pendingTaskID;
  IF (neq(t1status,"pending_tasks") AND neq(SUBSTRING(t2status,1,6),"closed")) THEN
    call change_task_status(NEW.taskID,"pending_tasks");
  END IF;

  -- or might have inserted a closed task, so if there's no open dependencies, open the task
  IF (t1status = "pending_tasks") THEN
    SELECT count(pendingTask.taskID) INTO num_rows FROM pendingTask
 LEFT JOIN task ON task.taskID = pendingTask.pendingTaskID
     WHERE pendingTask.taskID = NEW.taskID
       AND SUBSTRING(task.taskStatus,1,6) != "closed";
  
    IF (num_rows = 0) THEN
      call change_task_status(NEW.taskID,"open_notstarted");
    END IF;
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_update_pendingTask $$
CREATE TRIGGER before_update_pendingTask BEFORE UPDATE ON pendingTask
FOR EACH ROW
BEGIN
  DECLARE pID INTEGER;
  SELECT projectID INTO pID FROM task WHERE taskID = NEW.taskID;
  call check_edit_task(pID);

  IF (NEW.taskID = NEW.pendingTaskID) THEN
    call alloc_error('Task cannot be pending itself.');
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_delete_pendingTask $$
CREATE TRIGGER before_delete_pendingTask BEFORE DELETE ON pendingTask
FOR EACH ROW
BEGIN
  DECLARE pID INTEGER;
  SELECT projectID INTO pID FROM task WHERE taskID = OLD.taskID;
  call check_edit_task(pID);
END
$$

DROP TRIGGER IF EXISTS after_delete_pendingTask $$
CREATE TRIGGER after_delete_pendingTask AFTER DELETE ON pendingTask
FOR EACH ROW
BEGIN
  DECLARE num_rows INTEGER;
  DECLARE t1status varchar(255);

  SELECT taskStatus INTO t1status FROM task WHERE taskID = OLD.taskID;
  IF (t1status = "pending_tasks") THEN

    -- if all the others are closed, then move task from pending to open
    SELECT count(pendingTask.taskID) INTO num_rows FROM pendingTask
 LEFT JOIN task ON task.taskID = pendingTask.pendingTaskID
     WHERE pendingTask.taskID = OLD.taskID
       AND SUBSTRING(task.taskStatus,1,6) != "closed";
  
    IF (num_rows = 0) THEN
      call change_task_status(OLD.taskID,"open_notstarted");
    END IF;
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_delete_reminder $$
CREATE TRIGGER before_delete_reminder BEFORE DELETE ON reminder
FOR EACH ROW
BEGIN
  IF (has_perm(personID(),4,"reminder")) THEN
    DELETE FROM reminderRecipient WHERE reminderID = OLD.reminderID;
  END IF;
END
$$

DROP TRIGGER IF EXISTS after_update_project $$
CREATE TRIGGER after_update_project AFTER UPDATE ON project
FOR EACH ROW
BEGIN
  call alloc_log("project", OLD.projectID, "projectName",             OLD.projectName,             NEW.projectName);
  call alloc_log("project", OLD.projectID, "projectShortName",        OLD.projectShortName,        NEW.projectShortName);
  call alloc_log("project", OLD.projectID, "projectComments",         OLD.projectComments,         NEW.projectComments);
  call alloc_log("project", OLD.projectID, "clientID",                OLD.clientID,                NEW.clientID);
  call alloc_log("project", OLD.projectID, "clientContactID",         OLD.clientContactID,         NEW.clientContactID);
  call alloc_log("project", OLD.projectID, "projectType",             OLD.projectType,             NEW.projectType);
  call alloc_log("project", OLD.projectID, "dateTargetStart",         OLD.dateTargetStart,         NEW.dateTargetStart);
  call alloc_log("project", OLD.projectID, "dateTargetCompletion",    OLD.dateTargetCompletion,    NEW.dateTargetCompletion);
  call alloc_log("project", OLD.projectID, "dateActualStart",         OLD.dateActualStart,         NEW.dateActualStart);
  call alloc_log("project", OLD.projectID, "dateActualCompletion",    OLD.dateActualCompletion,    NEW.dateActualCompletion);
  call alloc_log("project", OLD.projectID, "projectBudget",           OLD.projectBudget,           NEW.projectBudget);
  call alloc_log("project", OLD.projectID, "currencyTypeID",          OLD.currencyTypeID,          NEW.currencyTypeID);
  call alloc_log("project", OLD.projectID, "projectStatus",           OLD.projectStatus,           NEW.projectStatus);
  call alloc_log("project", OLD.projectID, "projectPriority",         OLD.projectPriority,         NEW.projectPriority);
  call alloc_log("project", OLD.projectID, "cost_centre_tfID",        OLD.cost_centre_tfID,        NEW.cost_centre_tfID);
  call alloc_log("project", OLD.projectID, "customerBilledDollars",   OLD.customerBilledDollars,   NEW.customerBilledDollars);
  call alloc_log("project", OLD.projectID, "defaultTaskLimit",        OLD.defaultTaskLimit,        NEW.defaultTaskLimit);
  call alloc_log("project", OLD.projectID, "defaultTimeSheetRate",    OLD.defaultTimeSheetRate,    NEW.defaultTimeSheetRate);
  call alloc_log("project", OLD.projectID, "defaultTimeSheetRateUnitID",OLD.defaultTimeSheetRateUnitID,NEW.defaultTimeSheetRateUnitID);
  call update_search_index("project",NEW.projectID);
END
$$

DELIMITER ;
