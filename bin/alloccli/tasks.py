"""alloccli subcommand for viewing a list of tasks."""
from alloc import alloc

class tasks(alloc):
  """Print a list of tasks."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('p:', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('t:', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
  ops.append(('s:', 'status=NAME    ', 'A task\'s status.\n'
                                       '(eg: open pending eg: open pending_info. Default: open)'))
  ops.append(('y:', 'type=NAME      ', 'A task\'s type, eg: Task eg: Fault Message'))
  ops.append(('a:', 'assignee=NAME  ', 'A task\'s assignee, username or first and surname.\n'
                                       '(eg: "jon" eg: "all" eg: "NULL". Defaults to yourself.)'))
  ops.append(('m:', 'manager=NAME   ', 'A task\'s manager, username or first and surname".'))
  ops.append(('c:', 'creator=NAME   ', 'A task\'s creator, username or first and surname".'))
  ops.append(('o:', 'order=NAME     ', 'The order the Tasks are displayed in.\n'
                                       'Default: "-o Priority -o Type -o _Rate -o status" (underscore means reverse).'))
  ops.append(('f:', 'fields=LIST    ', 'The list of fields you would like printed.\n'
                                       '(eg: -f all eg: -f taskID -f Status -f taskStatus -f Proj\\ Pri)'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += "\n\n%s\n\nIf called without arguments this program will display all tasks that are assigned to you."

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    order = []
    if o['order']: order = o['order']

    # Get personID, either assignee or logged in user
    personID = []
    if not isinstance(o['assignee'], list) and o['assignee'].lower() == 'null':
      personID.append('')
    elif not o['assignee']:
      personID.append(self.get_my_personID())
    elif o['assignee'] != 'all':
      personID = self.person_to_personID(o['assignee'])

    managerID = []
    if o['manager']:
      managerID = self.person_to_personID(o['manager'])

    creatorID = []
    if o['creator']:
      creatorID = self.person_to_personID(o['creator'])

    # Setup options for the task search
    ops = {}
    ops["personID"] = personID
    ops["managerID"] = managerID
    ops["creatorID"] = creatorID
    if o['project']: ops["projectNameMatches"] = o['project']
    ops["taskView"] = "prioritised"
    ops["showTimes"] = True
    o["status"] = o["status"] or "open"
    ops['taskStatus'] = o['status']
    if o['type']: ops['taskTypeID'] = o['type']

    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      ops["taskID"] = o['task']
      if 'taskTypeID' in ops: del ops["taskTypeID"]
      if 'taskStatus' in ops: del ops["taskStatus"]
      if 'personID' in ops: del ops["personID"]
    elif o['task']:
      ops["taskName"] = o["task"]

    if not o['fields']:
      if not order: order = ["priorityLabel", "taskTypeID", "_rate", "taskStatusLabel"]
      fields = ["taskID", "taskTypeID", "taskStatusLabel", "priorityLabel", "timeExpected",
                "timeLimit", "timeActual", "rate", "projectName", "taskName"]
    else:
      fields = o["fields"]

    if 'timeBest' not in o['fields'] \
    and 'timeWorst' not in o['fields'] \
    and 'timeExpected' not in o['fields'] \
    and 'timeLimit' not in o['fields'] \
    and 'timeActual' not in o['fields']:
      del ops['showTimes']

    if 'showTimes' not in ops:
      if 'timeWorst'    in fields: fields.remove('timeWorst')
      if 'timeExpected' in fields: fields.remove('timeExpected')
      if 'timeLimit'    in fields: fields.remove('timeLimit')
      if 'timeActual'   in fields: fields.remove('timeActual')

    # Get list of tasks
    r = self.get_list("task", ops)

    if r:
      self.print_table("task", r, fields, order)

