"""alloccli subcommand for viewing the details of a single entity."""
from alloc import alloc

class view(alloc):
  """View an entity."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('t:', 'task=ID        ', 'An existing task\'s ID.'))
  #ops.append(('i:', 'item=ID        ', 'An existing time sheet item\'s ID.'))
  #ops.append(('p:', 'project=ID     ', 'An existing project\'s ID.'))
  #ops.append(('s:', 'timesheet=ID   ', 'An existing time sheet\'s ID.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

This program allows you to view a single alloc entity, like a task.

Examples:

# Display all the different fields on a task
alloc view --task 1234"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    if o['task']:
      print self.print_task(o['task']) 

    # elif o['timesheet']:
    #   pass

    # elif o['item']:
    #   pass

    # elif o['project']:
    #   pass
  
