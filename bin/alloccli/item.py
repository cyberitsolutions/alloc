"""alloccli subcommand for editing alloc time sheet items."""
from alloc import alloc

class item(alloc):
  """Add or edit a time sheet item."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('q' , 'quiet          ', 'Run with less output.\n'))

  ops.append(('i.', '               ', 'Edit a time sheet item. Specify an ID or omit -i to create.'))
  ops.append((''  , 'tsid=ID        ', 'time sheet that this item belongs to'))
  ops.append((''  , 'date=DATE      ', 'time sheet item date'))
  ops.append((''  , 'duration=HOURS ', 'time sheet item duration'))
  ops.append((''  , 'unit=NUM       ', 'time sheet item unit of duration eg: 1=hours 2=days 3=week 4=month 5=fixed'))
  ops.append((''  , 'task=ID        ', 'ID of the time sheet item\'s task'))
  ops.append((''  , 'rate=NUM       ', '$rate of the time sheet item'))
  ops.append((''  , 'private=1|0    ', 'privacy setting of the time sheet item\'s comment eg: 1=private 0=normal'))
  ops.append((''  , 'comment=TEXT   ', 'time sheet item comment'))
  ops.append((''  , 'multiplier=NUM ', 'time sheet item multiplier eg: 1=standard 1.5=time-and-a-half 2=double-time\n'
                                       '3=triple-time 0=no-charge'))
  ops.append((''  , 'delete=1       ', 'set this to 1 to delete the time sheet item\n'))
  
  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

This program allows editing of the fields on a time sheet item.

Examples:

# Omit -i to create a new time sheet item. Note that tsid is mandatory.
alloc item --tsid 7941 --duration 3.5 --date 2011-07-24 --comment hey --task 15180"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    args = {}

    if not o['i']:
      o['i'] = 'new'

    args['entity'] = 'item'
    args['id'] = o['i']

    if o['date']:
      o['date'] = self.parse_date(o['date'])

    if o['task'] and not self.is_num(o['task']):
      o['task'] = self.search_for_task({'taskName': o['task']})

    package = {}
    for key, val in o.items():
      if val:
        package[key] = val
      if type(val)==type("") and val.lower() == 'null':
        package[key] = ''

    package['command'] = 'edit_timeSheetItem'
    args['options'] = package
    args['method'] = 'edit_entity'
    rtn = self.make_request(args)
    self.handle_server_response(rtn, not o['quiet'])
