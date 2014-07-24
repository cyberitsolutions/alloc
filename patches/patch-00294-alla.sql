-- Fix up case insensitivity problem with alloc-cli creating lowercase taskTypeIDs
UPDATE task SET taskTypeID = CONCAT(UCASE(LEFT(taskTypeID, 1)), SUBSTRING(taskTypeID, 2));
