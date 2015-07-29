"""alloccli subcommand for editing alloc tasks."""
from alloc import alloc


class task(alloc):

    """Add or edit a task."""

    # Setup the options that this cli can accept
    ops = []
    ops.append(('', 'help           ', 'Show this help.'))
    ops.append(('q', 'quiet         ', 'Run with less output.\n'))
    ops.append(('t.', '             ', 'Edit a task. Specify an ID or omit -t to create.'))
    ops.append(('', 'name=TEXT      ', 'task\'s title'))
    ops.append(('', 'desc=TEXT      ', 'task\'s long description'))
    ops.append(('', 'assign=USERNAME', 'username of the person that the task is assigned to'))
    ops.append(('', 'manage=USERNAME', 'username of the person that the task is managed by'))
    ops.append(('', 'dip=TEXT       ', 'default interested parties, comma separated usernames/emails/full names'))
    ops.append(('', 'tags=TEXT      ', 'comma separated task tags'))
    ops.append(('', 'priority=PRI   ', '1, 2, 3, 4 or 5; or one of Wishlist, Minor, Normal, Important or Critical'))
    ops.append(('', 'limit=HOURS    ', 'limit in hours for effort spend on this task'))
    ops.append(('', 'best=HOURS     ', 'shortest estimate of how many hours of effort this task will take'))
    ops.append(('', 'likely=HOURS   ', 'most likely amount of hours of effort this task will take'))
    ops.append(('', 'worst=HOURS    ', 'longest estimate of how many hours of effort this task will take'))
    ops.append(('', 'estimator=USERNAME', 'the person who created the estimates on this task'))
    ops.append(('', 'targetstart=DATE', 'estimated date for work to start on this task'))
    ops.append(('', 'targetcompletion=DATE', 'estimated date for when this task should be finished'))
    ops.append(('', 'project=ID|NAME', 'task\'s project ID, or a fuzzy match for a project name.'))
    ops.append(('', 'type=TYPE      ', 'Task, Fault, Message, Milestone or Parent'))
    ops.append(('', 'dupe=ID        ', 'task ID of the related dupe'))
    ops.append(('', 'pend=IDS       ', 'task ID(s), comma separated, that block this task.'))
    ops.append(('', 'reopen=DATE    ', 'Reopen the task on this date. To be used with --status=pending.'))
    ops.append(('', 'status=STATUS  ', 'inprogress, notstarted, info, client, manager, invalid, duplicate,\n'
                                       'incomplete, complete; or: open, pending, closed\n'))

    # Specify some header and footer text for the help text
    help_text = "Usage: %s [OPTIONS]\n"
    help_text += __doc__
    help_text += """\n\n%s

This program allows editing of the fields on a task.

Examples:

# Edit a particular task.
alloc task -t 1234 --status closed --name 'New name for the task.' --assign alla

# Omit -t to create a new task.
alloc task --name 'This task is fooed in the bar' --project 22

# Use null to unset
alloc task -t 1234 --assignee null"""

    def run(self, command_list):

        """Execute subcommand."""

        # Get the command line arguments into a dictionary
        o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

        # Got this far, then authenticate
        self.authenticate()
        personID = self.get_my_personID()

        args = {}
        if not o['t']:
            o['t'] = 'new'

        args['entity'] = 'task'
        args['id'] = o['t']
        if o['t'] == "new":
            if not o['type']:
                o['type'] = "Task"
            if not o['priority']:
                o['priority'] = "Normal"

        if o['project'] and not self.is_num(o['project']):
            o['project'] = self.search_for_project(o['project'], personID)

        package = {}
        for key, val in o.items():
            if val:
                package[key] = val
            if isinstance(val, str) and val.lower() == 'null':
                package[key] = ''

        if 'reopen' in package:
            package['reopen'] = self.parse_date(package['reopen'])

        package['command'] = 'edit_task'
        args['options'] = package
        args['method'] = 'edit_entity'
        rtn = self.make_request(args)
        self.handle_server_response(rtn, not o['quiet'])
