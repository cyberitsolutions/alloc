-- This script should be imported into the main production alloc database
-- It can be re-imported repeatedly and it should rebuild clean every time


-- Error messages for mysql
DELETE FROM error;
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to change time sheet status.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Not permitted to delete time sheet unless status is edit.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Time sheet is not editable.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not editable.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Invalid date.\n\n");
INSERT INTO error (errorID) VALUES ("\n\nALLOC ERROR: Task is not deletable.\n\n");


DELIMITER $$

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
    SELECT personID INTO @personID FROM person WHERE username = SUBSTRING(USER(),1,LOCATE("@",USER())-1);
    RETURN @personID;
  END IF;
END
$$

-- has_perm

DROP FUNCTION IF EXISTS has_perm $$
CREATE FUNCTION has_perm(pID INTEGER, action INTEGER, tableN VARCHAR(255)) RETURNS BOOLEAN READS SQL DATA
BEGIN
  SELECT perms INTO @perms FROM person WHERE personID = pID;
  SELECT count(*) INTO @rtn
    FROM permission
   WHERE tableName = tableN
     AND (actions & action = action)
     AND (roleName IS NULL OR roleName = '' OR find_in_set(roleName,@perms)>0);
  return @rtn;
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

DROP PROCEDURE IF EXISTS log $$
CREATE PROCEDURE log(entityName VARCHAR(255), entityID INTEGER, fieldName VARCHAR(255), oldValue TEXT, newValue TEXT)
BEGIN
  IF (neq(oldValue,newValue)) THEN
    INSERT INTO auditItem (entityName,entityID,personID,dateChanged,changeType,fieldName,oldValue) VALUES
    (entityName,entityID,personID(),NOW(),"FieldChange",fieldName,oldValue);
  END IF;
END
$$



-- triggers for timeSheet

DROP TRIGGER IF EXISTS before_insert_timeSheet $$
CREATE TRIGGER before_insert_timeSheet BEFORE INSERT ON timeSheet
FOR EACH ROW
BEGIN
  SET NEW.personID = personID();
  SET NEW.status = 'edit';
  SELECT preferred_tfID INTO @preferred_tfID FROM person WHERE personID = personID();
  SET NEW.recipient_tfID = @preferred_tfID;
  SELECT customerBilledDollars,currencyTypeID INTO @cbd,@cur FROM project WHERE projectID = NEW.projectID;
  SET NEW.customerBilledDollars = @cbd;
  SET NEW.currencyTypeID = @cur;
END
$$


-- define("PERM_TIME_INVOICE_TIMESHEETS", 512);
-- define("PERM_TIME_APPROVE_TIMESHEETS", 256);

