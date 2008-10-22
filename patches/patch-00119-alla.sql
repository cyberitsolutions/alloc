
-- Fix up permission table;
DROP TABLE IF EXISTS permission;
CREATE TABLE permission (
  permissionID int(11) NOT NULL auto_increment,
  tableName varchar(255) default NULL,
  entityID int(11) default NULL,
  personID int(11) default NULL,
  roleName varchar(255) default NULL,
  allow enum('Y','N') default NULL,
  sortKey int(11) default '100',
  actions int(11) default NULL,
  comment text,
  PRIMARY KEY  (permissionID),
  KEY tableName (tableName)
) TYPE=MyISAM PACK_KEYS=0;


-- Refresh the permission information
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `actions`, `comment`)
VALUES

 ('absence'                  ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('absence'                  ,0  ,0 ,'manage'   ,'Y' ,NULL ,31    ,NULL)
,('absence'                  ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('announcement'             ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('announcement'             ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('client'                   ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('clientContact'            ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('comment'                  ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('commentTemplate'          ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('commentTemplate'          ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('config'                   ,0  ,0 ,''         ,'Y' ,NULL ,17    ,NULL)
,('config'                   ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('expenseForm'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('expenseForm'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('history'                  ,0  ,0 ,''         ,'Y' ,NULL ,8     ,NULL)

,('interestedParty'          ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('invoice'                  ,-1 ,0 ,''         ,'Y' ,NULL ,3     ,'Update invoiceItem, can change invoice.')
,('invoice'                  ,-1 ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoice'                  ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('invoiceItem'              ,-1 ,0 ,''         ,'Y' ,NULL ,11    ,'Update time sheet, can change invoice item.')
,('invoiceItem'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('invoiceItem'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,271   ,NULL)

,('item'                     ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('item'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,11    ,NULL)
,('item'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('loan'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,17    ,NULL)
,('loan'                     ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('loan'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('person'                   ,-1 ,0 ,''         ,'Y' ,NULL ,259   ,NULL)
,('person'                   ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)
,('person'                   ,0  ,0 ,'admin'    ,'Y' ,NULL ,7951  ,NULL)

,('project'                  ,0  ,0 ,''         ,'Y' ,100  ,513   ,'Allow all to read projects for searches.')
,('project'                  ,-1 ,0 ,'employee' ,'Y' ,100  ,769   ,NULL)
,('project'                  ,-1 ,0 ,'employee' ,'Y' ,99   ,271   ,NULL)
,('project'                  ,-1 ,0 ,'manage'   ,'Y' ,100  ,783   ,NULL)
,('project'                  ,0  ,0 ,'admin'    ,'Y' ,100  ,783   ,NULL)

,('projectPerson'            ,-1 ,0 ,''         ,'Y' ,NULL ,17    ,NULL)
,('projectPerson'            ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,'Allow employee PMs to add other people.')
,('projectPerson'            ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectPerson'            ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('projectCommissionPerson'  ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,-1 ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('projectCommissionPerson'  ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('reminder'                 ,0  ,0 ,''         ,'Y' ,NULL ,15    ,'Will have to change this later?')

,('savedView'                ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('sentEmailLog'             ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('skillList'                ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('skillList'                ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('skillProficiencys'        ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('skillProficiencys'        ,-1 ,0 ,'employee' ,'Y' ,NULL ,14    ,NULL)
,('skillProficiencys'        ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)

,('task'                     ,-1 ,0 ,'employee' ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,'Allow read all task records for searches.')
,('task'                     ,0  ,0 ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('task'                     ,0  ,0 ,'admin'    ,'Y' ,NULL ,257   ,NULL)

,('taskType'                 ,0  ,0 ,''         ,'Y' ,NULL ,17    ,NULL)

,('tf'                       ,0  ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('tf'                       ,0  ,0 ,'admin'    ,'Y' ,NULL ,31    ,NULL)

,('tfPerson'                 ,-1 ,0 ,'employee' ,'Y' ,NULL ,1     ,'Allow employee to read own tfPerson.')
,('tfPerson'                 ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('timeUnit'                 ,0  ,0 ,''         ,'Y' ,NULL ,1     ,NULL)

,('timeSheet'                ,-1 ,0 ,'employee' ,'Y' ,NULL ,31    ,NULL)
,('timeSheet'                ,0  ,0 ,'manage'   ,'Y' ,NULL ,287   ,NULL)
,('timeSheet'                ,0  ,0 ,'admin'    ,'Y' ,NULL ,783   ,NULL)

,('timeSheetItem'            ,-1 ,0 ,''         ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,0 ,'manage'   ,'Y' ,NULL ,15    ,NULL)
,('timeSheetItem'            ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

,('token'                    ,0  ,0 ,''         ,'Y' ,NULL ,15    ,NULL)

,('transaction'              ,-1 ,0 ,'employee' ,'Y' ,NULL ,15    ,NULL)
,('transaction'              ,0  ,0 ,'admin'    ,'Y' ,NULL ,65295 ,NULL)

,('transactionRepeat'        ,-1 ,0 ,'employee' ,'Y' ,NULL ,1     ,NULL)
,('transactionRepeat'        ,0  ,0 ,'admin'    ,'Y' ,NULL ,15    ,NULL)

;


