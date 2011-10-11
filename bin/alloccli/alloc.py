#!/usr/bin/env python

import os
import sys
import cmd
import exceptions
import simplejson
import getopt
import re
import urllib
import datetime
import ConfigParser
import csv
from netrc import netrc
from urlparse import urlparse
from prettytable import PrettyTable
from textwrap import wrap

class alloc(object):

  client_version = "1.8.2"
  url = ''
  username = ''
  quiet = ''
  sessID = ''
  alloc_dir = os.environ.get('ALLOC_HOME') or os.path.join(os.environ['HOME'], '.alloc/')
  config = {}
  user_transforms = {}
  field_names = {
    "task" : {
      "taskID"                   :"Task ID"
     ,"taskTypeID"               :"Type"
     ,"taskStatusLabel"          :"Status"
     ,"taskStatusColour"         :"Colour"
     ,"priority"                 :"Task Pri"
     ,"projectPriority"          :"Proj Pri"
     ,"priorityFactor"           :"Pri Factor"
     ,"priorityLabel"            :"Priority"
     ,"rate"                     :"Rate"
     ,"projectName"              :"Project"
     ,"taskName"                 :"Task"
     ,"taskDescription"          :"Description"
     ,"creator_name"             :"Creator"
     ,"manager_name"             :"Manager"
     ,"assignee_name"            :"Assigned"
     ,"projectShortName"         :"Proj Nick"
     ,"currency"                 :"Curr"
     ,"taskComments"             :"Comments"
     ,"timeActualLabel"          :"Act Label"
     ,"timeBest"                 :"Best"
     ,"timeWorst"                :"Worst"
     ,"timeExpected"             :"Est"
     ,"timeLimit"                :"Limit"
     ,"timeActual"               :"Act"
     ,"dateTargetCompletion"     :"Targ Compl"
     ,"dateTargetStart"          :"Targ Start"
     ,"dateActualCompletion"     :"Act Compl"
     ,"dateActualStart"          :"Act Start"
     ,"taskStatus"               :"Stat"
     ,"dateAssigned"             :"Date Assigned"
     ,"project_name"             :"Proj Name"
     ,"dateClosed"               :"Closed"
     ,"dateCreated"              :"Created"
    },

    "timeSheet" : {
      "timeSheetID"              :"Time ID"
     ,"dateFrom"                 :"From"
     ,"dateTo"                   :"To"
     ,"status"                   :"Status"
     ,"person"                   :"Owner"
     ,"duration"                 :"Duration"
     ,"totalHours"               :"Hrs"
     ,"amount"                   :"Amount"
     ,"projectName"              :"Project"
     ,"currencyTypeID"           :"Currency"
     ,"customerBilledDollars"    :"Bill"
     ,"dateRejected"             :"Rejected"
     ,"dateSubmittedToManager"   :"Submitted"
     ,"dateSubmittedToAdmin"     :"Submitted Admin"
     ,"invoiceDate"              :"Invoiced"
     ,"billingNote"              :"Notes"
     ,"payment_insurance"        :"Insurance"
     ,"recipient_tfID"           :"TFID"
     ,"commentPrivate"           :"Comm Priv"
    },

    "timeSheetItem" : {
      "timeSheetID"              :"Time ID"
     ,"timeSheetItemID"          :"Item ID"
     ,"dateTimeSheetItem"        :"Date"
     ,"taskID"                   :"Task ID"
     ,"comment"                  :"Comment"
     ,"timeSheetItemDuration"    :"Hours"
     ,"rate"                     :"Rate"
     ,"worth"                    :"Worth"
     ,"hoursBilled"              :"Total"
     ,"timeLimit"                :"Limit"
     ,"limitWarning"             :"Warning"
     ,"description"              :"Desc"
     ,"secondsBilled"            :"Seconds"
     ,"multiplier"               :"Mult"
     ,"approvedByManagerPersonID":"Managed"
     ,"approvedByAdminPersonID"  :"Admin"
    },

    "transaction" : {
      "transactionID"            :"Transaction ID"
     ,"fromTfName"               :"From TF"
     ,"tfName"                   :"Dest TF"
     ,"amount"                   :"Amount"
     ,"status"                   :"Status"
     ,"transactionDate"          :"Transaction Date"
    },
  
    "tf" : {
      "tfID"                     :"TF ID"
     ,"tfBalancePending"         :"Pending"
     ,"tfBalance"                :"Approved"
    },
        
    "project" : {
      "projectID"                :"Proj ID"
     ,"projectName"              :"Proj Name"
    }
  }


  row_timeSheet = "timeSheetID,dateFrom,dateTo,status,person,duration,totalHours,amount,projectName"
  row_timeSheetItem = "timeSheetID,timeSheetItemID,dateTimeSheetItem,taskID,comment,timeSheetItemDuration,rate,worth,hoursBilled,timeLimit,limitWarning"

  def __init__(self,url=""):

    # Grab a storage dir to work in
    if self.alloc_dir[-1:] != '/':
      self.alloc_dir += "/"

    # Create ~/.alloc if necessary
    if not os.path.exists(self.alloc_dir):
      self.dbg("Creating: "+self.alloc_dir)
      os.mkdir(self.alloc_dir)

    # Create ~/.alloc/config
    if not os.path.exists(self.alloc_dir+"config"):
      self.create_config(self.alloc_dir+"config")

    # Create ~/.alloc/transforms
    if not os.path.exists(self.alloc_dir+"transforms"):
      self.create_transforms(self.alloc_dir+"transforms")

    # Load ~/.alloc/config into self.config{}
    self.load_config(self.alloc_dir+"config")

    # Load any user-customizations to table print output
    self.load_transforms(self.alloc_dir+"transforms")

    if not url:
      if "url" in self.config and self.config["url"]:
        url = self.config["url"]
      if not url:
        self.die("No alloc url specified!")

    # Grab session ~/.alloc/session
    if os.path.exists(self.alloc_dir+"session"):
      self.sessID = self.load_session(self.alloc_dir+"session")

    self.url = url
    self.username = ''
    self.quiet = ''
    self.csv = False

  def create_config(self,f):
    self.dbg("Creating and populating: "+f)
    str = "[main]\nurl: http://alloc/services/json.php\n"
    # Write it out to a file
    fd = open(f,'w')
    fd.write(str)
    fd.close()

  def load_config(self,f):
    config = ConfigParser.ConfigParser()
    config.read([f])
    sections = config.sections()
    # Loop through each section
    for section in sections:
      options = config.options(section)
      for option in options:
        self.config[option] = config.get(section,option)

  def create_transforms(self,f):
    self.dbg("Creating example transforms file: "+f)
    str = "# Add any field customisations here. eg:\n#\n# global user_transforms\n# user_transforms = { 'Priority' : lambda x,row: x[3:] }\n\n"
    # Write it out to a file
    fd = open(f,'w')
    fd.write(str)
    fd.close()

  def load_transforms(self,f):
    try:
      # yee-haw!
      execfile(f)
      self.user_transforms = user_transforms
    except:
      pass

  def create_session(self,sessID):
    old_sessID = self.load_session(self.alloc_dir+"session")
    if not old_sessID or old_sessID != sessID:
      self.dbg("Writing to: "+self.alloc_dir+"session: "+sessID)
      # Write it out to a file
      fd = open(self.alloc_dir+"session",'w')
      fd.write(sessID)
      fd.close()

  def load_session(self,f):
    try:
      fd = open(f)
      sessID = fd.read().strip()
      fd.close()
    except:
      sessID = ""
    return sessID

  def search_for_project(self, projectName, personID=None):
    # Search for a project like *projectName*
    if projectName:
      filter = {}
      if personID: filter["personID"] = personID
      filter["projectStatus"] = "Current"
      filter["projectName"] = projectName
      projects = self.get_list("project",filter)
      if len(projects) == 0:
        self.die("No project found matching: %s" % projectName)
      elif len(projects) > 1:
        self.print_table("project", projects, ["projectID","ID","projectName","Project"])
        self.die("Found more than one project matching: %s" % projectName)
      elif len(projects) == 1:
        return projects.keys()[0]
        
  def search_for_task(self, ops):
    # Search for a task like *taskName*
    if "taskName" in ops:
      tasks = self.get_list("task",ops)
      
      if not tasks:
        self.die("No task found matching: %s" % ops["taskName"])
      elif tasks and len(tasks) >1:
        self.print_table("task", tasks, ["taskID","ID","taskName","Task","projectName","Project"])
        self.die("Found more than one task matching: %s" % ops["taskName"])
      elif len(tasks) == 1:      
        return tasks.keys()[0]   

  def search_for_client(self, ops):
    # Search for a client like *clientName*
    if "clientName" in ops:
      clients = self.get_list("client",ops)

      if not clients:
        self.die("No client found matching: %s" % ops["clientName"])
      elif clients and len(clients) >1:
        self.print_table("client", clients, ["clientID","ID","clientName","Client"])
        self.die("Found more than one client matching: %s" % ops["clientName"])
      elif len(clients) == 1:
        return clients.keys()[0]

  def print_task(self, id):
    # view of a task
    rtn = self.get_list("task",{"taskID": id, "taskView": "prioritised","showTimes":True})

    for k,r in rtn.items():
      pass

    underline_length = len(r["taskTypeID"]+": "+r["taskID"]+" "+r["taskName"])

    str = "\n"+r["taskTypeID"]+": "+r["taskID"]+" "+r["taskName"]
    str+= "\n".ljust(underline_length+1,"=")
    str+= "\n"
    if r["priorityLabel"]: str+= "\n"+"Priority: "+r["priorityLabel"].ljust(26)+r["taskStatusLabel"]
    str+= "\n"
    if r["projectName"]:  str+= "\nProject: "+r["projectName"]+" ["+r["projectPriorityLabel"]+"]"
    if r["parentTaskID"]: str+= "\nParent Task: "+r["parentTaskID"]
    if r["projectName"] or r["parentTaskID"]: str+= "\n"
    if r["creator_name"]:  str+= "\nCreator:  "+r["creator_name"].ljust(25)+" "+r["dateCreated"]
    if r["assignee_name"]: str+= "\nAssigned: "+r["assignee_name"].ljust(25)+" "+r["dateAssigned"]
    if r["manager_name"]:  str+= "\nManager:  "+r["manager_name"].ljust(25)
    if r["dateClosed"]:
      str+= "\nCloser:   "+r["closer_name"].ljust(25)+" "+r["dateClosed"]
    str+= "\n"
    str+= "\nB/E/W Estimates:  "+(r["timeBestLabel"] or "--")+" / "+(r["timeExpectedLabel"] or "--")+" / "+(r["timeWorstLabel"] or "--")+"  "+(r["estimator_name"] or "")
    str+= "\nActual/Limit Hrs: %s / %s " % (r["timeActualLabel"] or "--", r["timeLimitLabel"] or "--")
    str+= "\n"

    if r["dateTargetStart"] or r["dateTargetCompletion"]:
      str+= "\nTarget Start: %-18s Target Completion: %-18s " % (r["dateTargetStart"], r["dateTargetCompletion"])
    if r["dateActualStart"] or r["dateActualCompletion"]:
      str+= "\nActual Start: %-18s Actual Completion: %-18s " % (r["dateActualStart"], r["dateActualCompletion"])

    if r["taskDescription"]:
      str+= "\n"
      str+= "\nDescription"
      str+= "\n-----------"
      str+= "\n"
      #str+= "\n".join(wrap(r["taskDescription"],75))+"\n" # this seems to not work very well.
      str+= "\n"+r["taskDescription"]

    str+= "\n"
    return str

  def get_subcommand_help(self, command_list, ops, str):
    # Get help text for a subcommand.
    help_str = ""
    for x in ops:
      # These are used to build up the help text for --help
      c = " "
      s = "    "
      l = "                   "
      if x[0] and x[1]: c = ","
      if x[0]: s = "  -"+x[0].replace(":","")
      if x[1].strip(): l = c+" --"+x[1]
      # eg:  -q, --quiet             Run with less output.
      help_str += s+l+"   "+x[2]+"\n"
    return str % (os.path.basename(" ".join(command_list[0:2])), help_str.rstrip())
    
  def get_args(self, command_list, ops, str):
    # This function allows us to handle the cli arguments elegantly
    short_ops = ""
    long_ops = []
    options = []
    no_arg_ops = {}
    all_ops = {}
    rtn = {}
    remainder = ""
    
    help_string = self.get_subcommand_help(command_list, ops, str)

    for x in ops:
      # These args go straight to getops
      short_ops += x[0]
      long_ops.append(re.sub("=.*$","=",x[1]).strip())

      # track which ops don't require an argument eg -q
      if x[0] and x[0][-1] != ':':
        no_arg_ops["-"+x[0]] = True

      # or eg --help
      if not x[0] and '=' not in x[1]:
        no_arg_ops["--"+x[1].strip()] = True

      # And this is used below to build up a dictionary to return
      all_ops[re.sub("=.*$","",x[1]).strip()] = ["-"+x[0].replace(":",""), "--"+re.sub("=.*$","",x[1]).strip()]

    try:
      options, remainder = getopt.getopt(command_list[2:], short_ops, long_ops)
    except:
      print help_string
      sys.exit(0)

    for k,v in all_ops.items():
      rtn[k] = ''
      for opt, val in options:
        if opt in v:
          # eg -q
          if opt in no_arg_ops and val == '':
            rtn[k] = True

          # eg -x argument
          else:
            rtn[k] = val

    if rtn['help']:
      print help_string
      sys.exit(0)

    if 'csv' in rtn and rtn['csv']:
      self.csv = True
    
    return rtn, " ".join(remainder)

  def get_my_personID(self, nick=None):
    # Get current user's personID
    ops = {}
    ops["username"] = self.username
    if nick:
      ops["username"] = nick
    
    rtn = self.get_list("person",ops)
    for i in rtn:
      return i

  def get_only_these_fields(self,entity,rows,only_these_fields):
    rtn = []
    inverted_field_names = dict([[v,k] for k,v in self.field_names[entity].items()])

    # Allow the display of custom fields
    if type(only_these_fields) == type("string"):
      # Print all fields
      if only_these_fields.lower() == "all":
        for k,v in rows.items():
          for name,value in v.items():
            rtn.append(name)
            if name in self.field_names[entity]:
              rtn.append(self.field_names[entity][name])
            else:
              rtn.append(name)
          break
      # Print a selection of fields
      else:
        f = only_these_fields.split(",")
        for name in f:
          if name in inverted_field_names:
            name = inverted_field_names[name]
          rtn.append(name)
          if name in self.field_names[entity]:
            rtn.append(self.field_names[entity][name])
          else:
            rtn.append(name)
      return rtn;
    return only_these_fields

  def get_sorted_rows(self,entity,rows,sortby):
    rows = rows.items()
    if not sortby:
      return rows
    inverted_field_names = dict([[v,k] for k,v in self.field_names[entity].items()])

    sortby = sortby.split(",")
    sortby.reverse()    

    # load up fields
    for k,fields in rows:
      pass

    # Check that any attempted sortby columns are actually in the table
    for k in sortby:
      # Strip leading underscore (used in reverse sorting eg: _Rate)
      if re.sub("^_","",k) not in fields and re.sub("^_","",k) not in inverted_field_names:
        self.err("Sort column not found: "+k)

    def sort_func(row):
      try: val = row[1][inverted_field_names[f]]
      except:
        try: val = row[1][self.field_names[entity][f]]
        except:
          try: val = row[1][f]
          except:
            try: val = row[1][fields[fields.index(f)-1]]
            except:
              return ''

      try: return int(val)
      except:
        try: return float(val)
        except:
          try: return val.lower()
          except:
            return val

    for f in sortby:
      if f:
        reverse = False
        if f[0] == "_":
          reverse = True
          f = f[1:]
        rows = sorted(rows, key=sort_func, reverse=reverse)
    return rows

  def print_table(self, entity, rows, only_these_fields, sort=False, transforms={}):
    # For printing out results in an ascii table or CSV format
    if self.quiet: return
    if not rows: return 

    table = PrettyTable()

    only_these_fields = self.get_only_these_fields(entity,rows,only_these_fields)
    field_names = only_these_fields[1::2]
    table.set_field_names(field_names)

    # Re-order the table, this changes the dict to a list i.e. dict.items().
    rows = self.get_sorted_rows(entity,rows,sort)

    if self.csv:
      csv_table = csv.writer(sys.stdout)

    for label in field_names:
      if not self.csv and '$' in label:
        table.set_field_align(label, "r")
      else:
        table.set_field_align(label, "l")

    if rows:
      for k,row in rows:
        r = []
        for v in only_these_fields[::2]: 
          value = ''
          success = False
              
          if v in row:
            value = row[v]
            success = True
  
          if v in transforms:
            value = transforms[v](value)
            success = True

          if v in self.user_transforms:
            value = self.user_transforms[v](value,row)
            success = True

          if v in self.field_names[entity]:
            other_v = self.field_names[entity][v]
            if other_v in self.user_transforms:
              value = self.user_transforms[other_v](value,row)
              success = True

          if not success:
            self.err('Bad field name: '+v)

          if not value:
            value = ''

          r.append(value)

        if self.csv:
          csv_table.writerow([unicode(s).encode('utf-8') for s in r])
        else:
          table.add_row(r)

    if not self.csv:
      lines = table.get_string(header=True)
      print unicode(lines).encode('utf-8')

  def is_num(self, obj):
    # There's got to be a better way to tell if something is a number 
    # isinstance of float or int didn't do the job (for some reason ...)
    try:
      if obj is not None and float(obj) >= 0:
        return True;
    except:
      pass

    return False

  def to_num(self,obj):
    rtn = obj
    try:
      rtn = float(obj)
    except:
      try:
        rtn = int(obj)
      except:
        rtn = 0
    return rtn

  def get_credentials(self):
    # Obtain the user's alloc login credentials
    username = os.environ.get('ALLOC_USER')
    password = os.environ.get('ALLOC_PASS')
      
    if username is None or password is None:
      try:
        (username, _, password) = netrc().hosts[urlparse(self.url).hostname]
      except:
        pass
    
    if username is None or password is None:
      self.err("The settings ALLOC_USER and ALLOC_PASS are required.")
      self.err("Set them either in the environment or in your ~/.netrc eg:")
      self.die("machine alloc login $USER password $PASS")

    return username, password
    
  def add_time(self, stuff):
    # Add time to a time sheet using a task as reference
    if self.dryrun: return ''
    args = {}
    args["method"] = "add_timeSheetItem"
    args["options"] = stuff;
    return self.make_request(args)

  def get_list(self, entity, options):
    options["skipObject"] = '1'
    options["return"] = "array"
    args = {}
    args["entity"] = entity
    args["options"] = options
    args["method"] = "get_list"
    return self.make_request(args)

  def get_help(self, topic): 
    args = {}
    args["topic"] = topic
    args["method"] = "get_help"
    return self.make_request(args)

  def authenticate(self):
    self.dbg("calling authenticate()")
    username, password = self.get_credentials()
    # The user-agent must be identical between authenticated
    # requests, alloc uses the u-a for secondary auth
    allocUserAgent.version = 'alloc-cli %s' % username
    urllib._urlopener = allocUserAgent()
    args =  { "authenticate": True, "username": username, "password" : password }
    if not self.sessID:
      self.dbg("ATTEMPTING AUTHENTICATION.")
      rtn = self.make_request(args)
    else:
      rtn = {"sessID":self.sessID}

    if "sessID" in rtn and rtn["sessID"]:
      self.sessID = rtn["sessID"]
      self.username = username
      self.create_session(self.sessID)
      return self.sessID
    else:
      self.die("Error authenticating: %s" % rtn)

  def make_request(self, args):
    args["client_version"] = self.client_version
    args["sessID"] = self.sessID
    rtn = urllib.urlopen(self.url, urllib.urlencode(args)).read()
    try:
      rtn = simplejson.loads(rtn)
    except:
      self.err("Error(1): %s" % rtn)
      if args and 'password' in args: args['password'] = '********'
      self.die("Args: %s" % args)

    # Handle session expiration by re-authenticating 
    if rtn and 'reauthenticate' in rtn and 'authenticate' not in args:
      self.dbg("Session dead, reauthenticating.")
      self.sessID = ''
      self.authenticate()
      args['sessID'] = self.sessID
      self.dbg("executing: %s" % args)
      rtn2 = urllib.urlopen(self.url, urllib.urlencode(args)).read()
      try:
        return simplejson.loads(rtn2)
      except:
        self.err("Error(2): %s" % rtn2)
        if args and 'password' in args: args['password'] = '********'
        self.die("Args: %s" % args)
    return rtn

  def get_people(people):
    args = {}
    args["people"] = people
    args["method"] = "get_people"
    return self.make_request(args)

  def get_alloc_html(self,url):
    return urllib.urlopen(url).read()

  def today(self):
    return datetime.date.today()

  def msg(self,str):
    if not self.quiet: print "---",str

  def yay(self,str):
    if not self.quiet: print ":-]",str

  def err(self,str):
    sys.stderr.write("!!! "+str+"\n")

  def die(self,str):
    self.err(str)
    sys.exit(1)

  def dbg(self,str):
    #print "DBG",str
    pass

  def parse_email(self, email):
    addr = ''
    name = ''
    bits = email.split(' ')

    if len(bits) == 1:
      if '@' in bits[0]:
        addr = bits[0].replace('<','').replace('>','')
      else:
        name = bits[0]

    elif len(bits) > 1:

      if '@' in bits[-1:][0]:
        addr = bits[-1:][0].replace('<','').replace('>','')
        name = ' '.join(bits[:-1])
      else:
        name = ' '.join(bits)

    return addr, name

  def person_to_personID(self,name):

    if type(name) == type('string'):
      ops = {}
      if ' ' in name:
        ops['firstName'], ops['surname'] = name.split(" ")
      else:
        ops["username"] = name

      rtn = self.get_list("person",ops)
      if rtn:
        for i in rtn:
          return i

    # If they don't want all the records, then return an impossible personID
    if name != '%' and name != '*' and name.lower() != 'all':
      return '1000000000000000000' # returning just zero doesn't work

  # split a comparator and a date eg: '>=2011-10-10' becomes ['>=','2011-10-10']
  def parse_date_comparator(self,date):
    try:
      comparator, d = re.findall(r'[\d|-]+|\D+', date)
    except:
      comparator = '='
      d = date
  
    return d.strip(),comparator.strip() 

  def get_alloc_modules(self):
    modules = []
    for f in os.listdir("/".join(sys.argv[0].split("/")[:-1])+"/alloccli/"):
      s = f[-4:].lower()
      if s!=".pyc" and s!=".swp" and s!=".swo" and f!="alloc" and f!="alloc.py" and f!="__init__.py":
        m = f.replace(".py","")
        modules.append(m) 
    return modules

  def get_cli_help(self, halt_on_error=True):
    print "Usage: alloc command [OPTIONS]"
    print "Select one of the following commands:\n"
    for m in self.get_alloc_modules():
      alloccli = __import__("alloccli."+m)
      subcommand = getattr(getattr(alloccli,m), m)

      # Print out the module's one_line_help variable
      tabs = "\t "
      if len(m) <= 5: tabs = "\t\t "
      print "  "+m+tabs+getattr(subcommand,"one_line_help")

    print "\nEg: alloc command --help"

    if halt_on_error:
      if len(sys.argv) >1:
        self.die("Invalid command: "+sys.argv[1])
      else:
        self.die("Select a command to run.")
    else:
      if len(sys.argv) >1:
        self.err("Invalid command: "+sys.argv[1])
      else:
        self.err("Select a command to run.")

  def get_cmd_help(self):
    print "Select one of the following commands:\n"
    for m in self.get_alloc_modules():
      alloccli = __import__("alloccli."+m)
      subcommand = getattr(getattr(alloccli,m), m)

      # Print out the module's one_line_help variable
      tabs = "\t "
      if len(m) <= 5: tabs = "\t\t "
      print "  "+m+tabs+getattr(subcommand,"one_line_help")

    print "\nEg: tasks -t 1234"



