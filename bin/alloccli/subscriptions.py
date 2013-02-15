"""alloccli subcommand for viewing a list of comment subscriptions."""
import sys
from alloc import alloc

class subscriptions(alloc):
  """Modify interested party subscriptions for an email address."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('q' , 'quiet          ', 'Run with no output except errors.'))
  ops.append(('n' , 'dryrun         ', 'Perform a dry run, no data gets updated.'))
  ops.append(('k.', 'key=KEY        ', 'An 8 character email subject line key.'))
  ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
  ops.append(('e.', 'email=EMAIL    ', 'The email address. Any part of "Full Name \n'
                                       '<email@address.com>". Use % for wildcard.'))
  ops.append(('a' , 'add            ', 'Add the following subscriptions from stdin.'))
  ops.append(('d' , 'del            ', 'Delete the following subscriptions from stdin.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS] [FILE]\n"
  help_text += __doc__
  help_text += """\n\n%s

Examples:

# Print out a list of interested parties using different search criteria:
alloc subscriptions --key 1234abcd
alloc subscriptions --task 321
alloc subscriptions --email example@example.com 

# Output the list in CSV format to a file
alloc subscriptions --email example@example.com --key 1234abcd > foo.txt

# That CSV file can be manually edited, then read back in to add or delete interested parties.
# Note the same file can be read in multiple times, it won't create duplicate records.
alloc subscriptions --del < foo.txt
alloc subscriptions --add < foo.txt"""

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    self.quiet = o['quiet']
    self.dryrun = o['dryrun']

    # This is the data format that is exported and imported
    fields = ["entity", "Entity", "entityID", "ID", "personID",
              "Person ID", "emailAddress", "Email", "name", "Name", "interestedPartyActive", "Active"]
    keys = fields[::2]
    searchops = {}

    # If we're looking for interested parties and we have a {key:something}
    if o['key']:
      tokens = self.get_list("token", { "tokenHash" : o['key'] })
      if tokens:
        k_, v = tokens.popitem()
        searchops['entity'] = v['tokenEntity']
        searchops['entityID'] = v['tokenEntityID']
      else:
        self.die("No key found: "+o['key'])

    # Get email and parse it into fullName and emailAddress
    if o['email']:
      searchops['emailAddress'], searchops['fullName'] = self.parse_email(o['email'])


    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      searchops['taskID'] = o['task']
    elif o['task']:
      searchops['taskID'] = self.search_for_task({"taskName":o["task"]})


    # Look for the interested parties, using the criteria from above
    if not o['add'] and not o['del']:
      fields[fields.index('name')] = 'fullName' # hack
      parties = self.get_list("interestedParty", searchops)
      self.print_table("interestedParty", parties, fields)

    # Else if we're adding or deleting
    else:

      lines = sys.stdin.readlines()
      for line in lines:
        f = line[:-1].split(",")
        party = {}
        if len(f) > 0: party[keys[0]] = f[0]
        if len(f) > 1: party[keys[1]] = f[1]
        if len(f) > 2: party[keys[2]] = f[2]
        if len(f) > 3: party[keys[3]] = f[3]
        if len(f) > 4: party[keys[4]] = f[4]
        if o['add'] and 'emailAddress' in party and party['emailAddress']: 
          if not o['dryrun']: self.make_request({"method":"save_interestedParty", "options":party})
          self.msg("Adding:"+str(party))
        elif o['del'] and 'emailAddress' in party and party['emailAddress']: 
          if not o['dryrun']: self.make_request({"method":"delete_interestedParty", "options":party})
          self.msg("Deleting:"+str(party))

