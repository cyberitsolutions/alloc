#!/usr/bin/python

import SOAPpy

#url = 'http://peach/~alla/dev.allocpsa.net/soap/alloc.wsdl'
url = 'http://leaf/alloc6/soap/alloc.wsdl'

# just use the path to the wsdl of your choice
alloc = SOAPpy.WSDL.Proxy(url)

# This prints all the available methods:
for method in alloc.methods.keys() :
  print '\nMethod: '+method
  ci = alloc.methods[method]
  for param in ci.inparams :
    print 'In: '+param.name.ljust(20) , param.type
  for param in ci.outparams :
    print 'Out: '+param.name.ljust(20) , param.type

#SOAPpy.Config.debug = 1 # this will enable python/soap uber-debugging
username = 'alloc'
password = 'alloc'
key = alloc.authenticate(username, password)
print 'Session key: '+key

#ops = {"personID":"mpilgrim", "database":"master"}
#print alloc.get_list(key, "task",ops)

print alloc.get_help("get_list")

a = { 'return' : "array", 'showProjectName' : "1", 'applyFilter' : "1" }

#r = alloc.get_list(key, "project", a)


### Example 1 - adding time to a time sheet

#taskID = 1
#duration = 5.23
#comments = "hey commedsahdjkshajdkants!"
#
#print alloc.add_timeSheetItem_by_task(key, taskID, duration, comments)
#
#
#### Example 2 - getting transactions from your tf
#tfName = "alla"
#startDate = "2006-02-23"
#endDate = "2010-02-30"
#
#transactions = alloc.get_tf_transactions(key, tfName, startDate, endDate)
#