# Specify the user-agent 
class allocUserAgent(urllib.FancyURLopener):
  pass


# Interactive handler for alloccli
class allocCmd(cmd.Cmd):

  prompt = "alloc> "
  alloc = None
  url = ""
  modules = {}
  worklog = None

  def __init__(self,url):
    self.url = url
    self.alloc = alloc(self.url)
    self.alloc.authenticate()
    alloc.sessID = self.alloc.sessID
    #self.worklog = worklog()
  
    # Import all the alloc modules so they are available as eg self.tasks
    for m in self.alloc.get_alloc_modules():
      alloccli = __import__("alloccli."+m)
      subcommand = getattr(getattr(alloccli,m), m)
      setattr(self,m,subcommand(self.url))
    return cmd.Cmd.__init__(self);

  def emptyline(self):
    """Go to new empty prompt if an empty line is entered."""
    pass

  def default(self,line):
    """Print an error if an unrecognized command is entered."""
    self.alloc.err("Unrecognized command: '"+line+"', hit TAB and try 'help COMMAND'.")

  def do_EOF(self,line):
    """Exit if ctrl-d is pressed."""
    print "" # newline
    sys.exit(0)

  def do_quit(self,line):
    """Exit if quit is entered."""
    sys.exit(0)

  def do_exit(self,line):
    """Exit if exit is entered."""
    sys.exit(0)

  def do_help(self,line):
    """Provide help information if 'help' or 'help COMMAND' are entered."""
    bits = line.split()
    if len(bits) == 1:
      if (bits[0].lower() == "command"):
        print "Try eg: help timesheets"
      else:
        try:
          cmdbits = ["alloc", bits[0], "--help"]
          subcommand = getattr(self,bits[0])
          print subcommand.get_subcommand_help(cmdbits, subcommand.ops, subcommand.help_text)
        except:
          self.alloc.err("Unrecognized command: '"+bits[0]+"', hit TAB and try one of those.")
    else:
      self.alloc.get_cmd_help()


  
# Create some class methods called do_MODULE eg do_tasks, and
# dynamically add those methods to the allocCmd class. This will expose
# all the alloc modules to the allocCmd interface without having to
# duplicate the list of modules.
def make_func(m):
  def func(obj,line):
    bits = line.split()
    bits.insert(0,m)
    bits.insert(0,"alloc")
    subcommand = getattr(obj,m)
    # Putting this in an exception block lets us continue when the subcommands call die().
    try: 
      subcommand.run(bits)
    except BaseException,Err:
      pass
  return func

# Add the methods to allocCmd
for m in alloc().get_alloc_modules():
  setattr(allocCmd, "do_"+m, make_func(m))




