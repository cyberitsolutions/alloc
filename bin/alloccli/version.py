"""subcommand for viewing the cli and server versions."""
from alloc import alloc
import sys
import os

class version(alloc):
  """View the version of the cli and server."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('u.', 'url=URL        ', 'The alloc-server\'s URL'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += """\n\n%s

This program allows you to view the version number of the alloc-cli and alloc-server.
You can use the --url option to override the default alloc-server.

# Use default alloc-server
alloc version

# Check the version of another alloc-server
alloc version --url http://alloc.example.com/services/json.php"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # No authentication necessary
    #self.authenticate();

    if o['url']:
      self.url = o['url']

    rtn = self.make_request({"get_server_version":True})

    self.msg("alloc-cli:    "+self.client_version+" "+os.path.abspath(sys.argv[0]))
    self.msg("alloc-server: "+rtn['version']+" "+self.url)


