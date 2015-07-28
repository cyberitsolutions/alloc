"""alloccli subcommand for TF accounts."""
from alloc import alloc


class accounts(alloc):

    """Print a list of your TF accounts."""

    # Setup the options that this cli can accept
    ops = []
    ops.append(('', 'help           ', 'Show this help.'))
    ops.append(('', 'csv=[WHEN]     ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                         '"never" or "always". If WHEN is omitted, assume "always".'))
    ops.append(('i', 'items          ', 'Show accounts\' transactions.'))
    ops.append(('a:', 'account=TF     ', 'Show a particular TF. Default to your TFs.'))
    ops.append(('f:', 'field=NAME     ', 'A field you would like printed.\n'
                                         '(eg: -f all eg: -f tfID -f tfName -f tfBalance)'))
    ops.append(('', 'possible-fields', 'List of possible fields.'))

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

        if o['possible-fields']:
            alloc().possible_fields("transaction")

        # Initialize some variables
        ops = {}

        if 'account' in o and o['account']:
            ops['tfIDs'] = self.make_request(
                {'method': 'get_tfID', 'options': o['account']})

        # Get transactions
        if 'items' in o and o['items']:
            if o['field']:
                fields = o['field']
            else:
                fields = ["transactionID", "fromTfName", "tfName",
                          "amount", "status", "transactionDate"]

            transactions = self.get_list("transaction", ops)
            if transactions:
                self.print_table(
                    "transaction", transactions, fields, "transactionDate")
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
