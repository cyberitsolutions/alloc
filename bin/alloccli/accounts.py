from alloc import alloc
from sys import stdout

class accounts(alloc):

  one_line_help = "Print a list of your TF accounts."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  #ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  #ops.append(('i' ,'items          ','Show accounts\' transactions.'))
  #ops.append(('p:','project=ID|NAME','A project ID, or a fuzzy match for a project name.'))
  #ops.append(('s:','status=STATUS  ','The transactions\' status. Can accept multiple values, eg: "pending,approved,rejected" Default: approved'))
  #ops.append(('t:','time=ID        ','A time sheet ID.'))
  #ops.append(('d:','date=YYYY-MM-DD','The from date of the earliest transaction.'))
  #ops.append(('o:','order=NAME     ','The order the accounts or transactions are displayed in. Default for accounts: "???" Default for transactions: "???"'))
  ops.append(('f:','fields=LIST    ','The commar separated list of fields you would like printed, eg: "all" eg: "tfID,tfName,tfBalance"')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= '''\n\n%s'''

  def run(self, command_list):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate();

    # Initialize some variables
    #self.quiet = o['quiet']
    self.csv = not stdout.isatty()
    order = 'Name'
    ops = {'owner':True}
    tfs = self.get_list("tf",ops)

    if o['fields']:
      fields = o['fields']
    else:
      fields = ['tfID','ID'
               ,'tfName','Name'
               ,'tfBalancePending','Pending'
               ,'tfBalance','Approved']

    if tfs:
      self.print_table(tfs, fields, order)
    
