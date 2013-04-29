"""alloc library"""
import os
import sys
import simplejson
import re
import urllib
import urllib2
import datetime
import ConfigParser
import subprocess
from netrc import netrc
from urlparse import urlparse
from alloc_output_handler import alloc_output_handler
from alloc_cli_arg_handler import alloc_cli_arg_handler

class alloc(object):
  """Provide a parent class from which the alloc subcommands can extend"""

  client_version = "1.8.7"
  client_name = os.path.basename(sys.argv[0])
  url = ''
  username = ''
  quiet = ''
  dryrun = ''
  sessID = ''
  alloc_dir = os.environ.get(client_name.upper()+'_HOME') or os.path.join(os.environ['HOME'], '.'+client_name)
  debug = os.environ.get(client_name.upper()+'_DEBUG')
  config = {}
  user_transforms = {}
  url_opener = None
  field_names = {
    "task" : {
      "taskID"                   :"ID"
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
      "timeSheetID"              :"ID"
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
     ,"recipient_tfID"           :"TFID"
     ,"commentPrivate"           :"Comm Priv"
    },

    "timeSheetItem" : {
      "timeSheetID"              :"ID"
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
      "transactionID"            :"ID"
     ,"fromTfName"               :"From TF"
     ,"tfName"                   :"Dest TF"
     ,"amount"                   :"Amount"
     ,"status"                   :"Status"
     ,"transactionDate"          :"Transaction Date"
    },
  
    "tf" : {
      "tfID"                     :"ID"
     ,"tfBalancePending"         :"Pending"
     ,"tfBalance"                :"Approved"
    },

    "client" : {
      "clientID"                 :"ID"
     ,"clientName"               :"Name"
    },
        
    "token" : {
      "tokenID"                  :"ID"
     ,"tokenHash"                :"Key"
    },

    "interestedParty" : {
      "interestedPartyID"        :"ID"
     ,"entity"                   :"Entity"
     ,"entityID"                 :"Entity ID"
     ,"fullName"                 :"Name"
     ,"emailAddress"             :"Email"
    },

    "person" : {
      "personID"                 :"ID"
     ,"firstName"                :"First Name"
     ,"surname"                  :"Surname"
     ,"username"                 :"Username"
     ,"emailAddress"             :"Email"
    },

    "project" : {
      "projectID"                :"ID"
     ,"projectName"              :"Proj Name"
    },

    "invoice" : {
      "invoiceID"                :"ID"
     ,"clientName"               :"Client"
     ,"invoiceNum"               :"Num"
     ,"invoiceDateFrom"          :"From"
     ,"invoiceDateTo"            :"To"
     ,"invoiceStatus"            :"Status"
     ,"status_label"             :"Payment"
     ,"amountPaidRejected"       :"Rejected"
     ,"amountPaidPending"        :"Pending"
     ,"amountPaidApproved"       :"Approved"
     ,"iiAmountSum"              :"Total"
    },

    "invoiceItem" : {
      "invoiceID"                :"ID"
     ,"invoiceItemID"            :"Item ID"
     ,"clientName"               :"Client"
     ,"invoiceNum"               :"Num"
     ,"iiDate"                   :"Date"
     ,"iiAmount"                 :"Amount"
     ,"iiQuantity"               :"Qty"
     ,"iiUnitPrice"              :"Per unit"
     ,"iiMemo"                   :"Comment"
    }
  }


  row_timeSheet = ["timeSheetID", "dateFrom", "dateTo", "status", "person", "duration", "totalHours", "amount", "projectName"]
  row_timeSheetItem = ["timeSheetID", "timeSheetItemID", "dateTimeSheetItem", "taskID", "timeSheetItemDuration",
                       "rate", "worth", "hoursBilled", "timeLimit", "limitWarning", "comment"]

  def __init__(self, url=""):

    # Grab a storage dir to work in
    if self.alloc_dir[-1:] != os.sep:
      self.alloc_dir += os.sep

    # Create ~/.alloc if necessary
    if not os.path.exists(self.alloc_dir):
      self.dbg("Creating: "+self.alloc_dir)
      os.mkdir(self.alloc_dir)

    # Create ~/.alloc/config
    if not os.path.exists(self.alloc_dir+"config"):
      self.create_config(self.alloc_dir+"config")

    # Create ~/.alloc/transforms.py
    if not os.path.exists(self.alloc_dir+"transforms.py"):
      self.create_transforms(self.alloc_dir+"transforms.py")

    # Load ~/.alloc/config into self.config{}
    self.load_config(self.alloc_dir+"config")

    # Load any user-customizations to table print output
    self.load_transforms()

    if not url:
      if "url" in self.config and self.config["url"]:
        url = self.config["url"]
      if not url:
        self.die("No "+self.client_name+" url specified!")

    # Grab session ~/.alloc/session
    if os.path.exists(self.alloc_dir+"session"):
      self.sessID = self.load_session(self.alloc_dir+"session")

    self.url = url
    self.username = ''
    self.quiet = ''
    self.csv = False

    self.username, self.password, self.http_username, self.http_password = self.get_credentials()
    self.initialize_http_connection()

  def initialize_http_connection(self):
    """This is for a https connection with basic http auth,
       it is not the actual alloc user login credentials"""
    if self.http_password:
      # create a password manager
      top_level_url = "/".join(self.url.split("/")[0:3])
      password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
      password_mgr.add_password(None, top_level_url, self.http_username, self.http_password)
      handler = urllib2.HTTPBasicAuthHandler(password_mgr)
      self.url_opener = urllib2.build_opener(handler)
    else:
      self.url_opener = urllib2.build_opener()

    self.url_opener.addheaders = [('User-agent', self.client_name+'-cli %s' % self.username)]
    urllib2.install_opener(self.url_opener)

  def create_config(self, f):
    """Create a default ~/.alloc/config file."""
    self.dbg("Creating and populating: "+f)
    default = "[main]"
    default += "\n#url: http://"+self.client_name+"/services/json.php"
    default += "\n#"+self.client_name+"_user: $ALLOC_USER"
    default += "\n#"+self.client_name+"_pass: $ALLOC_PASS"
    default += "\n#"+self.client_name+"_http_user: $ALLOC_HTTP_USER"
    default += "\n#"+self.client_name+"_http_pass: $ALLOC_HTTP_PASS"
    default += "\n#"+self.client_name+"_trunc: 1" 
    # Write it out to a file
    fd = open(f, 'w')
    fd.write(default)
    fd.close()

  def load_config(self, f):
    """Read the ~/.alloc/config file and load it into self.config[]."""
    config = ConfigParser.ConfigParser()
    config.read([f])
    section = os.environ.get(self.client_name.upper()) or 'main'
    options = config.options(section)
    for option in options:
      self.config[option.lower()] = config.get(section, option)
      self.dbg("CONF: "+option.lower()+ ": "+self.config[option.lower()])
    if self.client_name.upper()+'_TRUNC' in os.environ:
      self.config[self.client_name+'_trunc'] = os.environ.get(self.client_name.upper()+'_TRUNC')

  def create_transforms(self, f):
    """Create a default ~/.alloc/transforms.py file for field manipulation."""
    if os.path.exists(self.alloc_dir+"transforms") and not os.path.exists(self.alloc_dir+"transforms.py"):
      # upgrade old transforms to transforms.py
      self.dbg("Renaming: "+self.alloc_dir+"transforms"+" to "+self.alloc_dir+"transforms.py")
      os.rename(self.alloc_dir+"transforms", self.alloc_dir+"transforms.py")
    else:
      # else create an example transforms.py
      self.dbg("Creating example transforms.py file: "+f)
      default = "# Add any field customisations here. eg:\n"
      default += "# user_transforms = { 'Priority' : lambda x,row: x[3:] }\n\n"
      # Write it out to a file
      fd = open(f, 'w')
      fd.write(default)
      fd.close()

  def load_transforms(self):
    """Load the ~/.alloc/transforms.py module into self.user_transforms."""
    try:
      sys.path.append(self.alloc_dir)
      from transforms import user_transforms
      self.user_transforms = user_transforms
    except:
      self.user_transforms = {}

  def create_session(self, sessID):
    """Create a ~/.alloc/session file to store the alloc session key."""
    old_sessID = self.load_session(self.alloc_dir+"session")
    if not old_sessID or old_sessID != sessID:
      self.dbg("Writing to: "+self.alloc_dir+"session: "+sessID)
      # Write it out to a file
      fd = open(self.alloc_dir+"session", 'w')
      fd.write(sessID)
      fd.close()

  def load_session(self, f):
    """Read the ~/.alloc/session and return the alloc session ID."""
    try:
      fd = open(f)
      sessID = fd.read().strip()
      fd.close()
    except:
      sessID = ""
    return sessID

  def search_for_project(self, projectName, personID=None, die=True):
    """Search for a project like *projectName*."""
    if projectName:
      ops = {}
      if personID:
        ops["personID"] = personID
      ops["projectStatus"] = "Current"
      ops["projectName"] = projectName
      projects = self.get_list("project", ops)
      if len(projects) == 0:
        self.die("No project found matching: %s" % projectName)
      elif len(projects) > 1 and die:
        self.print_table("project", projects, ["projectID", "projectName"])
        self.die("Found more than one project matching: %s" % projectName)
      elif len(projects) > 1 and not die:
        return projects.keys()
      elif len(projects) == 1:
        return projects.keys()[0]
        
  def search_for_task(self, ops):
    """Search for a task like *taskName*."""
    if "taskName" in ops:
      tasks = self.get_list("task", ops)
      
      if not tasks:
        self.die("No task found matching: %s" % ops["taskName"])
      elif tasks and len(tasks) >1:
        self.print_table("task", tasks, ["taskID", "taskName", "projectName"])
        self.die("Found more than one task matching: %s" % ops["taskName"])
      elif len(tasks) == 1:      
        return tasks.keys()[0]   

  def search_for_client(self, ops):
    """Search for a client like *clientName*."""
    if "clientName" in ops:
      clients = self.get_list("client", ops)

      if not clients:
        self.die("No client found matching: %s" % ops["clientName"])
      elif clients and len(clients) >1:
        self.print_table("client", clients, ["clientID", "clientName"])
        self.die("Found more than one client matching: %s" % ops["clientName"])
      elif len(clients) == 1:
        return clients.keys()[0]

  def print_task(self, taskID, prependEmailHeader=False):
    """Return a plaintext view of a task and its details."""
    rtn = self.get_list('task', {'taskID':taskID, 'taskView':'prioritised', 'showTimes':True})

    k, r = rtn.popitem()
    del(k)

    h = ''
    if prependEmailHeader:
      d = datetime.datetime.strptime(r['dateCreated'], '%Y-%m-%d %H:%M:%S')
      h = "From allocPSA "+d.strftime("%a %b  %d %H:%M:%S %Y")
      h += "\nX-Alloc-Task: "+r['taskName']
      h += "\nX-Alloc-TaskID: "+r['taskID']
      h += "\nX-Alloc-Project: "+r['projectName']
      h += "\nX-Alloc-ProjectID: "+r['projectID']
      h += "\nSubject: "+r['taskID']+" "+r['taskName']+" ["+r['priorityLabel']+"] "
      h += "\n"

    underline_length = len(r['taskTypeID']+': '+r['taskID']+' '+r['taskName'])

    s = '\n'+r['taskTypeID']+': '+r['taskID']+' '+r['taskName']
    s += '\n'.ljust(underline_length+1, '=')
    s += '\n'
    if r['priorityLabel']: s += '\n'+'Priority: '+r['priorityLabel'].ljust(26)+r['taskStatusLabel']
    s += '\n'
    if r['projectName']:  s += '\nProject: '+r['projectName']
    if 'projectPriorityLabel' in r and r['projectPriorityLabel']: s += ' ['+r['projectPriorityLabel']+']'
    if r['parentTaskID']: s += '\nParent Task: '+r['parentTaskID']
    if r['projectName'] or r['parentTaskID']: s += '\n'
    if r['creator_name']:  s += '\nCreator:  '+r['creator_name'].ljust(25)+' '+r['dateCreated']
    if r['assignee_name']: s += '\nAssigned: '+r['assignee_name'].ljust(25)+' '+r['dateAssigned']
    if r['manager_name']:  s += '\nManager:  '+r['manager_name'].ljust(25)
    if r['dateClosed']:
      s += '\nCloser:   '+r['closer_name'].ljust(25)+' '+r['dateClosed']
    s += '\n'
    s += '\nB/E/W Estimates:  '+(r['timeBestLabel'] or '--')+' / '+(r['timeExpectedLabel'] or '--')
    s += ' / '+(r['timeWorstLabel'] or '--')+'  '+(r['estimator_name'] or '')
    s += '\nActual/Limit Hrs: %s / %s ' % (r['timeActualLabel'] or '--', r['timeLimitLabel'] or '--')
    s += '\n'

    if r['dateTargetStart'] or r['dateTargetCompletion']:
      s += '\nTarget Start: %-18s Target Completion: %-18s ' % (r['dateTargetStart'], r['dateTargetCompletion'])
    if r['dateActualStart'] or r['dateActualCompletion']:
      s += '\nActual Start: %-18s Actual Completion: %-18s ' % (r['dateActualStart'], r['dateActualCompletion'])

    if r['taskDescription']:
      s += '\n'
      s += '\nDescription'
      s += '\n-----------'
      s += '\n'
      #s += '\n'.join(wrap(r['taskDescription'], 75))+'\n' # this seems to not work very well.
      s += '\n'+r['taskDescription']

    s += '\n'
    return h+s

  def get_args(self, command_list, ops, s):
    """Wrapper for handling command line arguments."""
    a = alloc_cli_arg_handler()
    return a.get_args(self, command_list, ops, s)

  def print_table(self, entity, rows, only_these_fields, sort=False, transforms=None):
    """Wrapper for printing the output to the screen in a table or csv."""
    t = alloc_output_handler()
    return t.print_table(self, entity, rows, only_these_fields, sort, transforms)

  def get_my_personID(self, nick=None):
    """Get current user's personID."""
    ops = {}
    ops["username"] = self.username
    if nick:
      ops["username"] = nick
    
    rtn = self.get_list("person", ops)
    for i in rtn:
      return i

  def is_num(self, obj):
    """Return True is the obj is numeric looking, or it obj is a list of numbers."""
    # There's got to be a better way to tell if something is a number 
    # if not a list, force it to a list
    if type(obj) != type([]):
      obj = [obj]

    ok = True
    if len(obj) == 0:
      ok = False

    for v in obj:
      try:
        if float(v) >= 0:
          pass
        else:
          ok = False
      except:
        ok = False
    return ok

  def to_num(self, obj):
    """Gently coerce obj into a number."""
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
    """Obtain user's alloc login and http auth credentials."""
    env_u  = os.environ.get(self.client_name.upper()+'_USER')
    env_p  = os.environ.get(self.client_name.upper()+'_PASS')
    env_hu = os.environ.get(self.client_name.upper()+'_HTTP_USER')
    env_hp = os.environ.get(self.client_name.upper()+'_HTTP_PASS')

    con_u  = self.config.get(self.client_name.lower()+'_user')
    con_p  = self.config.get(self.client_name.lower()+'_pass')
    con_hu = self.config.get(self.client_name.lower()+'_http_user')
    con_hp = self.config.get(self.client_name.lower()+'_http_pass')

    try:
      net = netrc().hosts[urlparse(self.url).hostname]
    except:
      net = ('', '', '')

    net_u  = net[0]
    net_p  = net[2]
    net_hu = net[0]
    net_hp = net[1]

    # Priority: Shell vars, ~/.alloc/config, ~/.netrc
    u  = env_u  or con_u  or net_u
    p  = env_p  or con_p  or net_p
    hu = env_hu or con_hu or net_hu
    hp = env_hp or con_hp or net_hp

    self.dbg("USING CONF: user:"+str(u)+" pass:"+str(p)+" httpuser:"+str(hu)+" httppass:"+str(hp))

    if u is None or u == '' or p is None or p == '':
      self.err("The settings "+self.client_name.upper()+"_USER and "+self.client_name.upper()+"_PASS are required.")
      self.err("The settings "+self.client_name.upper()+"_HTTP_USER and "+self.client_name.upper()+"_HTTP_PASS are optional.")
      self.err("Set any of them either in the environment as shell variables,")
      self.die("or in your ~/.netrc or your ~/."+self.client_name+"/config.")
    return u, p, hu, hp

  def get_list(self, entity, options):
    """The canonical method of retrieving a list of entities from alloc."""
    options["skipObject"] = '1'
    options["return"] = "array"
    args = {}
    args["entity"] = entity
    args["options"] = options
    args["method"] = "get_list"
    return self.make_request(args)

  def get_help(self, topic): 
    """Retrieve a help message from the alloc server regarding its API."""
    args = {}
    args["topic"] = topic
    args["method"] = "get_help"
    return self.make_request(args)

  def authenticate(self):
    """Perform an authentication against the alloc server."""
    self.dbg("calling authenticate()")
    args =  { "authenticate":True, "username":self.username, "password":self.password }
    if not self.sessID:
      self.dbg("ATTEMPTING AUTHENTICATION.")
      rtn = self.make_request(args)
    else:
      rtn = {"sessID":self.sessID}

    if "sessID" in rtn and rtn["sessID"]:
      self.sessID = rtn["sessID"]
      self.create_session(self.sessID)
      return self.sessID
    else:
      self.die("Error authenticating: %s" % rtn)

  def make_request(self, args):
    """Perform an HTTP request to the alloc server."""
    try:
      self.dbg("make_request(): "+str(args))
      self.url_opener.open(self.url)
    except urllib2.HTTPError, e:
      self.err(str(e))
      self.err("Possibly a bad username or password for HTTP AUTH")
      self.err("The settings "+self.client_name.upper()+"_HTTP_USER and "+self.client_name.upper()+"_HTTP_PASS are required.")
      self.die("Set them either in the shell environment or in your ~/."+self.client_name+"/config")
    except Exception, e:
      self.die(str(e))

    args["client_version"] = self.client_version
    args["sessID"] = self.sessID
    rtn = urllib2.urlopen(self.url, urllib.urlencode(args)).read()
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
      rtn2 = urllib2.urlopen(self.url, urllib.urlencode(args)).read()
      try:
        return simplejson.loads(rtn2)
      except:
        self.err("Error(2): %s" % rtn2)
        if args and 'password' in args: args['password'] = '********'
        self.die("Args: %s" % args)
    return rtn

  def get_people(self, people, entity="", entityID=""):
    """Get a list of people."""
    args = {}
    args["options"] = people
    args["method"] = "get_people"
    args["entity"] = entity
    args["entityID"] = entityID
    return self.make_request(args)

  def get_alloc_html(self, url):
    """Perform a direct fetch of an alloc page, ala wget/curl."""
    return urllib2.urlopen(url).read()

  def today(self):
    """Wrapper to return the date, so I don't have to import datetime into all the modules."""
    return datetime.date.today()

  def msg(self, s):
    """Print a message to the screen (stdout)."""
    if not self.quiet:
      print "--- " + str(s)
      sys.stdout.flush()

  def yay(self, s):
    """Print a success message to the screen (stdout)."""
    if not self.quiet:
      print ":-] " + str(s)
      sys.stdout.flush()

  def err(self, s):
    """Print a failure message to the screen (stderr)."""
    sys.stderr.write("!!! "+str(s)+"\n")

  def die(self, s):
    """Print a failure message to the screen (stderr) and then halt."""
    self.err(s)
    sys.exit(1)

  def dbg(self, s):
    """Print a message to the screen (stdout) for debugging only."""
    if self.debug:
      print "DBG " + str(s)
      sys.stdout.flush()

  def parse_email(self, email):
    """Parse an email address from this: Jon Smit <js@example.com> into: addr, name."""
    addr = ''
    name = ''
    bits = email.split(' ')

    if len(bits) == 1:
      if '@' in bits[0]:
        addr = bits[0].replace('<', '').replace('>', '')
      else:
        name = bits[0]

    elif len(bits) > 1:

      if '@' in bits[-1:][0]:
        addr = bits[-1:][0].replace('<', '').replace('>', '')
        name = ' '.join(bits[:-1])
      else:
        name = ' '.join(bits)

    return addr, name

  def parse_date(self, text):
    """Convert a human readable date string into YYYY-MM-DD format."""
    if text:
      prefix = ''

      # YYYY-MM-DD
      if re.match(r'^\d{4}-\d{1,2}-\d{1,2}$', text):
        return text

      # YYYY-MM-DD HH:MM:SS
      if re.match(r'^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$', text):
        return text

      # The date string may be prefixed with a comparator eg >= or != etc.
      # [><=!]some date string
      m = re.match(r'([><=!]+)(.*)', text)
      if m:
        prefix = m.group(1)
        text = m.group(2)

      p = subprocess.Popen(['date', '-d', text, '+%F'], stdout=subprocess.PIPE, stderr=subprocess.PIPE)
      output, errors = p.communicate()
      output = output.strip()
      errors = errors.strip()
      if re.match(r'^\d{4}-\d{1,2}-\d{1,2}$', output):
        return prefix+output
      else:
        self.die("Couldn't convert date: "+text+" (returned: "+output+" "+errors+")")
    return text

  def person_to_personID(self, name):
    """Convert a person's name into their alloc personID."""
    if type(name) == type('string'):
      name = [name]

    r = []
    for n in name:
      ops = {}
      if ' ' in n:
        ops['firstName'], ops['surname'] = n.split(" ")
      else:
        ops["username"] = n
      rtn = self.get_list("person", ops)
      if rtn:
        for i in rtn:
          r.append(i)

      # else if they don't want all the records, then return an impossible personID
      elif n and n != '%' and n != '*' and n.lower() != 'all':
        r.append('1000000000000000000') # returning just zero doesn't work

    return r

  def parse_date_comparator(self, date):
    """Split a comparator and a date eg: '>=2011-10-10' becomes ['>=','2011-10-10']."""
    try:
      comparator, d = re.findall(r'[\d|-]+|\D+', date)
    except:
      comparator = '='
      d = date
    return d.strip(), comparator.strip() 

  def get_alloc_modules(self):
    """Get all the alloc subcommands/modules."""
    return sys.modules['alloccli'].__all__

  def get_cli_help(self, halt_on_error=True):
    """Get the command line help."""
    print "Usage: "+self.client_name+" command [OPTIONS]"
    print "Select one of the following commands:\n"
    for m in self.get_alloc_modules():
      if m == 'alloc':
        continue
      alloccli = __import__("alloccli."+m)
      subcommand = getattr(getattr(alloccli, m), m)

      # Print out the module's doc
      tabs = "\t "
      if len(m) <= 5: tabs = "\t\t "
      print "  "+m+tabs+str(subcommand.__doc__)

    print "\nEg: "+self.client_name+" command --help"

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
    """Get help for a particular command."""
    print "Select one of the following commands:\n"
    for m in self.get_alloc_modules():
      if m == 'alloc':
        continue
      alloccli = __import__("alloccli."+m)
      subcommand = getattr(getattr(alloccli, m), m)

      # Print out the module's doc
      tabs = "\t "
      if len(m) <= 5: tabs = "\t\t "
      print "  "+m+tabs+str(subcommand.__doc__)

    print "\nEg: tasks -t 1234"


  def which(self, name, flags=os.X_OK):
    """Search PATH for executable files with the given name."""
    result = []
    exts = [item for item in os.environ.get('PATHEXT', '').split(os.pathsep) if item]
    path = os.environ.get('PATH', None)
    if path is None:
      return ''
    for p in os.environ.get('PATH', '').split(os.pathsep):
      p = os.path.join(p, name)
      if os.access(p, flags):
        result.append(p)
      for e in exts:
        pext = p + e
        if os.access(pext, flags):
          result.append(pext)
    if len(result) >= 1:
      return result[0]
    else:
      return ''


