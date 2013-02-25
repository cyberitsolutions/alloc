"""alloccli subcommand for TF accounts."""
from alloc import alloc

class accounts(alloc):
  """Print a list of your TF accounts."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('i' , 'items          ', 'Show accounts\' transactions.'))
  ops.append(('a:', 'account=TF     ', 'Show a particular TF. Default to your TFs.'))
  #ops.append(('p:', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  #ops.append(('s:', 'status=STATUS  ', 'The transactions\' status. Can accept multiple values.\n'
                                        #'(eg: "pending,approved,rejected" Default: approved)'))
  #ops.append(('t:', 'time=ID        ', 'A time sheet ID.'))
  #ops.append(('d:', 'date=YYYY-MM-DD', 'The from date of the earliest transaction.'))
  #ops.append(('o:', 'order=NAME     ', 'The order the accounts or transactions are displayed in.\n'
                                        #'Default for accounts: "???" Default for transactions: "???"'))
  ops.append(('f:', 'field=NAME     ', 'A field you would like printed.\n'
                                       '(eg: -f all eg: -f tfID -f tfName -f tfBalance)')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += '''\n\n%s'''

  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    #self.quiet = o['quiet']
    ops = {}
    
    if 'account' in o and o['account']:
      ops['tfIDs'] = self.make_request({'method':'get_tfID', 'options':o['account']})

    # Get transactions
    if 'items' in o and o['items']:
      if o['field']:
        fields = o['field']
      else:
        fields = ["transactionID", "fromTfName", "tfName", "amount", "status", "transactionDate"]

      transactions = self.get_list("transaction", ops)
      if transactions:
        self.print_table("transaction", transactions, fields, "transactionDate")
        print "num rows:", len(transactions)
 
    # Get tf
    else:
      if o['field']:
        fields = o['field']
      else:
        fields = ["tfID", "tfName", "tfBalancePending", "tfBalance"]

      tfs = self.get_list("tf", ops)
      if tfs:
        self.print_table("tf", tfs, fields, "tfName")


   
