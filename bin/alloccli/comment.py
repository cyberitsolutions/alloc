"""alloccli subcommand for adding comments."""
from alloc import alloc
import sys


class comment(alloc):
  """Add a new comment to a task, project, time sheet or client."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.')) 
  ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('c.', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client name.')) 
  ops.append(('i.', 'timesheet=ID   ', 'A time sheet ID.')) 
  ops.append(('r:', 'to=RECIPIENTS  ', 'Can be usernames, fullnames and/or email, "default",\n'
                                       '"internal" or "nobody".')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

The comment text is read from standard in. If you omit --to, the default
recipients are assumed. Due to a python bug, you may need to hit ctrl-d twice if
the cursor is not on an empty line. The current user is always added unless
'nobody' is used.

  $ alloc comment --task 1234
  --- Enter comment (ctrl-c to cancel, ctrl-d to send)
  --- Task: 1234 Foo the bar
  --- To: Alice Smith <as@example.com>, Bob Smith <bs@example.com>
  This is the comment

  It has line breaks.
  <ctrl-d>
  $

Start a new conversation, recipients are matched against username, real name,
email address and also client contact name.

  $ alloc comment --task 1234 --to conz --to jeremyc --to "David Keegel"
    --to alice@someclient.com --to "Bob Smith" --to "Clickety Clack <cc@example.com>"

The words 'default' and 'internal' can be used in place of an email address.

  $ alloc comment --task 1234 --to default --to conz
  $ alloc comment --task 1234 --to internal --to conz

In this example the recipients are going to be internal only, except for Clyde

  $ alloc comment --task 1234 --to internal --to "Clyde Client <cc@example.com>"
"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    self.msg('Enter comment (ctrl-c to cancel, ctrl-d to send)')

    personID = self.get_my_personID()
    taskID = 0
    projectID = 0
    timeSheetID = 0
    clientID = 0
    entity = ''
    entityID = 0
    recipients = {}

    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      taskID = o['task']
    elif o['task']:
      taskID = self.search_for_task({ 'taskName': o['task'], 'taskView': 'prioritised' })

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'], personID)

    # Get a clientID either passed via command line, or figured out from a client name
    if self.is_num(o['client']):
      clientID = o['client']
    elif o['client']:
      clientID = self.search_for_client(o['client'])

    # Get a timesheetID passed via command line
    if self.is_num(o['timesheet']):
      timeSheetID = o['timesheet']

    # Print out entity
    try:

      if taskID:
        entity = 'task'
        entityID = taskID
        k_, v = self.get_list(entity, { entity+'ID' : entityID, 'taskView' : 'prioritised' }).popitem()
        self.msg(v['taskTypeID']+': '+v['taskID']+' '+v['taskName'])
      elif projectID:
        entity = 'project'
        entityID = projectID
        k_, v = self.get_list(entity, { entity+'ID' : entityID }).popitem()
        self.msg(v['projectType']+': '+v['projectID']+' '+v['projectName'])
      elif timeSheetID:
        entity = 'timeSheet'
        entityID = timeSheetID
        k_, v = self.get_list(entity, { entity+'ID' : entityID }).popitem()
        self.msg('Time Sheet: '+v['timeSheetID']+' '+v['projectName'] +' '+ v['amount'])
      elif clientID:
        entity = 'client'
        entityID = clientID
        k_, v = self.get_list(entity, { entity+'ID' : entityID }).popitem()
        self.msg('Client: '+v['clientID']+' '+v['clientName'])

    except:
      self.die("No "+entity+" found with ID "+entityID)


    # Assume 'default' if there's no --to
    if not o['to']:
      o['to'] = ['default']

    # Sort out recipients
    nobody = False
    comma = ""
    for p in o['to']:
      if p.lower() == 'nobody':
        nobody = True
        o['to'].remove('nobody')

    # We only append current user if they haven't specified 'nobody'
    if not nobody:
      o['to'].append(self.username)

    # Magic
    if entity and entityID:
      recipients = self.get_people(o['to'], entity, entityID)

    # Print recipients
    comma = ""
    text = ""
    if recipients:
      for k_, info in recipients.items():
        text += comma + (info.get('name') or '')+ " <"+(info.get('emailAddress') or '')+">"
        comma = ", "
    self.msg("To: " + text)
  
    if not entity and not entityID:
      self.die("Exiting. No entity specified (eg --task ID).")

    # Grab stdin
    try:
      comment_text = sys.stdin.read()
    except (IOError):
      self.die("Exiting. No message specified.")
    except (KeyboardInterrupt):
      self.die("Exiting. No message specified.")


    # Send message
    if comment_text and entity and entityID:
      package = {}
      package['ip'] = recipients
      package['comment_text'] = comment_text
      package['entity'] = entity
      package['entityID'] = entityID

      args = {}
      args['entity'] = 'comment'
      args['id'] = 'new'
      args['options'] = package
      args['method'] = 'edit_entity'
      self.make_request(args)
    else:
      self.die("Exiting. No message specified.")

