"""alloccli subcommand for billing time for work done."""
from alloc import alloc
import sys
import re
import tempfile
import os
import subprocess

class reminders(alloc):
    """Create and manipulate reminders."""

    # Setup the options that this cli can accept
    ops = []
    ops.append((''  , 'help           ', 'Show this help.'))
    ops.append(('R.', 'reminder=ID    ', 'Reminder ID or "new".'))
    ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
    ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
    ops.append(('C.', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client name.'))
    ops.append(('c.', 'comment=COMMENT', 'The text of the reminder.'))
    ops.append(('n.', 'name=NAME      ', 'The title of the reminder.'))
    ops.append(('f.', 'frequency=FREQ ', 'How often this reminder is to recur.\n'
                                         'Specify as [number][unit], where unit is one of\n'
                                         '[h]our, [d]ay, [w]eek, [m]onth, [y]ear.'))
    ops.append(('N.', 'notice=WARNING ', 'Advance warning for this reminder. Same format as frequency.'))
    ops.append(('d.', 'date=DATE      ', 'When this reminder is to trigger.'))
    ops.append(('a.', 'active=1|0     ', 'Whether this reminder is active or not.'))
    ops.append(('e' , 'edit           ', 'Spawn EDITOR to write comment.'))
    ops.append(('r:', 'to=PEOPLE      ', 'Recipients. Can be usernames, full names and/or email.'))
    ops.append(('D:', 'remove=PEOPLE  ', 'Recipients to remove.'))
    ops.append((''  , 'reopen         ', 'Reopen the task when this reminder triggers.'))

    # Specify some header and footer text for the help text
    help_text = "Usage: %s [OPTIONS]\n"
    help_text += __doc__
    help_text += "\n\n%s\n\nCreate and edit reminders."

    def run(self, commands):
        op, _ = self.get_args(commands, self.ops, self.help_text)
        self.authenticate()

        args = {}

        # hour day week month year
        if op['frequency'] and not re.match(r'\d+[hdwmy]', op['frequency'], re.IGNORECASE):
            self.die("Invalid frequency specification")
        if op['notice'] and not re.match(r'\d+[hdwmy]', op['notice'], re.IGNORECASE):
            self.die("Invalid advance notice specification")

        if op['reminder']:
            args['method'] = 'edit_reminder'
            args['id'] = op['reminder']
            options = {}

            if op['task'] and not op['task'].isdigit():
                op['task'] = self.search_for_task({'taskName': op['task']})
            elif op['project'] and not op['project'].isdigit():
                op['project'] = self.search_for_project(op['project'])
            elif op['client'] and not op['client'].isdigit():
                op['client'] = self.search_for_client({'clientName': op['client']})

            options.update(op)

            if options['to']:
                options['recipients'] = [x['personID'] for x in self.get_people(options['to']).values()]
            if options['remove']:
                options['recipients_remove'] = [x['personID'] for x in self.get_people(options['remove']).values()]

            if op['reminder'] == 'new' and not op['comment']:
                if not op['edit']:
                    # read from stdin, same as comment
                    print("Enter reminder text:")
                    options['comment'] = sys.stdin.read()
                else:
                    # spawn EDITOR for user to play with
                    editor = os.environ.get('EDITOR', 'vim')
                    message = "# Enter your message\n# Lines starting with '#' will be ignored"
                    with tempfile.NamedTemporaryFile() as f:
                        f.write(message)
                        f.flush()
                        subprocess.call([editor, f.name])
                        f.seek(0)
                        comment = f.read()
                        options['comment'] = re.sub('^#.*$', '', comment, flags = re.MULTILINE)
                        if re.match('^\s*$', options['comment']):
                            self.die("No comment entered. Aborting.")
            elif op['reminder'] != 'new' and op['edit']:
                comment_rq = {'method': 'get_reminder'}
                comment_rq['id'] = op['reminder']
                comment = self.make_request(comment_rq)
                with tempfile.NamedTemporaryFile() as f:
                    editor = os.environ.get('EDITOR', 'vim')
                    f.write(comment)
                    f.flush()
                    subprocess.call([editor, f.name])
                    f.seek(0)
                    new_comment = f.read()
                    if comment == new_comment:
                        self.die("Comment not changed. Aborting.")
                    options['comment'] = new_comment

            args['options'] = options
            print self.make_request(args)
            return
        else:
            # Viewing reminders
            options = {'fields': ['reminderID', 'reminderSubject',  'link', 'frequency']}
            options['filter_reminderActive'] = False if op['active'] == 'false' else True

            if op['task']:
                options['type'] = 'task'
                options['id'] = op['task']
            elif op['client']:
                options['type'] = 'client'
                options['id'] = op['client']
            elif op['project']:
                options['type'] = 'project'
                options['project'] = op['project']

            rows = self.get_list("reminder", options)

            # Compact the recurrency display
            # And the link display
            for k, row in rows.items():
                row['link'] = ''
                if row['reminderType']:
                    row['link'] = "%s %s" % (row['reminderType'].capitalize(), row['reminderLinkID'])

                row['frequency'] = ''
                if int(row['reminderRecuringValue']):
                    row['frequency'] = "%s %ss" % (row['reminderRecuringValue'], row['reminderRecuringInterval'])
            self.print_table("reminder", rows, options['fields'])

