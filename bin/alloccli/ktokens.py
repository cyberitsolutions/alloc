"""alloccli subcommand for viewing a list of tokens."""
from alloc import alloc

class ktokens(alloc):
  """Retrieve token information."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('q' , 'quiet          ', 'Run with no output except errors.'))
  ops.append(('k:', 'key=KEY        ', 'An 8 character email subject line key.'))
  ops.append(('i:', 'id=ID          ', 'A comment id.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS] [FILE]\n"
  help_text += __doc__
  help_text += """\n\n%s

Examples:

# Print out a list of tokens
alloc token --key 1234abcd"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    self.quiet = o['quiet']

    # This is the data format that is exported and imported
    fields = ["tokenEntity", "Entity", "tokenEntityID", "ID", "tokenHash", "Key"]
    searchops = {}
    searchops['tokenHash'] = o['key'] or ''
    searchops['tokenEntity'] = 'comment'
    searchops['tokenEntityID'] = o['id'] or ''

    if o['key'] or o['id']:
      tokens = self.get_list("token", searchops)
      if tokens:
        self.print_table("token", tokens, fields)


