"""alloccli subcommand for viewing a list of reminders."""
from alloc import alloc


class reminders(alloc):

    """Print a list of reminders."""

    # Setup the options that this cli can accept
    ops = []
    ops.append(('', 'help             ', 'Show this help.'))
    ops.append(('', 'csv=[WHEN]       ', 'Return the results in CSV format. WHEN can be "auto",\n'
                                         '"never" or "always". If WHEN is omitted, assume "always".'))
    ops.append(('r.', 'reminder=ID    ', 'Reminder ID.'))
    ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
    ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
    ops.append(('c.', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client name.'))
    ops.append(('a.', 'active=1|0     ', 'Reminder active or not.'))

    # Specify some header and footer text for the help text
    help_text = 'Usage: %s [OPTIONS]\n'
    help_text += __doc__
    help_text += '''\n\n%s

This program displays a list of reminders.

Examples:

alloc reminders -t 1234'''

    def run(self, commands):
        # Print a list of reminders
        o, remainder_ = self.get_args(commands, self.ops, self.help_text)
        self.authenticate()

        # Viewing reminders
        options = {}
        options['fields'] = ['reminderID', 'reminderSubject', 'link', 'frequency']
        options['filter_reminderActive'] = False if o['active'] == 0 else True

        if o['task']:
            options['type'] = 'task'
            options['id'] = o['task']
        elif o['client']:
            options['type'] = 'client'
            options['id'] = o['client']
        elif o['project']:
            options['type'] = 'project'
            options['project'] = o['project']

        if o['reminder']:
            options['reminderID'] = o['reminder']

        rows = self.get_list("reminder", options)

        # Compact the type field and the frequency
        for k_, row in rows.items():
            row['link'] = ''
            if row['reminderType']:
                row['link'] = "%s %s" % (
                    row['reminderType'].capitalize(), row['reminderLinkID'])

            row['frequency'] = ''
            if int(row['reminderRecuringValue']):
                row['frequency'] = "%s %ss" % (
                    row['reminderRecuringValue'], row['reminderRecuringInterval'])

        self.print_table("reminder", rows, options['fields'])
