from __future__ import with_statement
import tempfile
import sys
import os
from sys import stdout
from contextlib import closing
from alloc import alloc

class mbox(alloc):

  one_line_help = "Download a task's emails to an mbox file."

  # Setup the options that this cli can accept
  ops = []
  ops.append((''  ,'help           ','Show this help.'))
  ops.append(('q' ,'quiet          ','Run with no output except errors.'))
  ops.append(('t:','task=ID|NAME   ','A task ID, or a fuzzy match for a task name.'))

  # Specify some header and footer text for the help text
  help_text = "Usage: %s [OPTIONS]\n"
  help_text+= one_line_help
  help_text+= '''\n\n%s

This program will automatically run $MAILER on the mbox file, if outputting to a TTY.

Examples:
alloc mbox -t 1234
alloc mbox -t 1234 > file.mbox'''

  def run(self):

    # Get the command line arguments into a dictionary
    o, remainder = self.get_args(self.ops, self.help_text)

    self.quiet = o['quiet']
    taskID = ''
    emailUIDs = ''

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

      # If we're redirecting stdout eg -t 123 >task123.html
      if not stdout.isatty():
        str = self.make_request({"method":"search_emails","str":'SUBJECT "Task Comment: '+taskID+' "'})
        str += "\n\n" + self.make_request({"method":"get_timeSheetItem_comments","taskID":taskID})
        print str

      else:
        if not 'MAILER' in os.environ or not os.environ['MAILER']:
          self.die('The environment variable $MAILER has not been defined. Eg: export MAILER="mutt -f "')

        str = self.make_request({"method":"search_emails","str":'SUBJECT "Task Comment: '+taskID+' "'})
        str += "\n\n" + self.make_request({"method":"get_timeSheetItem_comments","taskID":taskID})

        fd, filepath = tempfile.mkstemp()
        with closing(os.fdopen(fd, 'wb')) as tf:
          tf.write(unicode(str).encode('utf-8'))

        command = os.environ['MAILER']+' "'+filepath+'"'
        self.msg('Running: '+command)
        os.system(command)
        self.msg('Removing: '+filepath)
        os.remove(filepath)

