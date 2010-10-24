import sys
from alloc import alloc

class subscriptions(alloc):

  one_line_help = "Modify interested party subscriptions for an email address."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append((''  ,'csv            ','Return the results in CSV format.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('n' ,'dryrun         ','Perform a dry run, no data gets updated.'))
  ops.append(('e:','email=EMAIL    ','Output subscriptions with this email address. Use % for all.'))
  ops.append(('a' ,'add            ','Add the following subscriptions from stdin.'))
  ops.append(('d' ,'del            ','Delete the following subscriptions from stdin.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS] [FILE]\n"
  help_text+= one_line_help
  help_text+= """\n\n%s

Examples:
alloc subscriptions --email example@example.com 
alloc subscriptions --email example@example.com --csv > foo.txt
alloc subscriptions --del < foo.txt
alloc subscriptions --add < foo.txt"""

  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    self.csv = o['csv']
    self.quiet = o['quiet']
    self.dryrun = o['dryrun']
    personID = self.get_my_personID()

    # This is the data format that is exported and imported
    fields = ["entity","Entity","entityID","ID","personID","Person ID","emailAddress","Email","fullName","Name"]
    keys = fields[::2]
    ops = {}

    if o['email']:
      ops['emailAddress'] = o['email']
      parties = self.get_list("interestedParty",ops)
      self.print_table(parties,fields)

    elif o['add'] or o['del']:
      lines = sys.stdin.readlines()
      for line in lines:
        f = line[:-1].split(",")
        party = {}
        party[keys[0]] = f[0]
        party[keys[1]] = f[1]
        party[keys[2]] = f[2]
        party[keys[3]] = f[3]
        party[keys[4]] = f[4]
        if o['add']: 
          if not o['dryrun']: self.make_request({"method":"save_interestedParty","options":party})
          self.msg("Adding:"+str(party))
        elif o['del']: 
          if not o['dryrun']: self.make_request({"method":"delete_interestedParty","options":party})
          self.msg("Deleting:"+str(party))



