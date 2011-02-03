from alloc import alloc

class tasks(alloc):

  one_line_help = "Print a list of tasks."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('v' ,'verbose        ','Print the tasks\' descriptions.'))
  ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))
  ops.append(('t:','task=ID|NAME   ','A task ID, or a fuzzy match for a task name.'))
  ops.append(('s:','status=NAME    ','A task\'s status, eg: "open_inprogress" eg: "pending". Default: "open"'))
  ops.append((''  ,'type=NAME      ','A task\'s type, eg: "Task" eg: "Fault"'))
  ops.append(('a:','assignee=NAME  ','A task\'s assignee, username or first and surname" Default: you.'))
  ops.append(('m:','manager=NAME   ','A task\'s manager, username or first and surname".'))
  ops.append((''  ,'people         ','Show the task\'s creator, manager and assignee.'))
  ops.append(('o:','order=NAME     ','The order the Tasks are displayed in. Default: "Priority"')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= "\n\n%s\n\nIf called without arguments this program will display all your tasks."

  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    self.csv = o['csv']
    order = "Priority"
    if o['order']: order = o['order']

    # Get personID, either assignee or logged in user
    if o['assignee']:
      personID = self.person_to_personID(o['assignee'])
    else:
      personID = self.get_my_personID()

    managerID = ''
    if o['manager']:
      managerID = self.person_to_personID(o['manager'])

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
    ops["projectIDs"] = projectIDs
    ops["taskView"] = "prioritised"
    ops["showTimes"] = True
    ops["taskStatus"] = "open"
    if o["status"]: ops["taskStatus"] = o["status"]
    if o["type"]: ops["taskTypeID"] = o["type"]

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

    fields = ["taskID","Task ID"
             ,"taskTypeID","Type"
             ,"taskStatusLabel","Status"
             ,"priority projectPriority priorityFactor","Priority"
             ,"timeExpected","Est"
             ,"timeLimit","Limit"
             ,"timeActual","Act"
             ,"rate","Rate"
             ,"projectName","Project"
             ,"taskName","Task"
             ]
    if o['verbose']:
      fields.append("taskDescription")
      fields.append("Description")
    if o['people']:
      fields.append("creator_name")
      fields.append("Creator")
      fields.append("manager_name")
      fields.append("Manager")
      fields.append("assignee_name")
      fields.append("Assigned")

    if r:
      def seconds_to_hours(x):
        if self.is_num(x):
          return float(x) / 60 / 60
      transforms = { "timeActual" : seconds_to_hours }
      self.print_table(r,fields,order,transforms)

