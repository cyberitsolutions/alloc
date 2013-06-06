-- fix up rejected time sheets
INSERT INTO timeSheetStatus (timeSheetStatusID,timeSheetStatusSeq, timeSheetStatusActive) VALUES ("rejected",6,1);
UPDATE timeSheet SET status = 'rejected' WHERE status = 'edit' AND dateRejected IS NOT NULL AND dateRejected;
