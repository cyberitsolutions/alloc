"""alloccli subcommand for billing time for work done."""
from alloc import alloc
import sys
import datetime
import time
import threading

class timer(threading.Thread):
  """Stopwatch, the timer should stop counting when ctrl-z/paused"""
  started = datetime.datetime.now()
  seconds = 0

  def run(self):
    while True:
      self.seconds += 1
      time.sleep(1)


class work(alloc):
  """Add time to a time sheet. Create the time sheet if necessary."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('q' , 'quiet          ', 'Run with no output except errors.'))
  ops.append(('n' , 'dryrun         ', 'Perform a dry run, no data gets updated.'))
  ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.')) 
  ops.append(('d.', 'date=YYYY-MM-DD', 'The date that the work was performed.')) 
  ops.append(('h.', 'hours=NUM      ', 'The amount of time worked. Eg: 2.5 eg: 150m')) 
  ops.append(('m.', 'multiplier=NUM ', 'The rate multiplier for the time worked.\n'
                                       '(eg: 0=no-charge, 1=standard, 1.5=time-and-a-half,\n'
                                       '2=double-time, 3=triple-time). Default: 1')) 
  ops.append(('c.', 'comment=COMMENT', 'The time sheet item comment.')) 
  ops.append((''  , 'tsid=ID        ', 'Add time to a specific time sheet (optional).'))
  ops.append(('i' , 'interactive    ', 'Run a live work timer interactively.')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

If run without arguments this program will prompt for information.

Examples:

Get information manually:
$ alloc work

Specify information on command line:
$ alloc work --task 1234 --hours 2.5 --comment 'Worked on foo.'

Run a live work timer interactively:
$ alloc work -i -t 1234
--- Timer started. Enter comment:
--- (ctrl-z/fg to pause, ctrl-c to cancel, ctrl-d to create time sheet item.
--- Task: 1234 Do some stuff...
* worked on stuff
* did something good
* did something bad
<ctrl-d>
$
(note that you can use ctrl-z and fg to pause and unpause the timer.
Unfortunately doing so appears to flush the input buffer!)
"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Gather information manually?
    prompt = True
    for k, v in o.items():
      if v and (k != 'dryrun' and k != 'quiet' and k != 'interactive'):
        prompt = False

    # Get vars manually
    if prompt:
      o['task'] = raw_input("Task ID or some text from a task's name, or hit ENTER for none: ")
      if not o['task']:
        o['project'] = raw_input("Project ID or some text from a project's name: ")
      o['hours'] = raw_input("The number of hours you are billing: ")
      o['multiplier'] = raw_input("The rate multiplier (0, 1, 1.5, 2, 3), or hit ENTER for 1: ")
      o['date'] = raw_input("The date that the work was performed, or hit ENTER for %s: " % self.today())
      o['comment'] = raw_input("Comments: ")

    # If we're running interactively
    if o['interactive']:
      t = timer()
      t.daemon = True
      t.start()
      self.msg('Timer started at '+str(t.started.replace(microsecond=0)))
      self.msg('Enter comment (ctrl-z to pause, ctrl-c to cancel, ctrl-d to create)')
      try:
        k_, v = self.get_list('task', { 'taskID' : o['task'], 'taskView' : 'prioritised' }).popitem()
      except:
        self.die("Task not found.")
      self.msg(v['taskTypeID']+': '+v['taskID']+' '+v['taskName'])

      # Grab stdin
      try:
        o['comment'] = sys.stdin.read()
      except (IOError):
        self.die("Exiting.")
      except (KeyboardInterrupt):
        self.die("Exiting.")

      o['date'] = str(t.started.date())
      o['hours'] = str(t.seconds / 60.0 / 60.0)

    # Initialize some variables
    self.quiet = o['quiet']
    self.dryrun = o['dryrun']
    timeSheetID = 0
    personID = self.get_my_personID()
    projectID = 0
    taskID = 0

    if o['date']:
      o['date'] = self.parse_date(o['date'])

    if not o['multiplier']:
      o['multiplier'] = 1

    if not o['hours']:
      self.die('No quantity of hours has been specified.')
  
    # If the time period is expressed like 30m then convert the minutes into hours
    elif o['hours'][-1].lower() == "m":
      o['hours'] = o['hours'][:-1]
      o['hours'] = float(o['hours'])/60

    elif o['hours'][-1].lower() == "h":
      o['hours'] = o['hours'][:-1]

    if float(o['hours']) % .25 != 0:
      self.msg('The hours billed is not a multiple of 15 minutes. Continuing.')


    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'], personID)

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
      tasks = self.get_list("task", {"taskID":taskID, "taskView":"prioritised"})
      if tasks:
        if not o['quiet']:
          self.print_table("task", tasks, ["taskID", "taskName", "projectName"])
      else:
        self.die("Unable to find task with taskID: %s" % taskID)

      # Add time sheet item
      stuff = {}
      stuff['taskID'] = taskID
      stuff['duration'] = o['hours']
      stuff['multiplier'] = o['multiplier']
      stuff['date'] = o['date']
      stuff['comment'] = o['comment']
      if o['tsid']: stuff['timeSheetID'] = o['tsid']
      timeSheetID = self.add_time(stuff)

    # Or of we're just adding time that's not related to a particular task ...
    elif projectID:
      self.msg("Attempting to add time for work done on this project: %s" % projectID)
      projects = self.get_list("project", {"projectID":projectID})
      if projects:
        self.print_table("project", projects, ["projectID", "projectName"])
      else:
        self.die("Unable to find project with projectID: %s" % projectID)

      # Add time sheet item
      stuff = {}
      stuff['projectID'] = projectID
      stuff['hours'] = o['hours']
      stuff['multiplier'] = o['multiplier']
      stuff['date'] = o['date']
      stuff['comment'] = o['comment']
      timeSheetID = self.add_time(stuff)

    # No task or project means we don't add the time
    else:
      self.die("No task or project specified.")


    # We should only have a timeSheetID if a new timeSheetItem was successfully added.
    if timeSheetID:
      if not o['quiet']:
        self.print_table("timeSheet", self.get_list("timeSheet", {"timeSheetID": timeSheetID}),
                         self.row_timeSheet, sort="ID")
        self.print_table("timeSheetItem", self.get_list("timeSheetItem", {"timeSheetID": timeSheetID}),
                         self.row_timeSheetItem, sort=["dateTimeSheetItem","timeSheetItemID"])
        self.yay("Time added to time sheet: %s" % timeSheetID)
    elif not o['dryrun']:
      self.die("No time was added.")

  def add_time(self, stuff):
    """Add time to a time sheet using a task as reference."""
    if self.dryrun: return ''
    args = {}
    args["method"] = "add_timeSheetItem"
    args["options"] = stuff
    return self.make_request(args)


