"""alloccli subcommand for launching your $BROWSER at alloc."""
import os
from sys import stdout
from alloc import alloc

class browse(alloc):
  """Provide web browser access to particular entities."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  , 'help           ', 'Show this help.'))
  ops.append(('q' , 'quiet          ', 'Run with no output except errors.'))
  ops.append(('p.', 'project=ID|NAME', 'A project ID, or a fuzzy match for a project name.'))
  ops.append(('t.', 'task=ID|NAME   ', 'A task ID, or a fuzzy match for a task name.'))
  ops.append(('c.', 'client=ID|NAME ', 'A client ID, or a fuzzy match for a client\'s name.'))
  ops.append(('i.', 'time=ID        ', 'A time sheet ID.'))

  # Specify some header and footer text for the help text
  help_text = 'Usage: %s [OPTIONS] ID|NAME\n'
  help_text += __doc__
  help_text += '''\n\n%s

This program allows you to quickly jump to a particular alloc web page. It fires up 
$BROWSER/sensible-browser/lynx/elinks on the location, or if the output is not a TTY
it redirects to stdout instead.
  
Examples:
alloc browse --task 123
alloc browse --task 123 > task123.html
alloc browse --project 1234
alloc browse --client 43432
alloc browse --time 213'''


  def run(self, command_list):
    """Execute subcommand."""

    # Get the command line arguments into a dictionary
    o, remainder_ = self.get_args(command_list, self.ops, self.help_text)

    # Got this far, then authenticate
    self.authenticate()

    self.quiet = o['quiet']
    projectID = 0
    taskID = 0
    clientID = 0

    # Get a projectID either passed via command line, or figured out from a project name
    if self.is_num(o['project']):
      projectID = o['project']
    elif o['project']:
      projectID = self.search_for_project(o['project'])

    # Get a taskID either passed via command line, or figured out from a task name
    if self.is_num(o['task']):
      taskID = o['task']
    elif o['task']:
      tops = {}
      tops["taskName"] = o["task"]
      tops["taskView"] = "prioritised"
      taskID = self.search_for_task(tops)

    # Get a clientID either passed via command line, or figured out from a client name
    if self.is_num(o['client']):
      clientID = o['client']
    elif o['client']:
      clientID = self.search_for_client({"clientName":o['client']})

    # url to alloc
    base = "/".join(self.url.split("/")[:-2])

    if taskID:
      url = base+"/task/task.php?sessID="+self.sessID+"&taskID="+taskID
    elif projectID:
      url = base+"/project/project.php?sessID="+self.sessID+"&projectID="+projectID
    elif clientID:
      url = base+"/client/client.php?sessID="+self.sessID+"&clientID="+clientID
    elif o['time']:
      url = base+"/time/timeSheet.php?sessID="+self.sessID+"&timeSheetID="+str(o['time'])
    elif not o['task'] and not o['project'] and not o['client'] and not o['time']:
      url = base+"/index.php?sessID="+self.sessID
    else: 
      self.die('Specify one of -t, -p, -c, etc.')


    # If we're redirecting stdout eg -t 123 >task123.html
    if not stdout.isatty():
      print self.get_alloc_html(url)

    elif url:
      browser = ''
      brow_lynx = self.which('lynx')
      brow_elinks = self.which('elinks')
      brow_sensible = self.which('sensible-browser')

      if 'BROWSER' in os.environ and os.environ['BROWSER']:
        browser = os.environ['BROWSER']
      elif brow_sensible:
        browser = brow_sensible
      elif brow_lynx:
        browser = brow_lynx
      elif brow_elinks:
        browser = brow_elinks
     
      if not browser:
        self.die('$BROWSER not defined, and sensible-browser and lynx weren\'t found in PATH.')
      elif url:
        command = browser+' "'+url+'"'
        if o['quiet']: command += ' >/dev/null'
        self.msg('Running: '+command)
        os.system(command)

