"""alloccli subcommand for viewing a list of projects."""
from alloc import alloc

class projects(alloc):
  """Print a list of projects."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('p:', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('f:', 'fields=LIST    ', 'The list of fields you would like printed.\n'
                                       '(eg: all eg: projectID projectName)')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += "\n\n%s\n\nIf called without arguments this program will display all of your projects."


  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    #self.quiet = o['quiet']
    personID = self.get_my_personID()

    # Get a projectID either passed via command line, or figured out from a project name
    f = {}
    if o['project']: f['projectNameMatches'] = o['project']
    f["personID"] = personID
    f["projectStatus"] = "Current"
    fields = o["fields"] or ["projectID","projectName"]
    self.print_table("project", self.get_list("project", f), fields, sort="projectName")

