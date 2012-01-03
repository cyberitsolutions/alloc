-- This script should be imported into the main production alloc database
-- It can be re-imported repeatedly and it should rebuild clean every time

DELIMITER $$

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
  ELSEIF (OLD.status != 'edit') THEN
    call alloc_error('Time sheet is not editable.');
  ELSEIF (NEW.status != 'edit') THEN
    call alloc_error('Not permitted to change time sheet status.');
  ELSE
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
  IF (OLD.status != 'edit') THEN
    call alloc_error('Not permitted to delete time sheet unless status is edit.');
  ELSE
    DELETE FROM timeSheetItem WHERE timeSheetID = OLD.timeSheetID;
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
  if (@timeSheetStatus != "edit") THEN
    call alloc_error('Time sheet is not editable.');
  END IF;
END
$$

DROP TRIGGER IF EXISTS before_insert_timeSheetItem $$
CREATE TRIGGER before_insert_timeSheetItem BEFORE INSERT ON timeSheetItem
FOR EACH ROW
BEGIN
  call check_edit_timeSheet(NEW.timeSheetID);
  SET NEW.personID = personID();
  SELECT projectID INTO @projectID FROM timeSheet WHERE timeSheet.timeSheetID = NEW.timeSheetID;
  SELECT rate,rateUnitID INTO @rate,@rateUnitID FROM projectPerson WHERE projectID = @projectID AND personID = personID();
  IF (@rate) THEN
    SET NEW.rate = @rate;
    SET NEW.timeSheetItemDurationUnitID = @rateUnitID;
  END IF;
END
$$

DROP TRIGGER IF EXISTS after_insert_timeSheetItem $$
CREATE TRIGGER after_insert_timeSheetItem AFTER INSERT ON timeSheetItem
FOR EACH ROW
  call updateTimeSheetDates(NEW.timeSheetID);
$$

DROP TRIGGER IF EXISTS before_update_timeSheetItem $$
CREATE TRIGGER before_update_timeSheetItem BEFORE UPDATE ON timeSheetItem
FOR EACH ROW
BEGIN
  call check_edit_timeSheet(OLD.timeSheetID);
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


-- audit 

DROP PROCEDURE IF EXISTS log $$
CREATE PROCEDURE log(entityName VARCHAR(255), entityID INTEGER, fieldName VARCHAR(255), oldValue TEXT, newValue TEXT)
BEGIN
  IF (oldValue != newValue) THEN
    INSERT INTO auditItem (entityName,entityID,personID,dateChanged,changeType,fieldName,oldValue) VALUES
    (entityName,entityID,personID(),NOW(),"FieldChange",fieldName,oldValue);
  END IF;
END
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

-- task

DROP TRIGGER IF EXISTS before_update_task $$
CREATE TRIGGER before_update_task BEFORE UPDATE ON task
FOR EACH ROW
BEGIN
  call check_edit_task(OLD.projectID);
END
$$

DROP TRIGGER IF EXISTS before_delete_task $$
CREATE TRIGGER before_delete_task BEFORE DELETE ON task
FOR EACH ROW
BEGIN
  call check_edit_task(OLD.projectID);
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
  call log("task", OLD.taskID, "taskStatus",           OLD.taskStatus,           NEW.taskStatus);
  call log("task", OLD.taskID, "projectID",            OLD.projectID,            NEW.projectID);
  call log("task", OLD.taskID, "parentTaskID",         OLD.parentTaskID,         NEW.parentTaskID);
  call log("task", OLD.taskID, "taskTypeID",           OLD.taskTypeID,           NEW.taskTypeID);
  call log("task", OLD.taskID, "personID",             OLD.personID,             NEW.personID);
  call log("task", OLD.taskID, "managerID",            OLD.managerID,            NEW.managerID);
  call log("task", OLD.taskID, "estimatorID",          OLD.estimatorID,          NEW.estimatorID);
  call log("task", OLD.taskID, "duplicateTaskID",      OLD.duplicateTaskID,      NEW.duplicateTaskID);
  call log("task", OLD.taskID, "dateTargetCompletion", OLD.dateTargetCompletion, NEW.dateTargetCompletion);
  call log("task", OLD.taskID, "dateActualCompletion", OLD.dateActualCompletion, NEW.dateActualCompletion);
END
$$





DELIMITER ;
