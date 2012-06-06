
DELETE FROM permission;
INSERT INTO permission (tableName, entityID, roleName, sortKey, actions, comment)
VALUES

 ('absence'                  ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('absence'                  ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('absence'                  ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('announcement'             ,0  ,''         ,NULL ,1          ,NULL)
,('announcement'             ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('auditItem'                ,0  ,''         ,NULL ,8+1        ,'Allow all to create and read audit items.')

,('client'                   ,0  ,''         ,NULL ,1+2+4+8    ,NULL)
,('clientContact'            ,0  ,''         ,NULL ,1+2+4+8    ,NULL)

,('comment'                  ,0  ,'employee' ,NULL ,1+2+4+8    ,NULL)

,('commentTemplate'          ,0  ,''         ,NULL ,1          ,NULL)
,('commentTemplate'          ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)

,('config'                   ,0  ,'god'      ,NULL ,1+2+4+8    ,NULL)

,('expenseForm'              ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('expenseForm'              ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('history'                  ,0  ,''         ,NULL ,8          ,NULL)

,('indexQueue'               ,0  ,''         ,NULL ,1+2+4+8    ,'Allow all to indexQueue.')

,('interestedParty'          ,0  ,''         ,NULL ,11         ,'Alloc all to read, update and create.')
,('interestedParty'          ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('interestedParty'          ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)
,('interestedParty'          ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)

,('invoice'                  ,-1 ,''         ,NULL ,3          ,'Read+update invoiceItem, can change invoice.')
,('invoice'                  ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('invoice'                  ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('invoiceItem'              ,-1 ,''         ,NULL ,11         ,'Update time sheet, can change invoice item.')
,('invoiceItem'              ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('invoiceItem'              ,0  ,'admin'    ,NULL ,1+2+4+8+256,NULL)

,('item'                     ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('item'                     ,0  ,'employee' ,NULL ,11         ,'Read, update, create.')
,('item'                     ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('loan'                     ,0  ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('loan'                     ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('loan'                     ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('permission'               ,0  ,'god'      ,NULL ,1+2+4+8    ,NULL)

,('person'                   ,-1 ,''         ,NULL ,2+256      ,NULL)
,('person'                   ,0  ,''         ,NULL ,1          ,NULL)
,('person'                   ,0  ,'admin'    ,NULL ,1+2+4+8+256+512+1024+2048+4096  ,NULL)
,('person'                   ,0  ,'god'      ,NULL ,1+2+4+8+256+512+1024+2048+4096  ,NULL)

,('product'                  ,0  ,''         ,0    ,1          ,NULL)
,('product'                  ,0  ,'manage'   ,100  ,1+2+4+8    ,NULL)
,('product'                  ,0  ,'admin'    ,100  ,1+2+4+8    ,NULL)

,('productCost'              ,0  ,''         ,NULL ,1          ,NULL)
,('productCost'              ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('productCost'              ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('productSale'              ,0  ,''         ,NULL ,1          ,NULL)
,('productSale'              ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('productSale'              ,0  ,'admin'    ,NULL ,1+2+4+8+256,NULL)

,('productSaleItem'          ,0  ,''         ,NULL ,1          ,NULL)
,('productSaleItem'          ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('productSaleItem'          ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('project'                  ,0  ,''         ,100  ,1+512      ,'Allow all to read projects for searches.')
,('project'                  ,-1 ,'employee' ,100  ,1+256+512  ,NULL)
,('project'                  ,-1 ,'employee' ,99   ,1+2+4+8+256,NULL)
,('project'                  ,-1 ,'manage'   ,100  ,1+2+4+8+256+512,NULL)
,('project'                  ,0  ,'admin'    ,100  ,1+2+4+8+256+512,NULL)

,('projectPerson'            ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('projectPerson'            ,-1 ,'employee' ,NULL ,1+2+4+8    ,'Allow employee PMs to add other people.')
,('projectPerson'            ,-1 ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('projectPerson'            ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('projectCommissionPerson'  ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('projectCommissionPerson'  ,-1 ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('projectCommissionPerson'  ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('reminder'                 ,0  ,''         ,NULL ,1+2+4+8    ,'Will have to change this later?')

,('sentEmailLog'             ,0  ,''         ,NULL ,1+2+4+8    ,NULL)

,('skill'                    ,0  ,'employee' ,NULL ,1          ,NULL)
,('skill'                    ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('proficiency'              ,0  ,'employee' ,NULL ,1          ,NULL)
,('proficiency'              ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('proficiency'              ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)

,('task'                     ,-1 ,'employee' ,NULL ,1+2+4+8+256,NULL)
,('task'                     ,0  ,'employee' ,NULL ,1          ,'Allow read all task records for searches.')
,('task'                     ,0  ,'manage'   ,NULL ,1+2+4+8+256,NULL)
,('task'                     ,0  ,'admin'    ,NULL ,1+256      ,NULL)

,('tf'                       ,0  ,'employee' ,NULL ,1          ,NULL)
,('tf'                       ,0  ,'manage'   ,NULL ,1          ,NULL)
,('tf'                       ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('tfPerson'                 ,-1 ,'employee' ,NULL ,1          ,'Allow employee to read own tfPerson.')
,('tfPerson'                 ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('timeUnit'                 ,0  ,''         ,NULL ,1          ,NULL)

,('timeSheet'                ,-1 ,'employee' ,NULL ,1+2+4+8    ,NULL)
,('timeSheet'                ,0  ,'manage'   ,NULL ,1+2+4+8+256,NULL)
,('timeSheet'                ,0  ,'admin'    ,NULL ,1+2+4+8+256+512 ,NULL)

,('timeSheetItem'            ,-1 ,''         ,NULL ,1+2+4+8    ,NULL)
,('timeSheetItem'            ,0  ,'manage'   ,NULL ,1+2+4+8    ,NULL)
,('timeSheetItem'            ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

,('token'                    ,0  ,''         ,NULL ,1+2+4+8    ,NULL)
,('tokenAction'              ,0  ,''         ,NULL ,1          ,NULL)

,('transaction'              ,-1 ,''         ,NULL ,1+2+4+8    ,'Allow everyone to modify PENDING transactions that they own.')
,('transaction'              ,0  ,'admin'    ,NULL ,1+2+4+8    ,'Allow admin to do everything with transactions.')

,('transactionRepeat'        ,-1 ,'employee' ,NULL ,1          ,NULL)
,('transactionRepeat'        ,0  ,'admin'    ,NULL ,1+2+4+8    ,NULL)

;


