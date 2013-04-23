"""alloc library for handling command line arguments"""
import os
import sys
import re
import argparse
from sys import stdout


class alloc_cli_arg_handler:
  """alloc library for handling command line arguments"""
  
  def __init__(self):
    """Not necessary."""
    pass

  def get_subcommand_help(self, command_list, ops, text):
    """Get help text for a subcommand."""
    help_str = ""
    for x in ops:
      # These are used to build up the help text for --help
      extra = ''
      c = (" " * 1)
      s = (" " * 4)
      l = (" " * 19)
      if x[0] and x[1]: c = ","
      if x[0]: s = "  -"+x[0].replace(":", "").replace(".", "")
      if x[1].strip(): l = c+" --"+x[1]

      if ':' in x[0]: extra = ' (can repeat)'
      # eg:  -q, --quiet             Run with less output.
      help_str += s+l+"   "+x[2].replace("\n", "\n" + (" " * 26))+extra+"\n"
    return text % (os.path.basename(" ".join(command_list[0:2])), help_str.rstrip())
    
  def __parse_args(self, ops):
    """Return three dictionaries that disambiguate the types of command line args. Use alloc.get_args."""
    no_arg_ops = {}
    all_ops = {}
    all_ops_list = []
    for x in ops:

      # track which ops don't require an argument eg -q
      if x[0] and ':' not in x[0] and '.' not in x[0]:
        no_arg_ops["-"+x[0]] = True

      # or eg --help
      if ':' not in x[0] and '.' not in x[0] and '=' not in x[1]:
        no_arg_ops["--"+x[1].strip()] = True

      # Handle cases where there is no long arg (eg -t in alloc edit)
      if len(x[1].strip()) == 0:
        key = x[0].replace(":", "").replace(".", "").strip()
      else:
        key = re.sub("=.*$", "", x[1]).strip()

      # And this is used below to build up a dictionary to return
      all_ops[key] = ["-"+x[0].replace(":", "").replace(".", ""), "--"+key, x[0]]

      # This is a flat list, so eg [-q, --quiet] will be separate entries
      if x[0].strip():
        all_ops_list.append("-"+x[0].replace(":", "").replace(".", "").strip())
      if x[1].strip():
        all_ops_list.append("--"+re.sub("=.*$", "", x[1]).strip())

    return no_arg_ops, all_ops, all_ops_list

  def get_args(self, alloc, command_list, ops, s):
    """This function allows us to handle the cli arguments efficiently."""
    options = []
    rtn = {}

    # For interrogation of a command's potential arguments
    ops.append((''  , 'list-option    ', 'List all options in a single column.'))

    # The options parser cannot handle long args that have optional parameters
    # If --csv is used without an argument, replace it with --csv=always
    if '--csv' in command_list:
      idx = command_list.index('--csv')
      if len(command_list) > idx+1 and command_list[idx+1] in ['always', 'never', 'auto']:
        command_list[idx] = '--csv='+ command_list[idx+1]
        del command_list[idx+1]
      else:
        command_list[idx] = '--csv=always'

    no_arg_ops, all_ops, all_ops_list = self.__parse_args(ops)
    parser = argparse.ArgumentParser(prog=os.path.basename(" ".join(sys.argv[0:2])), add_help=False)
    for k, v in all_ops.items():
      a1 = []
      a2 = {}
      a2['dest'] = k
      if ':' in v[2]:
        a2['default'] = []
        a2['action'] = 'append'
      elif '.' in v[2]:
        a2['default'] = ''
        a2['action'] = 'store'

      if v[0] != '-':
        a1.append(v[0])
      if v[1] != '--':
        a1.append(v[1])

      if v[0] in no_arg_ops or v[1] in no_arg_ops:
        a2['action'] = 'store_true'
        a2['default'] = False

      parser.add_argument(*a1, **a2)

    # Parse the options
    options = parser.parse_args(sys.argv[2:])

    # Turn the options into a dict
    for opt, val in vars(options).items():
      rtn[opt] = val

    # If --help print help and die
    if rtn['help']:
      print self.get_subcommand_help(command_list, ops, s)
      sys.exit(0)

    # If --list-option print options and die
    if 'list-option' in rtn and rtn['list-option']:
      for opt in all_ops_list:
        print opt
      sys.exit(0)

    # If --csv tell the alloc object about it
    if 'csv' in rtn and rtn['csv']:
      alloc.csv = True
      if rtn['csv'] == 'auto' and stdout.isatty():
        alloc.csv = False
      if rtn['csv'] == 'never':
        alloc.csv = False
    elif not stdout.isatty():
      alloc.csv = True
    
    return rtn, ""

