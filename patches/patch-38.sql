-- Add config option type to config table
ALTER TABLE config ADD type enum("text","array") NOT NULL default "text";

UPDATE config SET type = "text";

INSERT INTO config (name,value,type) VALUES ("timeSheetPrint",'a:3:{i:0;s:24:"timeSheetPrintMode=items";i:1;s:24:"timeSheetPrintMode=units";i:2;s:24:"timeSheetPrintMode=money";}',"array");
INSERT INTO config (name,value,type) VALUES ("timeSheetPrintOptions",'a:6:{s:24:"timeSheetPrintMode=items";s:7:"Default";s:36:"timeSheetPrintMode=items&printDesc=1";s:8:"Default+";s:24:"timeSheetPrintMode=units";s:5:"Units";s:36:"timeSheetPrintMode=units&printDesc=1";s:6:"Units+";s:24:"timeSheetPrintMode=money";s:7:"Invoice";s:36:"timeSheetPrintMode=money&printDesc=1";s:8:"Invoice+";}',"array");
