"""alloccli subcommand for editing alloc entities."""
from alloc import alloc

class edit(alloc):
  """Modify an alloc entity."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('t:', 'task=ID|new    ', 'An existing task\'s ID or the word "new" to create a new task.'))
  ops.append(('i:', 'item=ID|new    ', 'An existing time sheet item\'s ID.'))
  ops.append(('p:', 'project=ID     ', '[NOT OPERATIONAL] An existing project\'s ID.'))
  ops.append(('s:', 'timesheet=ID   ', '[NOT OPERATIONAL] An existing time sheet\'s ID.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

This program allows editing of the fields on an alloc entity, like a task.

Examples:

# Display all the different fields that can be edited on a task
alloc edit --task help

# Edit a particular task. Each field name must be prefixed with a caret character: ^
# which unfortunately means that carets can't be used anywhere else for this command.
alloc edit --task 1234 ^status:closed ^name:New name for the task. ^assign:alla

# Create a new task
alloc edit --task new ^name:This task is fooed in the bar 

# Note that the ^field:values must be the final arguments. I.e. this WON'T work:
alloc edit ^name:nope --task 1234  <-- NO
# It should be this:
alloc edit --task 1234 ^name:yep   <-- YES


# Display all the different fields that can be edited on a time sheet item
alloc edit --item help

# Edit an existing time sheet item.
alloc edit -i 1234 ^duration:3.5 ^date:2011-07-24 ^comment:hey ^private:1 ^task:15180

# Create a new time sheet item. Note that ^tsid is mandatory in this case
alloc edit -i new ^tsid:7941 ^duration:3.5 ^date:2011-07-24 ^comment:hey ^task:15180"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    args = {}
    if o['project']:
      args['entity'] = 'project'
      args['id'] = o['project']

    elif o['task']:
      args['entity'] = 'task'
      args['id'] = o['task']

    elif o['timesheet']:
      args['entity'] = 'timesheet'
      args['id'] = o['timesheet']

    elif o['item']:
      args['entity'] = 'item'
      args['id'] = o['item']
  
    package = {}

    # parse ^field:value into a dict {"field":"value"}
    bits = remainder.split("^")
    for bit in bits:
      if bit:
        chunks = bit.split(":")
        key = chunks[0].strip()
        val = ":".join(chunks[1:]).strip()
        package[key] = val

    args['options'] = package
    args['method'] = 'edit_entity'
    rtn = self.make_request(args)

    # If server returns a message, print it out
    if rtn and 'status' in rtn and 'message' in rtn:
      meth = getattr(self, rtn['status'])
      meth(rtn['message'])


