from alloc import alloc

class time(alloc):

  one_line_help = "Add time to a time sheet. Create the time sheet if necessary."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('n' ,'dryrun         ','Perform a dry run, no data gets updated.'))
  ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))
  ops.append(('t:','task=ID|NAME   ','A task ID, or a fuzzy match for a task name.')) 
  ops.append(('d:','date=YYYY-MM-DD','The date that the work was performed.')) 
  ops.append(('h:','hours=NUM      ','The number of hours worked.')) 
  ops.append(('c:','comment=COMMENT','The time sheet item comment.')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= """\n\n%s

If run without arguments this program will run interactively.

Examples:
alloc time
alloc time --task 1234 --hours 2.5 --comment 'Worked on foo.'"""

  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Or are we running this script interactively?
    interactive = True
    for k,v in o.items():
      if v and k != 'dryrun' and k != 'quiet':
        interactive = False

    # Get vars interactively
    if interactive and not remainder:
      o['task'] = raw_input("Task ID or some text from a task's name, or hit ENTER for none: ")
      if not o['task']:
        o['project'] = raw_input("Project ID or some text from a project's name: ")
      o['hours'] = raw_input("The number of hours you are billing: ")
      o['date'] = raw_input("The date that the work was performed, or hit ENTER for %s: " % self.today())
      o['comment'] = raw_input("Comments: ")


    # Initialize some variables
    self.quiet = o['quiet']
    self.csv = o['csv']
    self.dryrun = o['dryrun']
    timeSheetID = 0
    personID = self.get_my_personID()
    projectID = 0
    taskID = 0

    if not o['hours']:
      self.die('No quantity of hours has been specified.')

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'],personID)

    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      taskID = o['task']
    elif o['task']:
      tops = {}
      tops["taskName"] = o["task"]
      tops["personID"] = personID
      tops["taskView"] = "prioritised"
      tops["taskStatus"] = "open"
      taskID = self.search_for_task(tops)


    # If we've got a taskID, then we know all the details required to add time
    if taskID:
      self.msg("Attempting to add time for work done on this task: %s" % taskID)
      tasks = self.get_list("task",{"taskID":taskID,"taskView":"prioritised"})
      if tasks:
        self.print_table(tasks, ["taskID","ID","taskName","Task","projectName","Project"])
      else:
        self.die("Unable to find task with taskID: %s" % taskID)

      # Add time sheet item
      rtn = self.add_time_by_task(taskID, o['hours'], o['date'], o['comment'])
      timeSheetID = rtn['timeSheetID']

    # Or of we're just adding time that's not related to a particular task ...
    elif projectID:
      self.msg("Attempting to add time for work done on this project: %s" % projectID)
      projects = self.get_list("project",{"projectID":projectID})
      if projects:
        self.print_table(projects, ["projectID","ID","projectName","Project"])
      else:
        self.die("Unable to find project with projectID: %s" % projectID)

      # Add time sheet item
      rtn = self.add_time_by_project(projectID, o['hours'], o['date'], o['comment'])
      timeSheetID = rtn['timeSheetID']

    # No task or project means we don't add the time
    else:
      self.die("No task or project specified.")


    # We should only have a timeSheetID if a new timeSheetItem was successfully added.
    if timeSheetID:
      self.print_table(self.get_list("timeSheet",{"timeSheetID": timeSheetID}), self.row_timeSheet, sort="ID")
      self.print_table(self.get_list("timeSheetItem",{"timeSheetID": timeSheetID}), self.row_timeSheetItem, sort="Date")
      self.yay("Time added to time sheet: %s" % timeSheetID)
    elif not o['dryrun']:
      self.die("No time was added.")

