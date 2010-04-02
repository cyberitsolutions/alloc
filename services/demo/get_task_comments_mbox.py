#!/usr/bin/python

import SOAPpy
import time

def print_email(row):
    for items in row:
      email = get_email_bits(items)
      str = 'From '+email['from'].replace(" ","-")+' '+email['start_date']
      if ('downloaded_email' in email):
        str += '\n'+email['downloaded_email']+'\n'
      else:
        str += '\nFrom: '+email['from']
        str += '\nTo: '+email['recipients']
        str += '\nDate: '+email['start_date']
        str += '\nSubject: '+email['subject']
        str += '\n\n'+email['comment']+'\n'
      
      print str.encode('utf-8')

def get_email_bits(items):
  email = { 'recipients':'' }
  for item in items:

    if (item.key == 'commentCreatedUserEmail'):
      email['from'] = item.value

    elif (item.key == 'commentEmailRecipients'):
      email['recipients'] = item.value

    elif (item.key == 'commentEmailSubject'):
      email['subject'] = item.value

    elif (item.key == 'comment' and 'comment' not in email):
      email['comment'] = item.value

    elif (item.key == 'date'):   
      d = time.localtime()
      email['start_date'] = time.asctime(d)
      # time sheet comments have only the date component yyyy-mm-dd
      if (len(item.value) > 10): 
        email['start_date'] = datetime_to_asctime(item.value);

    elif (item.key == 'commentEmailUID'):   
      email['commentEmailUID'] = item.value
      email['downloaded_email'] = alloc.get_email(key, item.value) 

    elif (item.key == 'children'):
      for r in item.value:
        print_email(r)

  return email 

def datetime_to_asctime(datestr):
  d,t = datestr.split(' ')
  ho,mi,se = t.split(':')
  ye,mo,da = d.split('-')
  tup = [int(ye), int(mo), int(da), int(ho), int(mi), int(se), 1, 1, -1]
  return time.asctime(tup)




#SOAPpy.Config.debug = 1 
alloc = SOAPpy.WSDL.Proxy('http://leaf/alloc/soap/alloc.wsdl')
key = alloc.authenticate('admin', 'admin')
rows = alloc.get_list(key, "comment", { 'entity':"task", 'entityID':"13275" })

for row in rows:
  print_email(row)



