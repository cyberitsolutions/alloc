"""alloccli subcommand for downloading alloc comments to mbox file."""
from __future__ import with_statement
import tempfile
import os
import subprocess
from sys import stdout
from contextlib import closing
from alloc import alloc

class mbox(alloc):
  """Download a task's emails to an mbox file."""

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('t:','task=ID|NAME   ','A task ID, or a fuzzy match for a task name.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= __doc__
  help_text+= '''\n\n%s

This program will automatically run $MAILER on the mbox file, if outputting to a TTY.

Examples:
alloc mbox -t 1234
alloc mbox -t 1234 > file.mbox'''

  def run(self, command_list):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(command_list, self.ops, self.help_text)

    self.quiet = o['quiet']
    taskID = ''

    # Got this far, then authenticate
    self.authenticate();


    # Get a taskID either passed via command line, or figured out from a task name
    tops = {}
    if self.is_num(o['task']):
      taskID = o['task']
    elif o['task']:
      tops = {}
      tops["taskName"] = o["task"]
      tops["taskView"] = "prioritised"
      taskID = self.search_for_task(tops)

    if taskID:

      str = ''
      str0 = "From allocPSA Thu Jan  1 10:00:01 1970\r\n" + self.print_task(taskID)
      str1 = self.make_request({"method":"search_emails","str":'SUBJECT "Task Comment: '+taskID+' "'})
      str2 = self.make_request({"method":"get_timeSheetItem_comments","taskID":taskID})

      if str0:
        str+= str0+"\n\n"
      if str1:
        str+= str1+"\n\n"
      if str2:
        str+= str2

      # If we're redirecting stdout eg alloc mbox -t 123 >task123.html
      if not stdout.isatty():
        print str

      else:
        try:
          fd, filepath = tempfile.mkstemp(prefix="alloc-%s_" % taskID, suffix=".mbox")
          with closing(os.fdopen(fd, 'wb')) as tf:
            tf.write(unicode(str).encode('utf-8'))
          subprocess.check_call ([os.getenv ("MAILER") or "mutt", "-f", filepath])
        finally:
          os.remove(filepath)

