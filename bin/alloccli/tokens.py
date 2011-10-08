import sys
from alloc import alloc
from sys import stdout

class tokens(alloc):

  one_line_help = "Retrieve alloc token information."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('k:','key=KEY        ','An 8 character email subject line key.'))
  ops.append(('i:','id=ID          ','A comment id.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS] [FILE]\n"
  help_text+= one_line_help
  help_text+= """\n\n%s

Examples:

# Print out a list of tokens
alloc token --key 1234abcd"""

  def run(self, command_list):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    self.quiet = o['quiet']
    personID = self.get_my_personID()

    # This is the data format that is exported and imported
    fields = ["tokenEntity","Entity","tokenEntityID","ID","tokenHash","Key"]
    searchops = {}
    searchops['tokenHash'] = o['key'] or ''
    searchops['tokenEntity'] = 'comment'
    searchops['tokenEntityID'] = o['id'] or ''

    if o['key'] or o['id']:
      tokens = self.get_list("token",searchops)
      if tokens:
        self.print_table("token",tokens,fields)


