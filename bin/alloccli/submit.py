"""alloccli subcommand for submitting time sheets."""
import sys
from alloc import alloc

class submit(alloc):
  """Submit time sheets forwards. Read time sheets from standard in."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  #ops.append(('v', 'verbose        ', 'Run with more output.'))
  ops.append(('n' , 'dryrun         ', 'Perform a dry run, no data gets updated.'))
  ops.append(('q' , 'quiet          ', 'Run with no output except errors.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += '''\n\n%s

This program enables you to submit your time sheets to managers, admins etc.
The time sheet is moved from eg: Edit to Manager status. The time sheet may 
no longer be editable once you have submitted it.
  
Examples:
alloc timesheets | alloc submit --dryrun
alloc timesheets | alloc submit
alloc timesheets --status edit --hours ">=7" --date "1 week ago" | alloc submit'''

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    self.quiet = o['quiet']
    self.dryrun = o['dryrun']

    # Read entries from stdin
    lines = sys.stdin.readlines()
    for line in lines:
      f = line[:-1].split(",")
      timeSheetID = f[0]
      if not o['dryrun']: 
        self.msg("Attempting to submit time sheet: "+timeSheetID)
        self.make_request({"method":"change_timeSheet_status", "timeSheetID":timeSheetID, "direction":"forwards"})
      else:
        self.msg("Dry-run, not attempting to submit time sheet: "+timeSheetID)

