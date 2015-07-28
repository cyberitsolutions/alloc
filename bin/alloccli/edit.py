# alloccli subcommand for editing alloc entities.
from alloc import alloc

# edit.py is BEING DEPRECATED. Don't make any non-critical changes to this
# file.


class edit(alloc):

    """Modify an entity."""

    # Setup the options that this cli can accept
    ops = []
    ops.append((''  , 'help           ', 'Show this help.'))
    ops.append(('v' , 'verbose        ', 'Run with more output.\n'))

    # task options
    ops.append(('t.', '               ', 'Edit a task. Specify an ID or the word "new" to create.'))
    ops.append((''  , 'name=TEXT      ', 'task\'s title'))
    ops.append((''  , 'desc=TEXT      ', 'task\'s long description'))
    ops.append((''  , 'assign=USERNAME', 'username of the person that the task is assigned to'))
    ops.append((''  , 'manage=USERNAME', 'username of the person that the task is managed by'))
    ops.append((''  , 'dip=TEXT       ', 'default interested parties, comma separated usernames/emails/full names'))
    ops.append((''  , 'tags=TEXT      ', 'comma separated task tags'))
    ops.append((''  , 'priority=PRI   ', '1, 2, 3, 4 or 5; or one of Wishlist, Minor, Normal, Important or Critical'))
    ops.append((''  , 'limit=HOURS    ', 'limit in hours for effort spend on this task'))
    ops.append((''  , 'best=HOURS     ', 'shortest estimate of how many hours of effort this task will take'))
    ops.append((''  , 'likely=HOURS   ', 'most likely amount of hours of effort this task will take'))
    ops.append((''  , 'worst=HOURS    ', 'longest estimate of how many hours of effort this task will take'))
    ops.append((''  , 'estimator=USERNAME', 'the person who created the estimates on this task'))
    ops.append((''  , 'targetstart=DATE', 'estimated date for work to start on this task'))
    ops.append((''  , 'targetcompletion=DATE', 'estimated date for when this task should be finished'))
    ops.append((''  , 'project=ID|NAME', 'task\'s project ID, or a fuzzy match for a project name.'))
    ops.append((''  , 'type=TYPE      ', 'Task, Fault, Message, Milestone or Parent'))
    ops.append((''  , 'dupe=ID        ', 'task ID of the related dupe'))
    ops.append((''  , 'pend=IDS       ', 'task ID(s), comma separated, that block this task.'))
    ops.append((''  , 'reopen=DATE    ', 'Reopen the task on this date. To be used with --status=pending.'))
    ops.append((''  , 'status=STATUS  ', 'inprogress, notstarted, info, client, manager, invalid, duplicate,\n'
                                         'incomplete, complete; or: open, pending, closed\n'))
    # time sheet item options
    ops.append(('i.', '               ', 'Edit a time sheet item. Specify an ID or the word "new" to create.'))
    ops.append((''  , 'tsid=ID        ', 'time sheet that this item belongs to'))
    ops.append((''  , 'date=DATE      ', 'time sheet item date'))
    ops.append((''  , 'duration=HOURS ', 'time sheet item duration'))
    ops.append((''  , 'unit=NUM       ', 'time sheet item unit of duration eg: 1=hours 2=days 3=week 4=month 5=fixed'))
    ops.append((''  , 'task=ID        ', 'ID of the time sheet item\'s task'))
    ops.append((''  , 'rate=NUM       ', '$rate of the time sheet item'))
    ops.append((''  , 'private=1|0    ', 'privacy setting of the time sheet item\'s comment eg: 1=private 0=normal'))
    ops.append((''  , 'comment=TEXT   ', 'time sheet item comment'))
    ops.append((''  , 'multiplier=NUM ', 'time sheet item multiplier eg: 1=standard 1.5=time-and-a-half 2=double-time\n'
                                         '3=triple-time 0=no-charge'))
    ops.append((''  , 'delete=1       ', 'set this to 1 to delete the time sheet item\n'))

    # Specify some header and footer text for the help text
    help_text = "Usage: %s [OPTIONS]\n"
    help_text += __doc__
    help_text += """\n\n%s

This program allows editing of the fields on an alloc entity, like a task.

Examples:

# Edit a particular task.
alloc edit -t 1234 --status closed --name 'New name for the task.' --assign alla

# Create a new task.
alloc edit -t new --name 'This task is fooed in the bar' --project 22

# Create a new time sheet item. Note that tsid is mandatory.
alloc edit -i new --tsid 7941 --duration 3.5 --date 2011-07-24 --comment hey --task 15180

# Note that 'null' can be used to unset a field.
alloc edit -t 1234 --assignee null"""

    # Execute subcommand.
    def run(self, command_list):

        # Get the command line arguments into a dictionary
        o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

        # Got this far, then authenticate
        self.authenticate()
        personID = self.get_my_personID()

        args = {}
        if o['t']:
            command = 'edit_task'
            args['entity'] = 'task'
            args['id'] = o['t']
            if o['t'] == "new":
                if not o['type']:
                    o['type'] = "Task"
                if not o['priority']:
                    o['priority'] = "Normal"

        elif o['i']:
            command = 'edit_timeSheetItem'
            args['entity'] = 'item'
            args['id'] = o['i']

        else:
            self.die(
                "Use either -t to edit a task or -i to edit a time sheet item.")

        if o['date']:
            o['date'] = self.parse_date(o['date'])

        if o['project'] and not self.is_num(o['project']):
            o['project'] = self.search_for_project(o['project'], personID)

        if o['task'] and not self.is_num(o['task']):
            o['task'] = self.search_for_task({'taskName': o['task']})

        package = {}
        for key, val in o.items():
            if val:
                package[key] = val
            if type(val) == type("") and val.lower() == 'null':
                package[key] = ''

        if 'reopen' in package:
            package['reopen'] = self.parse_date(package['reopen'])

        package['command'] = command
        args['options'] = package
        args['method'] = 'edit_entity'
        rtn = self.make_request(args)
        self.handle_server_response(rtn, o['verbose'])
