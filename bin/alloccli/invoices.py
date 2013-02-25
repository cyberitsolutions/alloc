"""alloccli subcommand for viewing a list of invoices."""
from alloc import alloc

class invoices(alloc):
  """Print a list of invoices."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append((''  , 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                       '"never" or "always". If WHEN is omitted, assume "always".'))
  ops.append(('i' , 'items          ', 'Show the invoice\'s items.'))
  ops.append(('c:', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client name.'))
  ops.append(('p:', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('n:', 'num=ID         ', 'An invoice\'s number.'))
  ops.append((''  , 'from=DATE      ', 'From this start date.'))
  ops.append((''  , 'to=DATE        ', 'To this end date.'))
  ops.append(('s:', 'status=STATUS  ', 'The invoice\'s status eg: edit finished reconcile'))
  ops.append(('f:', 'fields=LIST    ', 'The list of fields you would like printed.\n'
                                       '(eg: all eg: clientID clientName)')) 

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text += __doc__
  help_text += "\n\n%s\n\nIf called without arguments this program will display the list of invoices."


  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    # Initialize some variables
    personID = self.get_my_personID()

    # Get a clientID either passed via command line, or figured out from a project name
    f = {}
    if self.is_num(o['client']):
      f["clientID"] = o['client']
    elif o['client']:
      f["clientID"] = self.search_for_client({"clientName":o['client']})

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      f["projectID"] = o['project']
    elif o['project']:
      f["projectID"] = self.search_for_project(o['project'], personID)

    #f["personID"] = personID
    f['return'] = "array"

    if o['num']:
      f['invoiceNum'] = o['num']
    if o['from']:
      f['dateOne'] = o['from']
    if o['to']:
      f['dateTwo'] = o['to']
    if o['status']:
      f['invoiceStatus'] = o['status']


    if o['items']:
      invoiceIDs = []
      invoices_list = self.get_list("invoice", f)
      if invoices_list:
        for i in invoices_list:
          invoiceIDs.append(i)

        fields = o["fields"] or ["invoiceID", "invoiceItemID", "clientID", "clientName", "invoiceNum",
                                 "iiDate", "iiAmount", "iiQuantity", "iiUnitPrice", "iiMemo"]
        self.print_table("invoiceItem", self.get_list("invoiceItem", {"invoiceID":invoiceIDs}),
                         fields, sort="invoiceID")
    
    else:
      fields = o["fields"] or ["invoiceID", "clientID", "clientName", "invoiceNum", "invoiceDateFrom",
                               "invoiceDateTo", "invoiceStatus", "status_label", "amountPaidRejected",
                               "amountPaidPending", "amountPaidApproved", "iiAmountSum"]

      self.print_table("invoice", self.get_list("invoice", f), fields, sort="clientName")
      