DROP TRIGGER IF EXISTS before_update_timeSheet $$
CREATE TRIGGER before_update_timeSheet BEFORE UPDATE ON timeSheet
FOR EACH ROW
BEGIN
  IF (has_perm(personID(),512,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (OLD.status = 'manage' AND NEW.status = 'edit' AND has_perm(personID(),256,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (OLD.status = 'manage' AND NEW.status = 'admin' AND has_perm(personID(),256,"timeSheet")) THEN
    SELECT 1 INTO @null;
  ELSEIF (neq(OLD.status, 'edit')) THEN
    call alloc_error('Time sheet is not editable.');
  ELSEIF (neq(NEW.status, 'edit')) THEN
    call alloc_error('Not permitted to change time sheet status.');
  ELSE
    SET NEW.timeSheetID = OLD.timeSheetID;
    SET NEW.personID = OLD.personID;
    SET NEW.status = 'edit';
    SELECT preferred_tfID INTO @preferred_tfID FROM person WHERE personID = OLD.personID;
    SET NEW.recipient_tfID = @preferred_tfID;
    SELECT customerBilledDollars,currencyTypeID INTO @cbd,@cur FROM project WHERE projectID = NEW.projectID;
    SET NEW.customerBilledDollars = @cbd;
    SET NEW.currencyTypeID = @cur;
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_delete_timeSheet $$
CREATE TRIGGER before_delete_timeSheet BEFORE DELETE ON timeSheet
FOR EACH ROW
BEGIN
  IF (neq(OLD.status, 'edit')) THEN
    call alloc_error('Not permitted to delete time sheet unless status is edit.');
  -- ELSE
    -- This don't work.
    -- DELETE FROM timeSheetItem WHERE timeSheetID = OLD.timeSheetID;
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
  SELECT status INTO @timeSheetStatus FROM timeSheet WHERE timeSheetID = id;
  if (neq(@timeSheetStatus, "edit")) THEN
    call alloc_error('Time sheet is not editable.');
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_insert_timeSheetItem $$
CREATE TRIGGER before_insert_timeSheetItem BEFORE INSERT ON timeSheetItem
FOR EACH ROW
BEGIN
  call check_edit_timeSheet(NEW.timeSheetID);

  SELECT DATE(NEW.dateTimeSheetItem) INTO @validDate;
  IF (@validDate = '0000-00-00') THEN
    call alloc_error("Invalid date.");
    -- SET NEW.dateTimeSheetItem = NULL;
  END IF;

  SET NEW.personID = personID();
  SELECT projectID INTO @projectID FROM timeSheet WHERE timeSheet.timeSheetID = NEW.timeSheetID;
  SELECT rate,rateUnitID INTO @rate,@rateUnitID FROM projectPerson WHERE projectID = @projectID AND personID = personID();
  IF (@rate) THEN
    SET NEW.rate = @rate;
    SET NEW.timeSheetItemDurationUnitID = @rateUnitID;
  END IF;

  IF (NEW.taskID) THEN
    SELECT taskName INTO @taskName FROM task WHERE taskID = NEW.taskID;
    SET NEW.description = @taskName;
  END IF;

END
$$

DROP TRIGGER IF EXISTS after_insert_timeSheetItem $$
CREATE TRIGGER after_insert_timeSheetItem AFTER INSERT ON timeSheetItem
FOR EACH ROW
BEGIN
  call updateTimeSheetDates(NEW.timeSheetID);

  SELECT count(*) INTO @isTask FROM task WHERE taskID = NEW.taskID AND taskStatus = 'open_notstarted';
  IF (@isTask) THEN
    UPDATE TASK SET taskStatus = 'open_inprogress' WHERE taskID = NEW.taskID;
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_update_timeSheetItem $$
CREATE TRIGGER before_update_timeSheetItem BEFORE UPDATE ON timeSheetItem
FOR EACH ROW
BEGIN
  call check_edit_timeSheet(OLD.timeSheetID);

  SELECT DATE(NEW.dateTimeSheetItem) INTO @validDate;
  IF (@validDate = '0000-00-00') THEN
    call alloc_error("Invalid date.");
    -- SET NEW.dateTimeSheetItem = NULL;
  END IF;

  SET NEW.timeSheetItemID = OLD.timeSheetItemID;
  SET NEW.personID = OLD.personID;
  SELECT projectID INTO @projectID FROM timeSheet WHERE timeSheet.timeSheetID = NEW.timeSheetID;
  SELECT rate,rateUnitID INTO @rate,@rateUnitID FROM projectPerson WHERE projectID = @projectID AND personID = OLD.personID;
  IF (@rate) THEN
    SET NEW.rate = @rate;
    SET NEW.timeSheetItemDurationUnitID = @rateUnitID;
  END IF;
END
$$

DROP TRIGGER IF EXISTS after_update_timeSheetItem $$
CREATE TRIGGER after_update_timeSheetItem AFTER UPDATE ON timeSheetItem
FOR EACH ROW
  call updateTimeSheetDates(NEW.timeSheetID);
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
  call updateTimeSheetDates(OLD.timeSheetID);
$$

-- task perm

DROP PROCEDURE IF EXISTS check_edit_task $$
CREATE PROCEDURE check_edit_task(IN id INTEGER)
BEGIN
  SELECT count(project.projectID) INTO @count_project
    FROM project LEFT JOIN projectPerson ON projectPerson.projectID = project.projectID 
   WHERE project.projectID = id AND projectPerson.personID = personID();

  IF (@count_project = 0 OR NOT has_perm(personID(),15,"task")) THEN
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
  SELECT COUNT(*) INTO @num_audits FROM auditItem WHERE entityName = 'task' AND entityID = id;
  -- perm delete = 4
  IF (NOT @num_audits AND has_perm(personID(),4,"task")) THEN
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
  call check_edit_task(NEW.projectID);

  SET NEW.creatorID = personID();
  SET NEW.dateCreated = current_timestamp();

  -- inserted closed edge-case
  IF (NEW.dateActualCompletion AND neq(substring(NEW.taskStatus,1,5), 'close')) THEN
    SET NEW.taskStatus = 'closed_complete';
  END IF;

  IF (empty(NEW.taskStatus)) THEN SET NEW.taskStatus = 'open_notstarted'; END IF;
  IF (empty(NEW.priority)) THEN SET NEW.priority = 3; END IF;
  IF (empty(NEW.taskTypeID)) THEN SET NEW.taskTypeID = 'Task'; END IF;
  IF (NEW.personID) THEN SET NEW.dateAssigned = current_timestamp(); END IF;
  IF (NEW.closerID) THEN SET NEW.dateClosed = current_timestamp(); END IF;
  IF (empty(NEW.timeLimit)) THEN SET NEW.timeLimit = NEW.timeExpected; END IF;
  
  IF (empty(NEW.timeLimit) AND NEW.projectID) THEN
    SELECT defaultTaskLimit INTO @defaultTaskLimit FROM project WHERE projectID = NEW.projectID;
    SET NEW.timeLimit = @defaultTaskLimit;
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

  IF ((SUBSTRING(NEW.taskStatus,1,4) = 'open' OR SUBSTRING(NEW.taskStatus,1,4) = 'pend') AND neq(NEW.taskStatus, OLD.taskStatus)) THEN
    SET NEW.closerID = NULL;  
    SET NEW.dateClosed = NULL;  
    SET NEW.dateActualCompletion = NULL;  
    SET NEW.duplicateTaskID = NULL;  
  END IF;

  IF (SUBSTRING(NEW.taskStatus,1,4) = 'clos' AND neq(NEW.taskStatus, OLD.taskStatus)) THEN
    IF (empty(NEW.dateActualStart)) THEN SET NEW.dateActualStart = current_date(); END IF;
    IF (empty(NEW.dateActualCompletion)) THEN SET NEW.dateActualCompletion = current_date(); END IF;
    IF (empty(NEW.dateClosed)) THEN SET NEW.dateClosed = current_timestamp(); END IF;
    IF (empty(NEW.closerID)) THEN SET NEW.closerID = personID(); END IF;

    -- MySQL don't like this unfortunately.
    -- IF (NEW.taskTypeID = 'Parent') THEN
    --   UPDATE task SET taskStatus = NEW.taskStatus WHERE parentTaskID = NEW.taskID;
    -- END IF;

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

  IF (neq(NEW.timeWorst, OLD.timeWorst) OR neq(NEW.timeBest, OLD.timeBest) OR neq(NEW.timeExpected, OLD.timeExpected)) THEN
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
END
$$

DROP TRIGGER IF EXISTS after_update_task $$
CREATE TRIGGER after_update_task AFTER UPDATE ON task
FOR EACH ROW
BEGIN
  call log("task", OLD.taskID, "taskName",             OLD.taskName,             NEW.taskName);
  call log("task", OLD.taskID, "taskDescription",      OLD.taskDescription,      NEW.taskDescription);
  call log("task", OLD.taskID, "priority",             OLD.priority,             NEW.priority);
  call log("task", OLD.taskID, "timeLimit",            OLD.timeLimit,            NEW.timeLimit);
  call log("task", OLD.taskID, "timeBest",             OLD.timeBest,             NEW.timeBest);
  call log("task", OLD.taskID, "timeWorst",            OLD.timeWorst,            NEW.timeWorst);
  call log("task", OLD.taskID, "timeExpected",         OLD.timeExpected,         NEW.timeExpected);
  call log("task", OLD.taskID, "dateTargetStart",      OLD.dateTargetStart,      NEW.dateTargetStart);
  call log("task", OLD.taskID, "dateActualStart",      OLD.dateActualStart,      NEW.dateActualStart);
  call log("task", OLD.taskID, "projectID",            OLD.projectID,            NEW.projectID);
  call log("task", OLD.taskID, "parentTaskID",         OLD.parentTaskID,         NEW.parentTaskID);
  call log("task", OLD.taskID, "taskTypeID",           OLD.taskTypeID,           NEW.taskTypeID);
  call log("task", OLD.taskID, "personID",             OLD.personID,             NEW.personID);
  call log("task", OLD.taskID, "managerID",            OLD.managerID,            NEW.managerID);
  call log("task", OLD.taskID, "estimatorID",          OLD.estimatorID,          NEW.estimatorID);
  call log("task", OLD.taskID, "duplicateTaskID",      OLD.duplicateTaskID,      NEW.duplicateTaskID);
  call log("task", OLD.taskID, "dateTargetCompletion", OLD.dateTargetCompletion, NEW.dateTargetCompletion);
  call log("task", OLD.taskID, "dateActualCompletion", OLD.dateActualCompletion, NEW.dateActualCompletion);
  call log("task", OLD.taskID, "taskStatus",           OLD.taskStatus,           NEW.taskStatus);
END
$$





DELIMITER ;
