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
from netrc import netrc
from urlparse import urlparse
from prettytable import PrettyTable

class alloc(object):

  client_version = "1.8.1"
  url = ''
  username = ''
  quiet = ''
  sessID = ''
  alloc_dir = os.path.join(os.environ['HOME'], '.alloc/')
  config = {}
  user_transforms = {}
  field_names = {
                # task fields
                "taskID":"Task ID"
                ,"taskTypeID":"Type"
                ,"taskStatusLabel":"Status"
                ,"priority":"Task Pri"
                ,"projectPriority":"Proj Pri"
                ,"priorityFactor":"Pri Factor"
                ,"priorityLabel": "Priority"
                ,"timeExpected":"Est"
                ,"timeLimit":"Limit"
                ,"timeActual":"Act"
                ,"rate":"Rate"
                ,"projectName":"Project"
                ,"taskName":"Task"
                ,"taskDescription": "Description"
                ,"creator_name": "Creator"
                ,"manager_name": "Manager"
                ,"assignee_name": "Assigned"

                # time sheet fields
                ,"timeSheetID":"ID"
                ,"dateFrom":"From"
                ,"dateTo":"To"
                ,"status":"Status"
                ,"person":"Owner"
                ,"duration":"Duration"
                ,"totalHours":"Hrs"
                ,"amount":"$"
                ,"projectName":"Project"
 
                # time sheet item fields
                ,"timeSheetID":"ID"
                ,"timeSheetItemID":"Item ID"
                ,"dateTimeSheetItem":"Date"
                ,"taskID":"taskID"
                ,"comment":"Comment"
                ,"timeSheetItemDuration":"Hours"
                ,"rate":"$"
                ,"worth":"Worth"
                ,"hoursBilled":"Total"
                ,"taskLimit":"Limit"
                ,"limitWarning":"Warning"
                }

                #Other task fields, that may one day require labels
                #personID
                #closerID
                #creatorID
                #managerID
                #projectID
                #parentTaskID
                #duplicateTaskID
                #clientID
                #taskStatusColour
                #rateUnit
                #rateUnitID
                #currency
                #taskComments
                #percentComplete
                #timeWorst
                #taskTypeImage
                #dateTargetCompletion
                #projectShortName
                #taskStatus
                #dateAssigned
                #project_name
                #dateClosed
                #dateCreated    
                #dateTargetStart
                #newSubTask
                #taskLink                                          
                #taskURL                                 
                #dateActualStart
                #taskModifiedUser
                #dateActualCompletion
                #timeBest




  row_timeSheet = ["timeSheetID","ID","dateFrom","From","dateTo","To","status","Status","person","Owner","duration","Duration","totalHours","Hrs","amount","$","projectName","Project"]
  row_timeSheetItem = ["timeSheetID","ID","timeSheetItemID","Item ID","dateTimeSheetItem","Date","taskID","taskID","comment","Comment","timeSheetItemDuration","Hours","rate","$","worth","Worth","hoursBilled","Total","taskLimit","Limit","limitWarning","Warning"]

  def __init__(self,url=""):

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
        self.print_table(projects, ["projectID","ID","projectName","Project"])
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
        self.print_table(tasks, ["taskID","ID","taskName","Task","projectName","Project"])
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
        self.print_table(clients, ["clientID","ID","clientName","Client"])
        self.die("Found more than one client matching: %s" % ops["clientName"])
      elif len(clients) == 1:
        return clients.keys()[0]

  def print_task(self, id):
    # print a descriptive view of a task
    rtn = self.get_list("task",{"taskID": id})

    # Print it out
    self.print_table(rtn, ["taskID","Task ID","projectID","Project","status","Status","person","Owner"])

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
    all_ops = {}
    rtn = {}
    remainder = ""
    
    help_string = self.get_subcommand_help(command_list, ops, str)

    for x in ops:
      # These args go straight to getops
      short_ops += x[0]
      long_ops.append(re.sub("=.*$","=",x[1]).strip())

      # And this is used below to build up a dictionary to return
      all_ops[re.sub("=.*$","",x[1]).strip()] = ["-"+x[0].replace(":",""), "--"+re.sub("=.*$","",x[1]).strip()]
    
    try:
      options, remainder = getopt.getopt(command_list[2:], short_ops, long_ops)
    except:
      print help_string
      sys.exit(0)

    for k,v in all_ops.items():
      rtn[k] = ""
      for opt, val in options:
        if opt in v:
          if val != "": # have to do it like this for eg -q which has no args
            rtn[k] = val
          else:
            rtn[k] = True

    if rtn['help']:
      print help_string
      sys.exit(0)
    
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

  def get_only_these_fields(self,rows,only_these_fields):
    rtn = []
    inverted_field_names = dict([[v,k] for k,v in self.field_names.items()])

    # Allow the display of custom fields
    if type(only_these_fields) == type("string"):
      # Print all fields
      if only_these_fields.lower() == "all":
        for k,v in rows.items():
          for name,value in v.items():
            rtn.append(name)
            if name in self.field_names:
              rtn.append(self.field_names[name])
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
          if name in self.field_names:
            rtn.append(self.field_names[name])
          else:
            rtn.append(name)
      return rtn;
    return only_these_fields

  def get_sorted_rows(self,rows,sortby,fields):
    rows = rows.items()
    if not sortby:
      return rows
    inverted_field_names = dict([[v,k] for k,v in self.field_names.items()])

    sortby = sortby.split(",")
    sortby.reverse()    

    # Check that any attempted sortby columns are actually in the table
    for k in sortby:
      # Strip leading underscore (used in reverse sorting eg: _Rate)
      if re.sub("^_","",k) not in fields:
        self.err("Sort column not found: "+k)

    def sort_func(row):
      try: val = row[1][inverted_field_names[f]]
      except:
        try: val = row[1][self.field_names[f]]
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

  def print_table(self, rows, only_these_fields, sort=False, transforms={}):
    # For printing out results in an ascii table or CSV format
    if self.quiet: return
    if not rows: return 

    table = PrettyTable()

    only_these_fields = self.get_only_these_fields(rows,only_these_fields)
    field_names = only_these_fields[1::2]
    table.set_field_names(field_names)

    # Re-order the table, this changes the dict to a list i.e. dict.items().
    rows = self.get_sorted_rows(rows,sort,only_these_fields)

    # Hide the frame and header if --csv
    if self.csv:
      table.set_border_chars(vertical=",",horizontal="",junction="")
      table.padding_width=0

    for label in field_names:
      if '$' in label:
        table.set_field_align(label, "r")
      else:
        table.set_field_align(label, "l")

    if rows:
      for k,row in rows:
        r = []
        for v in only_these_fields[::2]: 
          str = ''
          success = False
              
          if v in row:
            str = row[v]
            success = True
  
          if v in transforms:
            str = transforms[v](str)
            success = True

          if v in self.user_transforms:
            str = self.user_transforms[v](str,row)
            success = True

          if v in self.field_names:
            other_v = self.field_names[v]
            if other_v in self.user_transforms:
              str = self.user_transforms[other_v](str,row)
              success = True

          if not success:
            self.err('Bad field name: '+v)

          if not str:
            str = ''

          r.append(str)
        table.add_row(r)
    lines = table.get_string(header=not self.csv)
    # If csv, need to manually strip out the leading and trailing tab on
    # each line as well as compress the whitespace in the fields
    if self.csv:
      s = ''
      for line in lines[1:-1].split("\n"):
        line = re.sub("\s+,",",",line) # strip out whitespace padding 
        s+= line[1:-1]+"\n"            # strip out leading and trailing character
      lines = s[:-1]

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
    self.worklog = worklog()
  
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




