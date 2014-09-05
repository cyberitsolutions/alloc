"""alloccli subcommand for editing alloc reminders."""
from alloc import alloc
import re

class reminder(alloc):
  """Add or edit a reminder."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('q' , 'quiet          ', 'Run with less output.\n'))

  ops.append(('r.', '               ', 'Edit a reminder. Specify an ID or omit -r to create.'))
  ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
  ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('c.', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client name.'))

  ops.append(('s.', 'subject=TEXT   ', 'The subject line of the reminder.'))
  ops.append(('b.', 'body=TEXT      ', 'The text body of the reminder.'))
  ops.append((''  , 'frequency=FREQ ', 'How often this reminder is to recur.\n'
                                       'Specify as [number][unit], where unit is one of\n'
                                       '[h]our, [d]ay, [w]eek, [m]onth, [y]ear.'))
  ops.append((''  , 'notice=WARNING ', 'Advance warning for this reminder. Same format as frequency.'))
  ops.append(('d.', 'date=DATE      ', 'When this reminder is to trigger.'))
  ops.append((''  , 'active=1|0     ', 'Whether this reminder is active or not.'))
  ops.append(('T:', 'to=PEOPLE      ', 'Recipients. Can be usernames, full names and/or email.'))
  ops.append(('D:', 'remove=PEOPLE  ', 'Recipients to remove.'))


  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

This program allows editing of the fields on a reminder.

Examples:

# Edit a particular reminder.
alloc reminder -r 1234 --title 'Name for the reminder.' --to alla

# Omit -r to create a new reminder
alloc reminder --title 'Name for the reminder.' --to alla"""


  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()
    personID = self.get_my_personID()

    args = {}
    if not o['r']:
      o['r'] = 'new'

    args['entity'] = 'reminder'
    args['id'] = o['r']

    if o['date']:
      o['date'] = self.parse_date(o['date'])

    if o['project'] and not self.is_num(o['project']):
      o['project'] = self.search_for_project(o['project'], personID)

    if o['task'] and not self.is_num(o['task']):
      o['task'] = self.search_for_task({'taskName': o['task']})

    if o['client'] and not self.is_num(o['client']):
      o['client'] = self.search_for_client({'clientName': o['client']})

    if o['frequency'] and not re.match(r'\d+[hdwmy]', o['frequency'], re.IGNORECASE):
      self.die("Invalid frequency specification")
    if o['notice'] and not re.match(r'\d+[hdwmy]', o['notice'], re.IGNORECASE):
      self.die("Invalid advance notice specification")

    if o['to']:
      o['recipients'] = [x['personID'] for x in self.get_people(o['to']).values()]
    if o['remove']:
      o['recipients_remove'] = [x['personID'] for x in self.get_people(o['remove']).values()]


    package = {}
    for key, val in o.items():
      if val:
        package[key] = val
      if type(val)==type("") and val.lower() == 'null':
        package[key] = ''

    package['command'] = 'edit_reminder'
    args['options'] = package
    args['method'] = 'edit_entity'
    rtn = self.make_request(args)
    self.handle_server_response(rtn, not o['quiet'])
