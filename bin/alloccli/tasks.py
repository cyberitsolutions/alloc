"""alloccli subcommand for viewing a list of tasks."""
from alloc import alloc

class tasks(alloc):
  """Print a list of tasks."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))
  ops.append(('t:','task=ID|NAME   ','A task ID, or a fuzzy match for a task name.'))
  ops.append(('s:','status=NAME    ','A task\'s status. Can accept multiple values, eg: "open,pending" eg: "open,pending_info". Default: "open"'))
  ops.append((''  ,'type=NAME      ','A task\'s type, eg: "Task" eg: "Fault,Message"'))
  ops.append(('a:','assignee=NAME  ','A task\'s assignee, username or first and surname, Eg: "jon" Eg: "all" Eg: "NULL". Defaults to yourself.'))
  ops.append(('m:','manager=NAME   ','A task\'s manager, username or first and surname".'))
  ops.append(('c:','creator=NAME   ','A task\'s creator, username or first and surname".'))
  ops.append(('o:','order=NAME     ','The order the Tasks are displayed in. Default: "Priority,Type,_Rate,status". (Underscore is for descending sort).')) 
  ops.append(('f:','fields=LIST    ','The commar separated list of fields you would like printed, eg: "all" eg: "taskID,Status,taskStatus,Proj Pri"')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= __doc__
  help_text+= "\n\n%s\n\nIf called without arguments this program will display all tasks that are assigned to you."

  def run(self, command_list):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    order = ''
    if o['order']: order = o['order']

    # Get personID, either assignee or logged in user
    personID = ''
    if o['assignee'].lower() == 'null':
      personID = 'NULL'
    elif not o['assignee']:
      personID = self.get_my_personID()
    elif o['assignee'] != 'all':
      personID = self.person_to_personID(o['assignee'])

    managerID = ''
    if o['manager']:
      managerID = self.person_to_personID(o['manager'])

    creatorID = ''
    if o['creator']:
      creatorID = self.person_to_personID(o['creator'])

    # Get a projectID either passed via command line, or figured out from a project name
    projects = {}
    projectIDs = []

    if self.is_num(o['project']):
      projectIDs.append(o['project'])
    elif o['project']:
      filter = {}
      filter["personID"] = personID
      filter["projectStatus"] = "Current"
      filter["projectName"] = o['project']
      projects = self.get_list("project",filter)
      if not projects or len(projects) == 0:
        projectIDs.append(0)
      if projects:
        for pID,v in projects.items():
          projectIDs.append(int(pID))
      
    # Setup options for the task search
    ops = {}
    ops["personID"] = personID
    ops["managerID"] = managerID
    ops["creatorID"] = creatorID
    ops["projectIDs"] = projectIDs
    ops["taskView"] = "prioritised"
    ops["showTimes"] = True
    o["status"] = o["status"] or "open"
    ops['taskStatus'] = o['status'].split(',')
    ops['taskTypeID'] = o['type'].split(',')

    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      ops["taskID"] = o['task']
      if 'taskTypeID' in ops: del ops["taskTypeID"]
      if 'taskStatus' in ops: del ops["taskStatus"]
      if 'personID' in ops: del ops["personID"]
    elif o['task']:
      ops["taskName"] = o["task"]

    # Get list of tasks
    r = self.get_list("task",ops)

    if not o['fields']:
      if not order: order = "priorityLabel,taskTypeID,_rate,taskStatusLabel"
      fields = "taskID,taskTypeID,taskStatusLabel,priorityLabel,timeExpected,timeLimit,timeActual,rate,projectName,taskName"
    else:
      fields = o["fields"]

    if r:
      self.print_table("task",r,fields,order)

