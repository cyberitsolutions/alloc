"""alloc library for handling command line arguments"""
import os
import sys
import getopt
import re
from sys import stdout

class alloc_cli_arg_handler:
  def get_subcommand_help(self, command_list, ops, text):
    """Get help text for a subcommand."""
    help_str = ""
    for x in ops:
      # These are used to build up the help text for --help
      c = " "
      s = "    "
      l = "                   "
      if x[0] and x[1]: c = ","
      if x[0]: s = "  -"+x[0].replace(":", "")
      if x[1].strip(): l = c+" --"+x[1]
      # eg:  -q, --quiet             Run with less output.
      help_str += s+l+"   "+x[2].replace("\n", "\n" + (" " * 26))+"\n"
    return text % (os.path.basename(" ".join(command_list[0:2])), help_str.rstrip())
    
  def __parse_args(self, ops):
    """Return four dictionaries that disambiguate the types of command line args. Don't use this, use alloc.get_args."""
    short_ops = ""
    long_ops = []
    no_arg_ops = {}
    all_ops = {}
    for x in ops:
      # These args go straight to getops
      short_ops += x[0]
      long_ops.append(re.sub("=.*$", "=", x[1]).strip())

      # track which ops don't require an argument eg -q
      if x[0] and x[0][-1] != ':':
        no_arg_ops["-"+x[0]] = True

      # or eg --help
      if ':' not in x[0] and '=' not in x[1]:
        no_arg_ops["--"+x[1].strip()] = True

      # Handle cases where there is no long arg (eg -t in alloc edit)
      if len(x[1].strip()) == 0:
        key = x[0].replace(":", "").strip()
      else:
        key = re.sub("=.*$", "", x[1]).strip()

      # And this is used below to build up a dictionary to return
      all_ops[key] = ["-"+x[0].replace(":", ""), "--"+key]
    return short_ops, long_ops, no_arg_ops, all_ops

  def get_args(self, alloc, command_list, ops, s):
    """This function allows us to handle the cli arguments elegantly."""
    options = []
    rtn = {}
    remainder = ""
    
    # The options parser cannot handle long args that have optional parameters
    # If --csv is used, replace it with --csv=auto
    if '--csv' in command_list:
      idx = command_list.index('--csv')
      if len(command_list) > idx+1 and command_list[idx+1] in ['always', 'never', 'auto']:
        command_list[idx] = '--csv='+ command_list[idx+1] 
        del command_list[idx+1]
      else:
        command_list[idx] = '--csv=always'

    short_ops, long_ops, no_arg_ops, all_ops = self.__parse_args(ops)

    for i in command_list[1:]:
      found = False
      if i[0:1] == "-":
        for k, v in all_ops.items():
          if i == v[0] or i == v[1]:
            found = True
        if not found and not i[:5] == "--csv":
          alloc.die("Unrecognized option: "+i)

    try:
      options, remainder = getopt.getopt(command_list[2:], short_ops, long_ops)
    except:
      print self.get_subcommand_help(command_list, ops, s)
      sys.exit(0)

    for k, v in all_ops.items():
      rtn[k] = ''
      for opt, val in options:
        if opt in v:
          # eg -q
          if opt in no_arg_ops and val == '':
            rtn[k] = True

          # eg -x argument
          else:
            rtn[k] = val

    if rtn['help']:
      print self.get_subcommand_help(command_list, ops, s)
      sys.exit(0)

    # Deal with --csv=[auto|always|never]
    if 'csv' in rtn and rtn['csv']:
      alloc.csv = True
      if rtn['csv'] == 'auto' and stdout.isatty():
        alloc.csv = False
      if rtn['csv'] == 'never':
        alloc.csv = False
    elif not stdout.isatty():
      alloc.csv = True
    
    return rtn, " ".join(remainder)


